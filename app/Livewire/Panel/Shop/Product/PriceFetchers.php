<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Jobs\Shop\PriceFetcher\FetchPriceJob;
use App\Models\Shop\PriceFetcher;
use App\Models\Shop\Product;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class PriceFetchers extends Component
{
    public ?Product $product = null;

    public ?int $productId = null;

    public string $type = 'digikala';

    public string $url = '';

    public function mount(): void
    {
        //
    }

    #[On('panel.shop.product.price-fetchers.assign-data')]
    public function assignData($id): void
    {
        $this->product = Product::with('priceFetchers')->findOrFail($id);
        $this->productId = $this->product->id;
        $this->type = 'digikala';
        $this->url = '';
        Flux::modal('panel.shop.product.price-fetchers.modal')->show();
    }

    public function addPriceFetcher(): void
    {
        if (! $this->product) {
            return;
        }

        $this->validate([
            'type' => 'required|in:digikala,fafait,markazi,fater,setaregan',
            'url' => 'required|url|max:500',
        ], [], [
            'type' => __('app.price_fetcher_type'),
            'url' => __('app.price_fetcher_url'),
        ]);

        PriceFetcher::create([
            'product_id' => $this->product->id,
            'type' => $this->type,
            'url' => $this->url,
        ]);

        $this->product->refresh();
        $this->type = 'digikala';
        $this->url = '';
        Flux::toast(variant: 'success', text: __('app.price_fetcher_added'));
    }

    public function removePriceFetcher(int $priceFetcherId): void
    {
        if (! $this->product) {
            return;
        }

        PriceFetcher::where('product_id', $this->product->id)
            ->where('id', $priceFetcherId)
            ->delete();

        $this->product->refresh();
        Flux::toast(variant: 'success', text: __('app.price_fetcher_removed'));
    }

    public function fetchPrice(int $priceFetcherId): void
    {
        if (! $this->product) {
            return;
        }

        $priceFetcher = PriceFetcher::where('product_id', $this->product->id)
            ->where('id', $priceFetcherId)
            ->first();

        if (! $priceFetcher) {
            Flux::toast(variant: 'danger', text: __('app.price_fetcher_not_found'));
            return;
        }

        // Dispatch job to fetch price
        FetchPriceJob::dispatch($priceFetcher);

        Flux::toast(variant: 'info', text: __('app.price_fetcher_fetching'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.price-fetchers');
    }
}
