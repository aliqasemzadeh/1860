<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Attribute;
use App\Models\Shop\Product;
use App\Models\Shop\ProductAttributeValue;
use App\Services\Shop\CatalogAiService;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Throwable;

class Attributes extends Component
{
    public Product $product;

    public int $productId;

    public array $attributeValues = [];

    public int $aiStep = 1;

    public string $productKind = '';

    public string $modelHint = '';

    /** @var list<array{name: string, attributes: list<array{key: string, label: string, type: string, is_required: bool, options: list<array{value: string, label: string}>}>}> */
    public array $schemaPreview = [];

    public bool $aiBusy = false;

    public function mount(int $id): void
    {
        $this->productId = $id;
        $this->loadProduct();
        $this->resetAiWizardDefaults();
    }

    public function loadProduct(): void
    {
        $this->product = Product::with(['category.attributes', 'category.main_category', 'attributeValues.attribute', 'brand'])
            ->findOrFail($this->productId);

        $this->attributeValues = [];

        foreach ($this->product->attributeValues as $value) {
            $this->attributeValues[$value->attribute_id] = $this->getValueForAttribute($value);
        }

        if ($this->product->category) {
            foreach ($this->product->category->attributes as $attribute) {
                if (! isset($this->attributeValues[$attribute->id])) {
                    $this->attributeValues[$attribute->id] = $this->getDefaultValueForAttribute($attribute);
                }
            }
        }
    }

    public function openAiWizard(): void
    {
        if (! $this->product->category) {
            Flux::toast(variant: 'danger', text: __('general.product_must_have_category_for_attributes'));

            return;
        }

        $this->resetAiWizardDefaults();
        Flux::modal('panel.shop.product.attributes.ai-wizard.modal')->show();
    }

    public function suggestAttributeSchema(CatalogAiService $catalogAi): void
    {
        if (! $this->product->category) {
            Flux::toast(variant: 'danger', text: __('general.product_must_have_category_for_attributes'));

            return;
        }

        if (blank($this->productKind)) {
            Flux::toast(variant: 'danger', text: __('general.catalog_ai_product_kind_required'));

            return;
        }

        $this->aiBusy = true;

        try {
            $this->schemaPreview = $catalogAi->suggestCategoryAttributes(
                $this->productKind,
                $this->product->category,
            );

            Flux::toast(variant: 'success', text: __('general.catalog_ai_schema_suggested'));
        } catch (Throwable $exception) {
            Log::warning('Catalog AI attribute schema failed', [
                'product_id' => $this->productId,
                'message' => $exception->getMessage(),
            ]);

            Flux::toast(variant: 'danger', text: __('general.catalog_ai_schema_failed'));
        } finally {
            $this->aiBusy = false;
        }
    }

    public function applyAttributeSchema(CatalogAiService $catalogAi): void
    {
        if (! $this->product->category) {
            Flux::toast(variant: 'danger', text: __('general.product_must_have_category_for_attributes'));

            return;
        }

        if ($this->schemaPreview === []) {
            Flux::toast(variant: 'danger', text: __('general.catalog_ai_schema_required'));

            return;
        }

        $this->aiBusy = true;

        try {
            $catalogAi->applyCategoryAttributeSchema($this->product->category, $this->schemaPreview);
            $this->loadProduct();
            $this->aiStep = 2;

            if (blank($this->modelHint)) {
                $this->modelHint = (string) ($this->product->en_name ?: $this->product->name);
            }

            Flux::toast(variant: 'success', text: __('general.catalog_ai_schema_applied'));
        } catch (Throwable $exception) {
            Log::warning('Catalog AI attribute schema apply failed', [
                'product_id' => $this->productId,
                'message' => $exception->getMessage(),
            ]);

            Flux::toast(variant: 'danger', text: __('general.catalog_ai_schema_apply_failed'));
        } finally {
            $this->aiBusy = false;
        }
    }

    public function skipToValueStep(): void
    {
        if (! $this->product->category) {
            Flux::toast(variant: 'danger', text: __('general.product_must_have_category_for_attributes'));

            return;
        }

        if ($this->product->category->attributes->isEmpty()) {
            Flux::toast(variant: 'danger', text: __('general.catalog_ai_no_attributes'));

            return;
        }

        $this->aiStep = 2;

        if (blank($this->modelHint)) {
            $this->modelHint = (string) ($this->product->en_name ?: $this->product->name);
        }
    }

