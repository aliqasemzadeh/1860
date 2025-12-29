<?php

namespace App\Jobs\Shop\PriceFetcher;

use App\Models\Shop\PriceFetcher;
use App\Support\DigikalaPriceFetcher;
use App\Support\FafaitPriceFetcher;
use App\Support\FaterPriceFetcher;
use App\Support\MarkaziPriceFetcher;
use App\Support\SetareganPriceFetcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchPriceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PriceFetcher $priceFetcher
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $price = match ($this->priceFetcher->type) {
                'digikala' => DigikalaPriceFetcher::fetchPrice($this->priceFetcher->url),
                'fafait' => FafaitPriceFetcher::fetchPrice($this->priceFetcher->url),
                'markazi' => MarkaziPriceFetcher::fetchPrice($this->priceFetcher->url),
                'fater' => FaterPriceFetcher::fetchPrice($this->priceFetcher->url),
                'setaregan' => SetareganPriceFetcher::fetchPrice($this->priceFetcher->url),
                default => null,
            };

            if ($price !== null) {
                $this->priceFetcher->update([
                    'last_price' => $price,
                    'last_fetched_at' => now(),
                ]);

                Log::info("Price fetched for price fetcher {$this->priceFetcher->id}: {$price}");
            } else {
                Log::warning("Failed to fetch price for price fetcher {$this->priceFetcher->id}");
            }
        } catch (\Exception $e) {
            Log::error("Error fetching price for price fetcher {$this->priceFetcher->id}: {$e->getMessage()}");
        }
    }
}
