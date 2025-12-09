<?php

namespace App\Livewire\Shop\Product\Pricing;

use App\Models\Shop\Product;
use Livewire\Attributes\Locked;
use Livewire\Component;

class History extends Component
{
    #[Locked]
    public $productId;

    public Product $product;

    public function mount(int $productId, array $data = [])
    {
        $colorId = $data['colorId'] ?? null;
        $warrantyId = $data['warrantyId'] ?? null;
        $this->productId = $productId;
        $this->product = Product::findOrFail($productId);
    }

    public function render()
    {
        return view('livewire.shop.product.pricing.history');
    }
}