    public function generateAttributeValues(CatalogAiService $catalogAi): void
    {
        if (! $this->product->category) {
            Flux::toast(variant: 'danger', text: __('general.product_must_have_category_for_attributes'));

            return;
        }

        if ($this->product->category->attributes()->count() === 0) {
            Flux::toast(variant: 'danger', text: __('general.catalog_ai_no_attributes'));

            return;
        }

        $this->aiBusy = true;

        try {
            $this->attributeValues = $catalogAi->generateProductAttributeValues(
                $this->product->fresh(['category.attributes.options', 'brand']),
                $this->modelHint,
            );

            Flux::modal('panel.shop.product.attributes.ai-wizard.modal')->close();
            Flux::toast(variant: 'success', text: __('general.catalog_ai_attributes_generated'));
        } catch (Throwable $exception) {
            Log::warning('Catalog AI attribute values failed', [
                'product_id' => $this->productId,
                'message' => $exception->getMessage(),
            ]);

            Flux::toast(variant: 'danger', text: __('general.catalog_ai_attributes_failed'));
        } finally {
            $this->aiBusy = false;
        }
    }

    public function removeSchemaAttribute(int $groupIndex, int $attributeIndex): void
    {
        if (! isset($this->schemaPreview[$groupIndex]['attributes'][$attributeIndex])) {
            return;
        }

        unset($this->schemaPreview[$groupIndex]['attributes'][$attributeIndex]);
        $this->schemaPreview[$groupIndex]['attributes'] = array_values($this->schemaPreview[$groupIndex]['attributes']);

        if ($this->schemaPreview[$groupIndex]['attributes'] === []) {
            unset($this->schemaPreview[$groupIndex]);
            $this->schemaPreview = array_values($this->schemaPreview);
        }
    }

    private function resetAiWizardDefaults(): void
    {
        $this->aiStep = 1;
        $this->schemaPreview = [];
        $this->aiBusy = false;
        $this->productKind = (string) ($this->product->category?->name ?? '');
        $this->modelHint = (string) ($this->product->en_name ?: $this->product->name);
    }

    private function getValueForAttribute(ProductAttributeValue $value): mixed
    {
        return match ($value->attribute->type) {
            'text', 'textarea' => $value->value_text,
            'number' => $value->value_number,
            'boolean' => $value->value_bool,
            'date' => $value->value_date ? Jalalian::fromCarbon($value->value_date)->format('Y/m/d') : null,
            'select' => is_array($value->value_json) ? ($value->value_json[0] ?? null) : $value->value_json,
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

        if (! $this->product->category) {
            Flux::toast(variant: 'danger', text: __('general.product_must_have_category'));

            return;
        }

        $categoryAttributes = $this->product->category->attributes;

        foreach ($categoryAttributes as $attribute) {
            $value = $this->attributeValues[$attribute->id] ?? null;

            $isEmpty = ($attribute->type === 'multiselect' && (empty($value) || (is_array($value) && count($value) === 0)))
                || ($attribute->type !== 'multiselect' && empty($value) && $value !== 0 && $value !== '0' && $value !== false);

            if ($isEmpty && ! $attribute->is_required) {
                ProductAttributeValue::query()
                    ->where('product_id', $this->product->id)
                    ->where('attribute_id', $attribute->id)
                    ->delete();

                continue;
            }

            if ($attribute->is_required && $isEmpty) {
                Flux::toast(variant: 'danger', text: __('general.attribute_required', ['name' => $attribute->label]));

                return;
            }

            $valueData = [
                'product_id' => $this->product->id,
                'attribute_id' => $attribute->id,
            ];

            match ($attribute->type) {
                'text', 'textarea' => $valueData['value_text'] = $value,
                'number' => $valueData['value_number'] = $value,
                'boolean' => $valueData['value_bool'] = (bool) $value,
                'date' => $valueData['value_date'] = $value ? Jalalian::fromFormat('Y/m/d', str_replace('-', '/', $value))->toCarbon() : null,
                'select' => $valueData['value_json'] = $value ? [$value] : null,
                'multiselect' => $valueData['value_json'] = is_array($value) ? array_values($value) : ($value ? [$value] : []),
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

        Flux::toast(variant: 'success', text: __('general.product_attributes_updated'));
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
