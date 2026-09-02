<?php

namespace App\Jobs\Shop\PriceFetcher;

use App\Models\Shop\PriceFetcher;
use App\Support\DigikalaPriceFetcher;
use App\Support\FafaitPriceFetcher;
use App\Support\FaterPriceFetcher;
use App\Support\MarkaziPriceFetcher;
use App\Support\SetareganPriceFetcher;
use App\Support\TechnolifePriceFetcher;
use App\Support\TorobOfferFetcher;
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
    public function handle(TorobOfferFetcher $torobOfferFetcher): void
    {
        try {
            $logger = Log::channel('single');

            $price = match ($this->priceFetcher->type) {
                'digikala' => DigikalaPriceFetcher::fetchPrice($this->priceFetcher->url, $logger),
                'fafait' => FafaitPriceFetcher::fetchPrice($this->priceFetcher->url, $logger),
                'markazi' => MarkaziPriceFetcher::fetchPrice($this->priceFetcher->url, $logger),
                'fater' => FaterPriceFetcher::fetchPrice($this->priceFetcher->url, $logger),
                'setaregan' => SetareganPriceFetcher::fetchPrice($this->priceFetcher->url, $logger),
                'technolife' => TechnolifePriceFetcher::fetchPrice($this->priceFetcher->url, $logger),
                'torob' => $this->fetchTorobPrice($torobOfferFetcher),
                default => null,
            };

            if ($price !== null) {
                $this->priceFetcher->refresh();
                $this->priceFetcher->update([
                    'last_price' => $price,
                    'last_fetched_at' => now(),
                ]);

                $logger->info("Price fetched for price fetcher {$this->priceFetcher->id}: {$price}");
                Log::info("Price fetched for price fetcher {$this->priceFetcher->id}: {$price}");
            } else {
                $logger->warning("Failed to fetch price for price fetcher {$this->priceFetcher->id}");
                Log::warning("Failed to fetch price for price fetcher {$this->priceFetcher->id}");
            }
        } catch (\Exception $e) {
            Log::error("Error fetching price for price fetcher {$this->priceFetcher->id}: {$e->getMessage()}");
            Log::error('Stack trace: '.$e->getTraceAsString());
        }
    }

    private function fetchTorobPrice(TorobOfferFetcher $offerFetcher): ?int
    {
        $setter = $this->priceFetcher->torobPriceSetter;
        if (! $setter) {
            return null;
        }

        $offer = $offerFetcher->cheapestCompetitor(
            $this->priceFetcher->url,
            $setter->own_shop_names ?? [],
        );

        return $offer['price'] ?? null;
    }
}
