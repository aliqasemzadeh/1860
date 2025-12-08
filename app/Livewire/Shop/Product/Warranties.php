<?php

namespace App\Livewire\Shop\Product;

use App\Models\Shop\Product;
use App\Models\Shop\ProductWarranty;
use App\Models\Shop\Warranty;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Warranties extends Component
{
    public ?Product $product = null;

    public ?int $productId = null;

    public string $warrantySearch = '';

    public ?int $selectedWarrantyId = null;

    #[On('shop.product.warranties.assign-data')]
    public function assignData($id): void
    {
        $this->product = Product::with('warranties')->findOrFail($id);
        $this->productId = $this->product->id;
        $this->warrantySearch = '';
        $this->selectedWarrantyId = null;
        Flux::modal('shop.product.warranties.modal')->show();
    }

    #[On('shop.setting-management.warranty.index.render')]
    public function refreshWarranties(): void
    {
        // Refresh the product to get updated warranties list
        if ($this->product) {
            $this->product->refresh();
        }
    }

    public function addWarranty(): void
    {
        if (! $this->product || ! $this->selectedWarrantyId) {
            return;
        }

            // Create new record
        ProductWarranty::create([
            'product_id' => $this->product->id,
            'warranty_id' => $this->selectedWarrantyId,
        ]);

        $this->product->refresh();
        $this->selectedWarrantyId = null;
        $this->warrantySearch = '';
        Flux::toast(variant: 'success', text: __('app.warranty_added'));
    }

    public function removeWarranty(int $warrantyId): void
    {
        if (! $this->product) {
            return;
        }

        ProductWarranty::where('product_id', $this->product->id)
            ->where('warranty_id', $warrantyId)
            ->delete();

        $this->product->refresh();
        Flux::toast(variant: 'success', text: __('app.warranty_removed'));
    }

    #[Computed]
    public function availableWarranties()
    {
        // Get existing warranty IDs that are not soft-deleted
        $existingWarrantyIds = $this->product
            ? ProductWarranty::where('product_id', $this->product->id)
                ->pluck('warranty_id')
                ->toArray()
            : [];

        return Warranty::query()
            ->when($this->warrantySearch, fn($query) => $query->where('name', 'like', '%' . $this->warrantySearch . '%'))
            ->whereNotIn('id', $existingWarrantyIds)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'slug', 'slug_fa']);
    }

    public function render(): View
    {
        return view('livewire.shop.product.warranties');
    }
}
