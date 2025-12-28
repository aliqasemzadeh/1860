<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Attribute;
use App\Models\Shop\Product;
use App\Models\Shop\ProductAttributeValue;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Attributes extends Component
{
    public Product $product;

    public int $productId;

    public array $attributeValues = [];

    public function mount(int $id): void
    {
        $this->productId = $id;
        $this->loadProduct();
    }

    public function loadProduct(): void
    {
        $this->product = Product::with(['category.attributes', 'attributeValues.attribute'])->findOrFail($this->productId);

        // Load existing values
        foreach ($this->product->attributeValues as $value) {
            $this->attributeValues[$value->attribute_id] = $this->getValueForAttribute($value);
        }

        // Add category attributes that don't have values yet
        if ($this->product->category) {
            foreach ($this->product->category->attributes as $attribute) {
                if (! isset($this->attributeValues[$attribute->id])) {
                    $this->attributeValues[$attribute->id] = $this->getDefaultValueForAttribute($attribute);
                }
            }
        }
    }

    private function getValueForAttribute(ProductAttributeValue $value): mixed
    {
        return match ($value->attribute->type) {
            'text', 'textarea' => $value->value_text,
            'number' => $value->value_number,
            'boolean' => $value->value_bool,
            'date' => $value->value_date?->format('Y-m-d'),
            'select' => $value->value_json,
            'multiselect' => $value->value_json ?? [],
            default => $value->value_text,
        };
    }

    private function getDefaultValueForAttribute(Attribute $attribute): mixed
    {
        return match ($attribute->type) {
            'boolean' => false,
            'multiselect' => [],
            default => null,
        };
    }

    public function save(): void
    {
        $this->product = Product::with('category.attributes')->findOrFail($this->productId);

        if (!$this->product->category) {
            Flux::toast(variant: 'error', text: __('app.product_must_have_category'));

            return;
        }

        $categoryAttributes = $this->product->category->attributes;

        foreach ($categoryAttributes as $attribute) {
            $value = $this->attributeValues[$attribute->id] ?? null;

            // Skip if value is empty and not required
            if (empty($value) && !$attribute->is_required) {
                // Delete existing value if exists
                ProductAttributeValue::query()
                    ->where('product_id', $this->product->id)
                    ->where('attribute_id', $attribute->id)
                    ->delete();
                continue;
            }

            // Validate required attributes
            if ($attribute->is_required && empty($value)) {
                Flux::toast(variant: 'error', text: __('app.attribute_required', ['name' => $attribute->label]));

                return;
            }

            // Prepare value data based on type
            $valueData = [
                'product_id' => $this->product->id,
                'attribute_id' => $attribute->id,
            ];

            match ($attribute->type) {
                'text', 'textarea' => $valueData['value_text'] = $value,
                'number' => $valueData['value_number'] = $value,
                'boolean' => $valueData['value_bool'] = (bool) $value,
                'date' => $valueData['value_date'] = $value ? \Carbon\Carbon::parse($value) : null,
                'select', 'multiselect' => $valueData['value_json'] = is_array($value) ? $value : [$value],
                default => $valueData['value_text'] = $value,
            };

            ProductAttributeValue::updateOrCreate(
                [
                    'product_id' => $this->product->id,
                    'attribute_id' => $attribute->id,
                ],
                $valueData
            );
        }

        Flux::toast(variant: 'success', text: __('app.product_attributes_updated'));
        $this->loadProduct();
    }

    #[Layout('layouts.panels.shop')]
    public function render()
    {
        $attributes = $this->product->category?->attributes()->with(['attributeGroup', 'options'])->orderBy('sort_order')->get() ?? collect();

        return view('livewire.panel.shop.product.attributes', [
            'attributes' => $attributes->groupBy('attributeGroup.name'),
        ]);
    }
}

