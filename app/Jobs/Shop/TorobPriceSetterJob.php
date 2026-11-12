<?php

namespace App\Jobs\Shop;

use App\Jobs\Notification\SendBaleMessageJob;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\TorobPriceSetter;
use App\Settings\BaleSettings;
use App\Settings\GeneralSettings;
use App\Support\FetcherLog;
use App\Support\TorobOfferFetcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
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
            ->with(['priceFetcher.product', 'productPrice.product', 'productPrice.color', 'productPrice.warranty'])
            ->find($this->priceSetter->getKey());

        if (! $setter || ! $setter->is_active) {
            $setter?->update([
                'status' => TorobPriceSetter::STATUS_INACTIVE,
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            FetcherLog::info('Torob: قیمت عوض نشد', FetcherLog::decision(
                'torob',
                TorobPriceSetter::STATUS_INACTIVE,
                'قانون قیمت‌گذاری غیرفعال است یا پیدا نشد.',
                [
                    'torob_price_setter_id' => $this->priceSetter->getKey(),
                    'is_active' => (bool) $setter?->is_active,
                ],
            ));

            return;
        }

        $baseContext = $this->baseContext($setter);

        if (! $setter->priceFetcher || ! $setter->productPrice) {
            $setter->update([
                'status' => TorobPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => 'The configured price source or target price no longer exists.',
            ]);

            FetcherLog::warning('Torob: قیمت عوض نشد', FetcherLog::decision(
                'torob',
                TorobPriceSetter::STATUS_FETCH_FAILED,
                'منبع قیمت یا قیمت هدف تنظیم‌شده دیگر وجود ندارد.',
                $baseContext,
            ));

            return;
        }

        if (! $setter->priceFetcher->product?->is_active || ! $setter->productPrice->product?->is_active) {
            $setter->update([
                'status' => TorobPriceSetter::STATUS_PRODUCT_UNAVAILABLE,
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            FetcherLog::info('Torob: قیمت عوض نشد', FetcherLog::decision(
                'torob',
                TorobPriceSetter::STATUS_PRODUCT_UNAVAILABLE,
                'محصول مربوط به قانون یا واریانت هدف غیرفعال است.',
                $baseContext,
            ));

            return;
        }

        try {
            $offer = $offerFetcher->cheapestCompetitor(
                $setter->priceFetcher->url,
                $setter->own_shop_names ?? [],
                $setter->min_price,
                $setter->step_amount,
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

                FetcherLog::info('Torob: قیمت عوض نشد', FetcherLog::decision(
                    'torob',
                    TorobPriceSetter::STATUS_NO_COMPETITOR,
                    'هیچ رقیب واجدی بعد از فیلتر فروشگاه خودمان، موجودی و اقساطی پیدا نشد.',
                    array_merge($baseContext, [
                        'own_shop_names' => $setter->own_shop_names ?? [],
                        'competitor_price' => null,
                        'target_price' => null,
                    ]),
                ));

                return;
            }

            $setter->priceFetcher->update([
                'last_price' => $offer['price'],
                'last_fetched_at' => now(),
            ]);

            $candidate = $offer['price'] - $setter->step_amount;
            $pricingContext = array_merge($baseContext, [
                'own_shop_names' => $setter->own_shop_names ?? [],
                'competitor_shop' => $offer['shop_name'],
                'competitor_price' => $offer['price'],
                'step_amount' => $setter->step_amount,
                'candidate_price' => $candidate,
                'min_price' => $setter->min_price,
                'max_price' => $setter->max_price,
                'selected_rank' => $offer['selected_rank'] ?? null,
                'selection_reason' => $offer['selection_reason'] ?? null,
                'skipped_below_floor' => $offer['skipped_below_floor'] ?? [],
            ]);

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

                FetcherLog::info('Torob: قیمت عوض نشد', FetcherLog::decision(
                    'torob',
                    TorobPriceSetter::STATUS_PRODUCT_UNAVAILABLE,
                    'موجودی واریانت هدف صفر است؛ قیمت اعمال نشد.',
                    array_merge($pricingContext, [
                        'quantity' => $setter->productPrice->quantity,
                        'target_price' => max(0, $candidate),
                    ]),
                ));

                return;
            }

            if ($candidate < $setter->min_price) {
                $setter->update($common + [
                    'status' => TorobPriceSetter::STATUS_FLOOR_REACHED,
                ]);

                FetcherLog::info('Torob: قیمت عوض نشد', FetcherLog::decision(
                    'torob',
                    TorobPriceSetter::STATUS_FLOOR_REACHED,
                    'همه رقبا زیر حد ضرر بودند؛ برای جلوگیری از ضرر قیمت عوض نشد.',
                    array_merge($pricingContext, [
                        'target_price' => max(0, $candidate),
                        'current_regular_price' => (int) $setter->productPrice->price,
                        'current_sale_price' => $setter->productPrice->sale_price !== null
                            ? (int) $setter->productPrice->sale_price
                            : null,
                    ]),
                ));

                return;
            }

            $target = min($candidate, $setter->max_price);
            $priceChange = null;
            $decision = null;

            DB::transaction(function () use ($setter, $offer, $target, $pricingContext, &$priceChange, &$decision): void {
                $lockedSetter = TorobPriceSetter::query()->lockForUpdate()->find($setter->getKey());
                $productPrice = ProductPrice::query()
                    ->with(['product', 'color', 'warranty'])
                    ->lockForUpdate()
                    ->find($setter->product_price_id);

                if (! $lockedSetter || ! $lockedSetter->is_active || ! $productPrice?->product?->is_active) {
                    $decision = [
                        'outcome' => TorobPriceSetter::STATUS_INACTIVE,
                        'reason' => 'در لحظه اعمال، قانون غیرفعال شد یا محصول در دسترس نبود.',
                        'context' => array_merge($pricingContext, [
                            'target_price' => $target,
                        ]),
                    ];

                    return;
                }

                $regularPrice = (int) $productPrice->price;
                $salePrice = $productPrice->sale_price !== null ? (int) $productPrice->sale_price : null;
                $hasEffectiveSalePrice = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
                $currentPrice = $hasEffectiveSalePrice ? $salePrice : $regularPrice;

                $status = TorobPriceSetter::STATUS_UNCHANGED;
                $changedAt = $lockedSetter->last_changed_at;
                $detailContext = array_merge($pricingContext, [
                    'target_price' => $target,
                    'current_regular_price' => $regularPrice,
                    'current_sale_price' => $salePrice,
                    'current_effective_price' => $currentPrice,
                    'capped_by_max' => $target < ($offer['price'] - $setter->step_amount),
                ]);

                if ($currentPrice !== $target) {
                    if ($hasEffectiveSalePrice && $target < $regularPrice) {
                        $productPrice = $productPrice->supersede(['sale_price' => $target]);
                    } elseif ($hasEffectiveSalePrice) {
                        $productPrice = $productPrice->supersede([
                            'price' => $target,
                            'sale_price' => null,
                        ]);
                    } else {
                        $updates = ['price' => $target];
                        if ($salePrice !== null && $salePrice > 0 && $salePrice < $target) {
                            $updates['sale_price'] = null;
                        }
                        $productPrice = $productPrice->supersede($updates);
                    }

                    $lockedSetter->refresh();

                    $status = TorobPriceSetter::STATUS_UPDATED;
                    $changedAt = now();
                    $priceChange = [
                        'product_price' => $productPrice,
                        'old_price' => $currentPrice,
                        'new_price' => $target,
                        'competitor_shop' => $offer['shop_name'],
                        'competitor_price' => $offer['price'],
                    ];
                    $skippedCount = count($offer['skipped_below_floor'] ?? []);
                    $decision = [
                        'outcome' => $status,
                        'reason' => $skippedCount > 0
                            ? sprintf(
                                'قیمت فروشگاه بر اساس نفر %d به‌روز شد؛ %d رقیب ارزان‌تر به‌خاطر حد ضرر رد شدند.',
                                $offer['selected_rank'] ?? 0,
                                $skippedCount,
                            )
                            : 'قیمت فروشگاه بر اساس ارزان‌ترین رقیب واجد به‌روز شد.',
                        'context' => array_merge($detailContext, [
                            'old_price' => $currentPrice,
                            'new_price' => $target,
                            'product_price_id' => $productPrice->id,
                        ]),
                    ];
                } else {
                    $decision = [
                        'outcome' => $status,
                        'reason' => 'قیمت فعلی فروشگاه با هدف محاسبه‌شده یکی است؛ تغییری لازم نبود.',
                        'context' => $detailContext,
                    ];
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

            if (is_array($priceChange)) {
                $this->notifyBalePriceChanged($priceChange);
            }

            if (is_array($decision)) {
                $message = $decision['outcome'] === TorobPriceSetter::STATUS_UPDATED
                    ? 'Torob: قیمت به‌روز شد'
                    : 'Torob: قیمت عوض نشد';

                FetcherLog::info($message, FetcherLog::decision(
                    'torob',
                    $decision['outcome'],
                    $decision['reason'],
                    $decision['context'],
                ));
            }
        } catch (Throwable $exception) {
            $setter->update([
                'status' => TorobPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            FetcherLog::error('Torob: قیمت عوض نشد', FetcherLog::decision(
                'torob',
                TorobPriceSetter::STATUS_FETCH_FAILED,
                'خطا در دریافت یا پردازش قیمت از ترب: '.$exception->getMessage(),
                array_merge($baseContext, [
                    'exception' => $exception::class,
                ]),
            ));

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function baseContext(TorobPriceSetter $setter): array
    {
        return [
            'torob_price_setter_id' => $setter->getKey(),
            'price_fetcher_id' => $setter->price_fetcher_id,
            'product_price_id' => $setter->product_price_id,
            'product_id' => $setter->priceFetcher?->product_id ?? $setter->productPrice?->product_id,
            'product_name' => $setter->priceFetcher?->product?->name ?? $setter->productPrice?->product?->name,
            'url' => $setter->priceFetcher?->url,
        ];
    }

    /**
     * @param  array{product_price: ProductPrice, old_price: int, new_price: int, competitor_shop: string, competitor_price: int}  $change
     */
    private function notifyBalePriceChanged(array $change): void
    {
        $bale = app(BaleSettings::class);

        if (trim($bale->bot_token) === '' || trim($bale->chat_id) === '') {
            return;
        }

        $productPrice = $change['product_price'];
        $product = $productPrice->product;
        $variantParts = array_filter([
            $productPrice->color?->name,
            $productPrice->warranty?->name,
        ]);

        $productLabel = trim((string) ($product?->name ?? ''));
        if ($variantParts !== []) {
            $productLabel .= ' ('.implode(' / ', $variantParts).')';
        }
        if ($productLabel === '') {
            $productLabel = '-';
        }

        $message = __('general.torob_price_changed_bale_message', [
            'site' => app(GeneralSettings::class)->title,
            'product' => $productLabel,
            'old_price' => number_format($change['old_price']),
            'new_price' => number_format($change['new_price']),
            'competitor' => $change['competitor_shop'],
            'competitor_price' => number_format($change['competitor_price']),
            'url' => $product
                ? route('panel.shop.product.pricing.index', ['productId' => $product->id])
                : '',
        ]);

        dispatch(new SendBaleMessageJob($bale->chat_id, $message));
    }

    public function failed(?Throwable $exception): void
    {
        TorobPriceSetter::query()->whereKey($this->priceSetter->getKey())->update([
            'status' => TorobPriceSetter::STATUS_FETCH_FAILED,
            'last_checked_at' => now(),
            'last_error' => mb_substr($exception?->getMessage() ?? 'Torob pricing job failed.', 0, 2000),
        ]);

        FetcherLog::error('Torob: قیمت عوض نشد', FetcherLog::decision(
            'torob',
            TorobPriceSetter::STATUS_FETCH_FAILED,
            'جاب قیمت‌گذاری ترب شکست خورد: '.($exception?->getMessage() ?? 'Torob pricing job failed.'),
            [
                'torob_price_setter_id' => $this->priceSetter->getKey(),
            ],
        ));
    }
}
