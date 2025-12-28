<?php

namespace App\Livewire\Panel\Shop\Product\Pricing;

use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public $productId;
    public Product $product;

    public function mount(int $productId)
    {
        $this->productId = $productId;
        $this->product = Product::with(['colors', 'warranties'])->findOrFail($productId);
    }

    #[Computed]
    public function latestPrices()
    {
        // Get the latest price for each unique combination of color_id and warranty_id
        return ProductPrice::where('product_id', $this->productId)
            ->with(['color', 'warranty'])
            ->get()
            ->groupBy(function ($price) {
                // Create a unique key for each color_id-warranty_id combination
                $colorKey = $price->color_id ?? 'null';
                $warrantyKey = $price->warranty_id ?? 'null';
                return $colorKey . '-' . $warrantyKey;
            })
            ->map(function ($group) {
                // Get the latest price (by created_at) for each group
                return $group->sortByDesc('created_at')->first();
            })
            ->values()
            ->sortBy(function ($price) {
                // Sort by color name, then warranty name
                $colorName = $price->color?->name ?? 'zzz'; // Put null colors at the end
                $warrantyName = $price->warranty?->name ?? 'zzz'; // Put null warranties at the end
                return $colorName . '|' . $warrantyName;
            });
    }

    #[Layout('layouts.panels.shop')]
    #[On('shop.product.pricing.index.render')]
    public function render()
    {
        return view('livewire.panel.shop.product.pricing.index');
    }
}
