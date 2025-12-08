<?php

namespace App\Livewire\Shop\Product;

use App\Models\Shop\Color;
use App\Models\Shop\Product;
use App\Models\Shop\ProductColor;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Colors extends Component
{
    public ?Product $product = null;

    public ?int $productId = null;

    public string $colorSearch = '';

    public ?int $selectedColorId = null;

    #[On('shop.product.colors.assign-data')]
    public function assignData($id): void
    {
        $this->product = Product::with('colors')->findOrFail($id);
        $this->productId = $this->product->id;
        $this->colorSearch = '';
        $this->selectedColorId = null;
        Flux::modal('shop.product.colors.modal')->show();
    }

    #[On('shop.setting-management.color.index.render')]
    public function refreshColors(): void
    {
        // Refresh the product to get updated colors list
        if ($this->product) {
            $this->product->refresh();
        }
    }

    public function addColor(): void
    {
        if (! $this->product || ! $this->selectedColorId) {
            return;
        }

        // Check if color already exists
        if ($this->product->colors()->where('color_id', $this->selectedColorId)->exists()) {
            Flux::toast(variant: 'error', text: __('app.color_already_exists'));
            return;
        }


        ProductColor::create([
            'product_id' => $this->product->id,
            'color_id' => $this->selectedColorId,
        ]);
        $this->product->refresh();
        $this->selectedColorId = null;
        $this->colorSearch = '';
        Flux::toast(variant: 'success', text: __('app.color_added'));
    }

    public function removeColor(int $colorId): void
    {
        if (! $this->product) {
            return;
        }

        $this->product->colors()->detach($colorId);
        $this->product->refresh();
        Flux::toast(variant: 'success', text: __('app.color_removed'));
    }

    #[Computed]
    public function availableColors()
    {
        $existingColorIds = $this->product?->colors->pluck('id')->toArray() ?? [];

        return Color::query()
            ->when($this->colorSearch, fn($query) => $query->where('name', 'like', '%' . $this->colorSearch . '%'))
            ->whereNotIn('id', $existingColorIds)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'hex']);
    }

    public function render(): View
    {
        return view('livewire.shop.product.colors');
    }
}
