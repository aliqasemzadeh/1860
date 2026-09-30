<?php

namespace App\Jobs\Shop;

use App\Jobs\Notification\SendBaleMessageJob;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\SetareganPriceSetter;
use App\Settings\BaleSettings;
use App\Settings\GeneralSettings;
use App\Support\SetareganPageMissingException;
use App\Support\SetareganPriceFetcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            return;
        }

        if (! $setter->priceFetcher || ! $setter->productPrice) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => 'The configured price source or target price no longer exists.',
            ]);

            return;
        }

        if (! $setter->priceFetcher->product?->is_active || ! $setter->productPrice->product?->is_active) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_PRODUCT_UNAVAILABLE,
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            return;
        }

        try {
            $offer = SetareganPriceFetcher::fetchOffer(
                $setter->priceFetcher->url,
                Log::channel('single'),
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

                return;
            }

            $this->handleAvailableOffer($setter, $offer);
        } catch (SetareganPageMissingException $exception) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_PAGE_NOT_FOUND,
                'last_checked_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            $this->notifyBalePageMissing($setter);

            return;
        } catch (Throwable $exception) {
            $setter->update([
                'status' => SetareganPriceSetter::STATUS_FETCH_FAILED,
                'last_checked_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

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

        $candidate = $offer['price'] + $setter->margin_amount;
        $target = min($candidate, $setter->max_price);
        $floorHit = $candidate < $setter->min_price;

        $priceChange = null;
        $stockChange = null;

        DB::transaction(function () use ($setter, $offer, $target, $floorHit, &$priceChange, &$stockChange): void {
            $lockedSetter = SetareganPriceSetter::query()->lockForUpdate()->find($setter->getKey());
            $productPrice = ProductPrice::query()
                ->with(['product', 'color', 'warranty'])
                ->lockForUpdate()
                ->find($setter->product_price_id);

            if (! $lockedSetter || ! $lockedSetter->is_active || ! $productPrice?->product?->is_active) {
                return;
            }

            $restored = false;
            if ((float) $productPrice->quantity <= 0) {
                $productPrice->quantity = $lockedSetter->default_quantity;
                $restored = true;
            }

            $regularPrice = (int) $productPrice->price;
            $salePrice = $productPrice->sale_price !== null ? (int) $productPrice->sale_price : null;
            $changed = false;

            if (! $floorHit && ($regularPrice !== $target || $salePrice !== $target)) {
                $oldPrice = ($salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice)
                    ? $salePrice
                    : $regularPrice;

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
        }, 3);

        if (is_array($priceChange)) {
            $this->notifyBalePriceChanged($priceChange);
        }

        if (is_array($stockChange)) {
            $this->notifyBaleStockChanged($stockChange['setter'], $stockChange['quantity']);
        }

        Log::info('Setaregan supplier pricing rule processed.', [
            'setaregan_price_setter_id' => $setter->getKey(),
            'supplier_price' => $offer['price'],
            'target_price' => $target,
            'floor_hit' => $floorHit,
        ]);
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
    }
}
