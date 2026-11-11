<?php

namespace App\Jobs\Shop;

use App\Jobs\Notification\SendBaleMessageJob;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\SetareganPriceSetter;
use App\Settings\BaleSettings;
use App\Settings\GeneralSettings;
use App\Support\FetcherLog;
use App\Support\SetareganPageMissingException;
use App\Support\SetareganPriceFetcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class SetareganPriceSetterJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    public int $uniqueFor = 120;

    public function __construct(public SetareganPriceSetter $priceSetter) {}

    public function uniqueId(): string
    {
        return (string) $this->priceSetter->getKey();
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(): void
    {
        $setter = SetareganPriceSetter::query()
            ->with(['priceFetcher.product', 'productPrice.product', 'productPrice.color', 'productPrice.warranty'])
            ->find($this->priceSetter->getKey());

        if (! $setter || ! $setter->is_active) {
            $setter?->update([
                'status' => SetareganPriceSetter::STATUS_INACTIVE,
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            FetcherLog::info('Setaregan: قیمت عوض نشد', FetcherLog::decision(
                'setaregan',
                SetareganPriceSetter::STATUS_INACTIVE,
                'قانون قیمت‌گذاری ستارگان غیرفعال است یا پیدا نشد.',
                [
                    'setaregan_price_setter_id' => $this->priceSetter->getKey(),
                    'is_active' => (bool) $setter?->is_active,
                ],
            ));

            return;
        }

        $baseContext = $this->baseContext($setter);

        if (! $setter->priceFetcher || ! $setter->productPrice) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => 'The configured price source or target price no longer exists.',
            ]);

            FetcherLog::warning('Setaregan: قیمت عوض نشد', FetcherLog::decision(
                'setaregan',
                SetareganPriceSetter::STATUS_FETCH_FAILED,
                'منبع قیمت یا قیمت هدف تنظیم‌شده دیگر وجود ندارد.',
                $baseContext,
            ));

            return;
        }

        if (! $setter->priceFetcher->product?->is_active || ! $setter->productPrice->product?->is_active) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_PRODUCT_UNAVAILABLE,
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            FetcherLog::info('Setaregan: قیمت عوض نشد', FetcherLog::decision(
                'setaregan',
                SetareganPriceSetter::STATUS_PRODUCT_UNAVAILABLE,
                'محصول مربوط به قانون یا واریانت هدف غیرفعال است.',
                $baseContext,
            ));

            return;
        }

        try {
            $offer = SetareganPriceFetcher::fetchOffer(
                $setter->priceFetcher->url,
                FetcherLog::channel(),
            );

            if ($offer['available'] === false) {
                $this->handleOutOfStock($setter, $offer);

                return;
            }

            if ($offer['price'] === null) {
                $setter->update([
                    'status' => SetareganPriceSetter::STATUS_NO_PRICE,
                    'last_supplier_available' => true,
                    'last_supplier_price' => null,
                    'last_stock_action' => null,
                    'last_checked_at' => now(),
                    'last_error' => 'Setaregan page appears in stock but no price could be parsed.',
                ]);

                FetcherLog::warning('Setaregan: قیمت عوض نشد', FetcherLog::decision(
                    'setaregan',
                    SetareganPriceSetter::STATUS_NO_PRICE,
                    'صفحه ستارگان موجود به نظر می‌رسد ولی قیمت قابل خواندن نبود.',
                    array_merge($baseContext, [
                        'offer_source' => $offer['source'] ?? null,
                        'supplier_price' => null,
                    ]),
                ));

                return;
            }

            $this->handleAvailableOffer($setter, $offer);
        } catch (SetareganPageMissingException $exception) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_PAGE_NOT_FOUND,
                'last_checked_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            FetcherLog::warning('Setaregan: قیمت عوض نشد', FetcherLog::decision(
                'setaregan',
                SetareganPriceSetter::STATUS_PAGE_NOT_FOUND,
                'صفحه محصول ستارگان پیدا نشد: '.$exception->getMessage(),
                $baseContext,
            ));

            $this->notifyBalePageMissing($setter);

            return;
        } catch (Throwable $exception) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            FetcherLog::error('Setaregan: قیمت عوض نشد', FetcherLog::decision(
                'setaregan',
                SetareganPriceSetter::STATUS_FETCH_FAILED,
                'خطا در دریافت یا پردازش قیمت از ستارگان: '.$exception->getMessage(),
                array_merge($baseContext, [
                    'exception' => $exception::class,
                ]),
            ));

            throw $exception;
        }
    }

    /**
     * @param  array{price: ?int, available: bool, source: ?string}  $offer
     */
    private function handleOutOfStock(SetareganPriceSetter $setter, array $offer): void
    {
        $setter->priceFetcher->update([
            'last_price' => $offer['price'],
            'last_fetched_at' => now(),
        ]);

        $zeroed = false;

        DB::transaction(function () use ($setter, &$zeroed): void {
            $productPrice = ProductPrice::query()->lockForUpdate()->find($setter->product_price_id);
            if ($productPrice && (float) $productPrice->quantity > 0) {
                $productPrice->update(['quantity' => 0]);
                $zeroed = true;
            }
        }, 3);

        $setter->update([
            'status' => SetareganPriceSetter::STATUS_OUT_OF_STOCK,
            'last_supplier_available' => false,
            'last_supplier_price' => $offer['price'],
            'last_stock_action' => $zeroed ? 'zeroed' : null,
            'last_stock_changed_at' => $zeroed ? now() : $setter->last_stock_changed_at,
            'last_checked_at' => now(),
            'last_error' => null,
        ]);

        FetcherLog::info(
            $zeroed ? 'Setaregan: موجودی صفر شد' : 'Setaregan: قیمت عوض نشد',
            FetcherLog::decision(
                'setaregan',
                SetareganPriceSetter::STATUS_OUT_OF_STOCK,
                $zeroed
                    ? 'تأمین‌کننده ناموجود بود؛ موجودی واریانت صفر شد و قیمت فروش عوض نشد.'
                    : 'تأمین‌کننده ناموجود بود؛ موجودی از قبل صفر بود و قیمت عوض نشد.',
                array_merge($this->baseContext($setter), [
                    'supplier_price' => $offer['price'],
                    'offer_source' => $offer['source'] ?? null,
                    'stock_action' => $zeroed ? 'zeroed' : null,
                ]),
            ),
        );

        if ($zeroed) {
            $this->notifyBaleStockChanged($setter->fresh(['productPrice.product', 'productPrice.color', 'productPrice.warranty']), 0);
        }
    }

    /**
     * @param  array{price: int, available: bool, source: ?string}  $offer
     */
    private function handleAvailableOffer(SetareganPriceSetter $setter, array $offer): void
    {
        $setter->priceFetcher->update([
            'last_price' => $offer['price'],
            'last_fetched_at' => now(),
        ]);

        $candidate = $offer['price'] - $setter->margin_amount;
        $target = min($candidate, $setter->max_price);
        $floorHit = $candidate < $setter->min_price;
        $baseContext = array_merge($this->baseContext($setter), [
            'supplier_price' => $offer['price'],
            'offer_source' => $offer['source'] ?? null,
            'margin_amount' => $setter->margin_amount,
            'candidate_price' => $candidate,
            'min_price' => $setter->min_price,
            'max_price' => $setter->max_price,
            'target_price' => $target,
            'floor_hit' => $floorHit,
        ]);

        $priceChange = null;
        $stockChange = null;
        $decision = null;

        DB::transaction(function () use ($setter, $offer, $target, $floorHit, $baseContext, &$priceChange, &$stockChange, &$decision): void {
            $lockedSetter = SetareganPriceSetter::query()->lockForUpdate()->find($setter->getKey());
            $productPrice = ProductPrice::query()
                ->with(['product', 'color', 'warranty'])
                ->lockForUpdate()
                ->find($setter->product_price_id);

            if (! $lockedSetter || ! $lockedSetter->is_active || ! $productPrice?->product?->is_active) {
                $decision = [
                    'outcome' => SetareganPriceSetter::STATUS_INACTIVE,
                    'reason' => 'در لحظه اعمال، قانون غیرفعال شد یا محصول در دسترس نبود.',
                    'context' => $baseContext,
                ];

                return;
            }

            $restored = false;
            if (
                (float) $productPrice->quantity <= 0
                && $lockedSetter->status === SetareganPriceSetter::STATUS_OUT_OF_STOCK
            ) {
                $productPrice->quantity = $lockedSetter->default_quantity;
                $restored = true;
            }

            $regularPrice = (int) $productPrice->price;
            $salePrice = $productPrice->sale_price !== null ? (int) $productPrice->sale_price : null;
            $changed = false;
            $currentEffective = ($salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice)
                ? $salePrice
                : $regularPrice;

            $detailContext = array_merge($baseContext, [
                'current_regular_price' => $regularPrice,
                'current_sale_price' => $salePrice,
                'current_effective_price' => $currentEffective,
            ]);

            if (! $floorHit && ($regularPrice !== $target || $salePrice !== $target)) {
                $oldPrice = $currentEffective;

                $productPrice->price = $target;
                $productPrice->sale_price = $target;
                $changed = true;

                $priceChange = [
                    'product_price' => $productPrice,
                    'old_price' => $oldPrice,
                    'new_price' => $target,
                    'supplier_price' => $offer['price'],
                ];
            }

            if ($changed || $restored) {
                $productPrice->save();
            }

            $status = $floorHit
                ? SetareganPriceSetter::STATUS_FLOOR_REACHED
                : ($changed ? SetareganPriceSetter::STATUS_UPDATED : SetareganPriceSetter::STATUS_UNCHANGED);

            $lockedSetter->update([
                'status' => $status,
                'last_supplier_price' => $offer['price'],
                'last_supplier_available' => true,
                'last_target_price' => $target,
                'last_applied_price' => $floorHit ? $lockedSetter->last_applied_price : $target,
                'last_stock_action' => $restored ? 'restored' : null,
                'last_checked_at' => now(),
                'last_changed_at' => $changed ? now() : $lockedSetter->last_changed_at,
                'last_stock_changed_at' => $restored ? now() : $lockedSetter->last_stock_changed_at,
                'last_error' => null,
            ]);

            if ($restored) {
                $stockChange = [
                    'setter' => $lockedSetter->fresh(['productPrice.product', 'productPrice.color', 'productPrice.warranty']),
                    'quantity' => (int) $productPrice->quantity,
                ];
            }

            $reason = match ($status) {
                SetareganPriceSetter::STATUS_FLOOR_REACHED => 'قیمت هدف محاسبه‌شده از کف قیمت کمتر است؛ برای جلوگیری از ضرر قیمت عوض نشد.',
                SetareganPriceSetter::STATUS_UPDATED => 'قیمت فروشگاه بر اساس قیمت تأمین‌کننده به‌روز شد.',
                default => 'قیمت فعلی فروشگاه با هدف محاسبه‌شده یکی است؛ تغییری لازم نبود.',
            };

            $decision = [
                'outcome' => $status,
                'reason' => $reason,
                'context' => array_merge($detailContext, [
                    'stock_restored' => $restored,
                    'old_price' => $priceChange['old_price'] ?? $currentEffective,
                    'new_price' => $priceChange['new_price'] ?? $currentEffective,
                ]),
            ];
        }, 3);

        if (is_array($priceChange)) {
            $this->notifyBalePriceChanged($priceChange);
        }

        if (is_array($stockChange)) {
            $this->notifyBaleStockChanged($stockChange['setter'], $stockChange['quantity']);
        }

        if (is_array($decision)) {
            $message = $decision['outcome'] === SetareganPriceSetter::STATUS_UPDATED
                ? 'Setaregan: قیمت به‌روز شد'
                : 'Setaregan: قیمت عوض نشد';

            FetcherLog::info($message, FetcherLog::decision(
                'setaregan',
                $decision['outcome'],
                $decision['reason'],
                $decision['context'],
            ));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function baseContext(SetareganPriceSetter $setter): array
    {
        return [
            'setaregan_price_setter_id' => $setter->getKey(),
            'price_fetcher_id' => $setter->price_fetcher_id,
            'product_price_id' => $setter->product_price_id,
            'product_id' => $setter->priceFetcher?->product_id ?? $setter->productPrice?->product_id,
            'product_name' => $setter->priceFetcher?->product?->name ?? $setter->productPrice?->product?->name,
            'url' => $setter->priceFetcher?->url,
        ];
    }

    /**
     * @param  array{product_price: ProductPrice, old_price: int, new_price: int, supplier_price: int}  $change
     */
    private function notifyBalePriceChanged(array $change): void
    {
        $bale = app(BaleSettings::class);

        if (trim($bale->bot_token) === '' || trim($bale->chat_id) === '') {
            return;
        }

        $productPrice = $change['product_price'];
        $product = $productPrice->product;
        $productLabel = $this->productLabel($productPrice);

        $message = __('general.setaregan_price_changed_bale_message', [
            'site' => app(GeneralSettings::class)->title,
            'product' => $productLabel,
            'old_price' => number_format($change['old_price']),
            'new_price' => number_format($change['new_price']),
            'supplier_price' => number_format($change['supplier_price']),
            'url' => $product
                ? route('panel.shop.product.pricing.index', ['productId' => $product->id])
                : '',
        ]);

        dispatch(new SendBaleMessageJob($bale->chat_id, $message));
    }

    private function notifyBaleStockChanged(SetareganPriceSetter $setter, int $quantity): void
    {
        $bale = app(BaleSettings::class);

        if (trim($bale->bot_token) === '' || trim($bale->chat_id) === '') {
            return;
        }

        $productPrice = $setter->productPrice;
        $product = $productPrice?->product;
        $productLabel = $productPrice ? $this->productLabel($productPrice) : '-';

        $message = __('general.setaregan_stock_changed_bale_message', [
            'site' => app(GeneralSettings::class)->title,
            'product' => $productLabel,
            'quantity' => number_format($quantity),
            'url' => $product
                ? route('panel.shop.product.pricing.index', ['productId' => $product->id])
                : '',
        ]);

        dispatch(new SendBaleMessageJob($bale->chat_id, $message));
    }

    private function notifyBalePageMissing(SetareganPriceSetter $setter): void
    {
        $bale = app(BaleSettings::class);

        if (trim($bale->bot_token) === '' || trim($bale->chat_id) === '') {
            return;
        }

        $productPrice = $setter->productPrice;
        $product = $productPrice?->product;
        $productLabel = $productPrice ? $this->productLabel($productPrice) : '-';

        $message = __('general.setaregan_page_missing_bale_message', [
            'site' => app(GeneralSettings::class)->title,
            'product' => $productLabel,
            'source_url' => $setter->priceFetcher?->url ?? '',
            'url' => $product
                ? route('panel.shop.product.pricing.index', ['productId' => $product->id])
                : '',
        ]);

        dispatch(new SendBaleMessageJob($bale->chat_id, $message));
    }

    private function productLabel(ProductPrice $productPrice): string
    {
        $variantParts = array_filter([
            $productPrice->color?->name,
            $productPrice->warranty?->name,
        ]);

        $productLabel = trim((string) ($productPrice->product?->name ?? ''));
        if ($variantParts !== []) {
            $productLabel .= ' ('.implode(' / ', $variantParts).')';
        }

        return $productLabel !== '' ? $productLabel : '-';
    }

    public function failed(?Throwable $exception): void
    {
        SetareganPriceSetter::query()->whereKey($this->priceSetter->getKey())->update([
            'status' => SetareganPriceSetter::STATUS_FETCH_FAILED,
            'last_checked_at' => now(),
            'last_error' => mb_substr($exception?->getMessage() ?? 'Setaregan pricing job failed.', 0, 2000),
        ]);

        FetcherLog::error('Setaregan: قیمت عوض نشد', FetcherLog::decision(
            'setaregan',
            SetareganPriceSetter::STATUS_FETCH_FAILED,
            'جاب قیمت‌گذاری ستارگان شکست خورد: '.($exception?->getMessage() ?? 'Setaregan pricing job failed.'),
            [
                'setaregan_price_setter_id' => $this->priceSetter->getKey(),
            ],
        ));
    }
}
