<?php

namespace App\Livewire\Panel\Shop\Product\Pricing;

use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class History extends Component
{
    #[Locked]
    public $productId;

    public ?int $colorId = null;
    public ?int $warrantyId = null;

    public Product $product;

    public function mount(int $productId)
    {
        $data = [];
        $this->productId = $productId;
        $this->colorId = $data['colorId'] ?? null;
        $this->warrantyId = $data['warrantyId'] ?? null;
        $this->product = Product::findOrFail($productId);
    }

    #[On('panel.shop.product.pricing.history.assign-data')]
    public function assignData(int $colorId = null, int $warrantyId = null): void
    {
        $this->colorId = $colorId;
        $this->warrantyId = $warrantyId;
        Flux::modal('panel.shop.product.pricing.history.modal')->show();
    }

    #[Computed]
    public function priceHistory()
    {
        return ProductPrice::where('product_id', $this->productId)
            ->when($this->colorId !== null, fn($query) => $query->where('color_id', $this->colorId))
            ->when($this->colorId === null, fn($query) => $query->whereNull('color_id'))
            ->when($this->warrantyId !== null, fn($query) => $query->where('warranty_id', $this->warrantyId))
            ->when($this->warrantyId === null, fn($query) => $query->whereNull('warranty_id'))
            ->with(['color', 'warranty'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    #[Computed]
    public function chartData()
    {
        $data = $this->priceHistory->map(function ($price) {
            $item = [
                'date' =>  Carbon::make($price->created_at)->toString(),
                'price' => (float) $price->price,
            ];

            // Only include sale_price if it exists
            if ($price->sale_price) {
                $item['sale_price'] = (float) $price->sale_price;
            }

            return $item;
        })->values()->toArray();

        // Ensure we have at least one data point
        if (empty($data)) {
            return [
                [
                    'date' => Carbon::now()->toIso8601String(),
                    'price' => 0,
                ]
            ];
        }

        return $data;
    }

    #[Computed]
    public function colorName()
    {
        if ($this->colorId === null) {
            return null;
        }

        $firstPrice = $this->priceHistory->first();
        return $firstPrice?->color?->name;
    }

    #[Computed]
    public function warrantyName()
    {
        if ($this->warrantyId === null) {
            return null;
        }

        $firstPrice = $this->priceHistory->first();
        return $firstPrice?->warranty?->name;
    }

    public function render()
    {
        return view('livewire.panel.shop.product.pricing.history');
    }
}
