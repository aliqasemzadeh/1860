<?php

namespace App\Livewire\Panel\Content\Box;

use App\Models\Content\Box;
use App\Models\Shop\Product;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Products extends Component
{
    public ?Box $box = null;

    public string $productSearch = '';

    public string $listSearch = '';

    public ?int $selectedProductId = null;

    #[On('panels.administrator.content.box.products.assign-data')]
    public function assignData(int $id): void
    {
        $this->box = Box::query()->with(['products' => fn ($q) => $q->select('products.id', 'products.name')])->findOrFail($id);
        $this->productSearch = '';
        $this->listSearch = '';
        $this->selectedProductId = null;
        Flux::modal('content.box.products')->show();
    }

    public function addProduct(): void
    {
        if (! $this->box || ! $this->selectedProductId) {
            return;
        }

        $this->box->products()->syncWithoutDetaching([$this->selectedProductId]);
        $this->box->load(['products' => fn ($q) => $q->select('products.id', 'products.name')]);
        $this->selectedProductId = null;
        $this->productSearch = '';
        unset($this->availableProducts, $this->attachedProducts);

        Flux::toast(__('general.box_product_added'));
    }

    public function removeProduct(int $productId): void
    {
        if (! $this->box) {
            return;
        }

        $this->box->products()->detach($productId);
        $this->box->load(['products' => fn ($q) => $q->select('products.id', 'products.name')]);
        unset($this->availableProducts, $this->attachedProducts);

        Flux::toast(__('general.box_product_removed'));
    }

    #[Computed]
    public function availableProducts(): Collection
    {
        $existingIds = $this->box
            ? $this->box->products->pluck('id')->all()
            : [];

        return Product::query()
            ->select('products.id', 'products.name')
            ->when($this->productSearch, fn ($query) => $query->where('name', 'like', '%' . $this->productSearch . '%'))
            ->whereNotIn('id', $existingIds)
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function attachedProducts(): Collection
    {
        if (! $this->box) {
            return collect();
        }

        return $this->box->products
            ->when($this->listSearch, fn (Collection $products) => $products->filter(
                fn (Product $product) => str_contains(mb_strtolower($product->name), mb_strtolower($this->listSearch))
            ))
            ->values();
    }

    public function render(): View
    {
        return view('livewire.panel.content.box.products');
    }
}
