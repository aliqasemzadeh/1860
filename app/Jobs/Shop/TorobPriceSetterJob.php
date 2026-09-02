<?php

namespace App\Jobs\Shop;

use App\Models\Shop\ProductPrice;
use App\Models\Shop\TorobPriceSetter;
use App\Support\TorobOfferFetcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TorobPriceSetterJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    public int $uniqueFor = 120;

    public function __construct(public TorobPriceSetter $priceSetter) {}

    public function uniqueId(): string
    {
        return (string) $this->priceSetter->getKey();
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Execute the job.
     */
    public function handle(TorobOfferFetcher $offerFetcher): void
    {
        $setter = TorobPriceSetter::query()
            ->with(['priceFetcher', 'productPrice'])
            ->find($this->priceSetter->getKey());

        if (! $setter || ! $setter->is_active) {
            $setter?->update([
                'status' => TorobPriceSetter::STATUS_INACTIVE,
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            return;
        }

        if (! $setter->priceFetcher || ! $setter->productPrice) {
            $setter->update([
                'status' => TorobPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => 'The configured price source or target price no longer exists.',
            ]);

            return;
        }

        try {
            $offer = $offerFetcher->cheapestCompetitor(
                $setter->priceFetcher->url,
                $setter->own_shop_names ?? [],
            );

            if ($offer === null) {
                $setter->priceFetcher->update([
                    'last_price' => null,
                    'last_fetched_at' => now(),
                ]);
                $setter->update([
                    'status' => TorobPriceSetter::STATUS_NO_COMPETITOR,
                    'last_competitor_shop' => null,
                    'last_competitor_price' => null,
                    'last_target_price' => null,
                    'last_checked_at' => now(),
                    'last_error' => null,
                ]);

                return;
            }

            $setter->priceFetcher->update([
                'last_price' => $offer['price'],
                'last_fetched_at' => now(),
            ]);

            $candidate = $offer['price'] - $setter->step_amount;
            $common = [
                'last_competitor_shop' => $offer['shop_name'],
                'last_competitor_price' => $offer['price'],
                'last_target_price' => max(0, $candidate),
                'last_checked_at' => now(),
                'last_error' => null,
            ];

            if ((float) $setter->productPrice->quantity <= 0) {
                $setter->update($common + [
                    'status' => TorobPriceSetter::STATUS_PRODUCT_UNAVAILABLE,
                ]);

                return;
            }

            if ($candidate < $setter->min_price) {
                $setter->update($common + [
                    'status' => TorobPriceSetter::STATUS_FLOOR_REACHED,
                ]);

                return;
            }

            $target = min($candidate, $setter->max_price);

            DB::transaction(function () use ($setter, $offer, $target): void {
                $lockedSetter = TorobPriceSetter::query()->lockForUpdate()->find($setter->getKey());
                $productPrice = ProductPrice::query()->lockForUpdate()->find($setter->product_price_id);

                if (! $lockedSetter || ! $lockedSetter->is_active || ! $productPrice) {
                    return;
                }

                $regularPrice = (int) $productPrice->price;
                $salePrice = $productPrice->sale_price !== null ? (int) $productPrice->sale_price : null;
                $hasEffectiveSalePrice = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
                $currentPrice = $hasEffectiveSalePrice ? $salePrice : $regularPrice;

                $status = TorobPriceSetter::STATUS_UNCHANGED;
                $changedAt = $lockedSetter->last_changed_at;

                if ($currentPrice !== $target) {
                    if ($hasEffectiveSalePrice && $target < $regularPrice) {
                        $productPrice->update(['sale_price' => $target]);
                    } elseif ($hasEffectiveSalePrice) {
                        $productPrice->update([
                            'price' => $target,
                            'sale_price' => null,
                        ]);
                    } else {
                        $updates = ['price' => $target];
                        if ($salePrice !== null && $salePrice > 0 && $salePrice < $target) {
                            $updates['sale_price'] = null;
                        }
                        $productPrice->update($updates);
                    }

                    $status = TorobPriceSetter::STATUS_UPDATED;
                    $changedAt = now();
                }

                $lockedSetter->update([
                    'status' => $status,
                    'last_competitor_shop' => $offer['shop_name'],
                    'last_competitor_price' => $offer['price'],
                    'last_target_price' => $target,
                    'last_applied_price' => $target,
                    'last_checked_at' => now(),
                    'last_changed_at' => $changedAt,
                    'last_error' => null,
                ]);
            }, 3);

            Log::info('Torob competitive pricing rule processed.', [
                'torob_price_setter_id' => $setter->getKey(),
                'competitor_price' => $offer['price'],
                'target_price' => $target,
            ]);
        } catch (Throwable $exception) {
            $setter->update([
                'status' => TorobPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        TorobPriceSetter::query()->whereKey($this->priceSetter->getKey())->update([
            'status' => TorobPriceSetter::STATUS_FETCH_FAILED,
            'last_checked_at' => now(),
            'last_error' => mb_substr($exception?->getMessage() ?? 'Torob pricing job failed.', 0, 2000),
        ]);
    }
}
