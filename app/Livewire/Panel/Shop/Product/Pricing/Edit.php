<?php

namespace App\Livewire\Panel\Shop\Product\Pricing;

use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    #[Locked]
    public $priceId;

    public ?ProductPrice $productPrice = null;

    public ?int $color_id = null;

    public ?int $warranty_id = null;

    public string $price = '0';

    public string $sale_price = '0';

    public ?string $quantity = '0';

    public bool $is_default = false;

    public string $color_search = '';

    public string $warranty_search = '';

    #[On('panel.shop.product.pricing.edit.assign-data')]
    public function assignData($id): void
    {
        $this->priceId = $id;
        $this->productPrice = ProductPrice::with(['product.colors', 'product.warranties'])->findOrFail($id);

        $this->color_id = $this->productPrice->color_id;
        $this->warranty_id = $this->productPrice->warranty_id;
        $this->price = number_format($this->productPrice->price);
        $this->sale_price = $this->productPrice->sale_price ? number_format($this->productPrice->sale_price) : '';
        $this->quantity = $this->productPrice->quantity;
        $this->is_default = (bool) $this->productPrice->is_default;

        $this->color_search = '';
        $this->warranty_search = '';

        $this->resetValidation();

        Flux::modal('panel.shop.product.pricing.edit.modal')->show();
    }

    public function update()
    {
        if (! $this->productPrice) {
            return;
        }

        // Convert empty strings to null for optional fields
        $this->color_id = $this->color_id === '' ? null : $this->color_id;
        $this->warranty_id = $this->warranty_id === '' ? null : $this->warranty_id;

        $validated = $this->validate([
            'price' => ['required'],
            'sale_price' => ['nullable'],
            'quantity' => ['required', 'numeric'],
            'color_id' => ['nullable', 'exists:colors,id'],
            'warranty_id' => ['nullable', 'exists:warranties,id'],
            'is_default' => ['boolean'],
        ], [
            'price.required' => __('app.price_required'),
            'price.numeric' => __('app.price_must_be_numeric'),
            'sale_price.numeric' => __('app.sale_price_must_be_numeric'),
            'quantity.required' => __('app.quantity_required'),
            'quantity.numeric' => __('app.quantity_must_be_numeric'),
            'color_id.exists' => __('app.color_not_found'),
            'warranty_id.exists' => __('app.warranty_not_found'),
        ]);

        // If setting as default, unset other defaults for this product with same color/warranty combination
        if ($validated['is_default']) {
            ProductPrice::where('product_id', $this->productPrice->product_id)
                ->where(function ($query) use ($validated) {
                    $query->where('color_id', $validated['color_id'] ?? null)
                        ->where('warranty_id', $validated['warranty_id'] ?? null);
                })
                ->update(['is_default' => false]);
        }

        $this->productPrice->update([
            'color_id' => $validated['color_id'],
            'warranty_id' => $validated['warranty_id'],
            'price' => str_replace(',', '', $validated['price']),
            'sale_price' => $validated['sale_price'] ? str_replace(',', '', $validated['sale_price']) : null,
            'quantity' => $validated['quantity'],
            'is_default' => $validated['is_default'],
        ]);

        Flux::toast(variant: 'success', text: __('app.price_updated'));
        Flux::modal('panel.shop.product.pricing.edit.modal')->close();

        $this->dispatch('panel.shop.product.pricing.index.render');
    }

    #[Computed]
    public function colors()
    {
        if (! $this->productPrice?->product) {
            return collect();
        }

        return $this->productPrice->product->colors()
            ->when($this->color_search, fn ($query) => $query->where('colors.name', 'like', '%'.$this->color_search.'%'))
            ->select('colors.id', 'colors.name', 'colors.hex')
            ->orderBy('colors.name')
            ->get();
    }

    #[Computed]
    public function warranties()
    {
        if (! $this->productPrice?->product) {
            return collect();
        }

        return $this->productPrice->product->warranties()
            ->when($this->warranty_search, fn ($query) => $query->where('warranties.name', 'like', '%'.$this->warranty_search.'%'))
            ->select('warranties.id', 'warranties.name', 'warranties.slug', 'warranties.slug_fa')
            ->orderBy('warranties.name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.pricing.edit');
    }
}
