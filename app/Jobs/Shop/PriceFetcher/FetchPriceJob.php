<?php

namespace App\Jobs\Shop\PriceFetcher;

use App\Models\Shop\PriceFetcher;
use App\Support\DigikalaPriceFetcher;
use App\Support\FafaitPriceFetcher;
use App\Support\FaterPriceFetcher;
use App\Support\FetcherLog;
use App\Support\MarkaziPriceFetcher;
use App\Support\SetareganPriceFetcher;
use App\Support\TechnolifePriceFetcher;
use App\Support\TorobOfferFetcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        $priceFetcher = PriceFetcher::query()
            ->with('product')
            ->find($this->priceFetcher->getKey());

        if (! $priceFetcher?->product?->is_active) {
            FetcherLog::info('PriceFetcher: دریافت قیمت انجام نشد', FetcherLog::decision(
                'price_fetcher',
                'product_unavailable',
                'محصول مربوط به دریافت‌کننده قیمت غیرفعال است یا پیدا نشد.',
                [
                    'price_fetcher_id' => $this->priceFetcher->getKey(),
                    'type' => $this->priceFetcher->type,
                ],
            ));

            return;
        }

        $this->priceFetcher = $priceFetcher;
        $logger = FetcherLog::channel();
        $context = [
            'price_fetcher_id' => $this->priceFetcher->id,
            'product_id' => $this->priceFetcher->product_id,
            'product_name' => $this->priceFetcher->product?->name,
            'type' => $this->priceFetcher->type,
            'url' => $this->priceFetcher->url,
        ];

        try {
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

                FetcherLog::info('PriceFetcher: قیمت خوانده شد', FetcherLog::decision(
                    'price_fetcher',
                    'fetched',
                    'قیمت با موفقیت از منبع خوانده و در last_price ذخیره شد.',
                    array_merge($context, [
                        'fetched_price' => $price,
                    ]),
                ));
            } else {
                FetcherLog::warning('PriceFetcher: قیمت خوانده نشد', FetcherLog::decision(
                    'price_fetcher',
                    'fetch_failed',
                    'هیچ قیمتی از منبع برنگشت.',
                    $context,
                ));
            }
        } catch (\Exception $e) {
            FetcherLog::error('PriceFetcher: خطا در دریافت قیمت', FetcherLog::decision(
                'price_fetcher',
                'fetch_failed',
                'خطا هنگام دریافت قیمت: '.$e->getMessage(),
                array_merge($context, [
                    'exception' => $e::class,
                ]),
            ));
        }
    }

    private function fetchTorobPrice(TorobOfferFetcher $offerFetcher): ?int
    {
        $setter = $this->priceFetcher->torobPriceSetter;
        if (! $setter) {
            FetcherLog::warning('PriceFetcher: قیمت ترب خوانده نشد', FetcherLog::decision(
                'price_fetcher',
                'fetch_failed',
                'برای این دریافت‌کننده، قانون رقابتی ترب تنظیم نشده است.',
                [
                    'price_fetcher_id' => $this->priceFetcher->id,
                    'type' => 'torob',
                ],
            ));

            return null;
        }

        $offer = $offerFetcher->cheapestCompetitor(
            $this->priceFetcher->url,
            $setter->own_shop_names ?? [],
        );

        return $offer['price'] ?? null;
    }
}
