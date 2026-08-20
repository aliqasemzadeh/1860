<?php

namespace App\Livewire\Panel\Shop\Product\Pricing;

use App\Models\Shop\ProductPrice;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class BulkChange extends Component
{
    /** @var array<int> */
    #[Locked]
    public array $productIds = [];

    public string $adjustmentType = 'percentage_increase';

    public string $priceTarget = 'price';

    public string $value = '';

    #[On('panel.shop.product.pricing.bulk-change.assign-data')]
    public function assignData(array $productIds): void
    {
        $this->productIds = array_values(array_map('intval', $productIds));
        $this->resetForm();
        Flux::modal('panel.shop.product.pricing.bulk-change.modal')->show();
    }

    public function resetForm(): void
    {
        $this->adjustmentType = 'percentage_increase';
        $this->priceTarget = 'price';
        $this->value = '';
        $this->resetValidation();
    }

    public function apply(): void
    {
        if ($this->productIds === []) {
            Flux::toast(variant: 'danger', text: __('general.no_products_selected'));

            return;
        }

        $validated = $this->validate([
            'adjustmentType' => ['required', 'in:percentage_increase,percentage_decrease,fixed_increase,fixed_decrease,set_price'],
            'priceTarget' => ['required', 'in:price,sale_price,both'],
            'value' => ['required'],
        ], [
            'value.required' => __('general.adjustment_value_required'),
        ]);

        $numericValue = (float) str_replace(',', '', $validated['value']);

        if ($numericValue <= 0 && $validated['adjustmentType'] !== 'set_price') {
            $this->addError('value', __('general.adjustment_value_must_be_positive'));

            return;
        }

        if ($validated['adjustmentType'] === 'set_price' && $numericValue < 0) {
            $this->addError('value', __('general.price_must_be_numeric'));

            return;
        }

        $updatedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (&$updatedCount, &$skippedCount, $numericValue, $validated): void {
            foreach ($this->productIds as $productId) {
                $latestPrices = $this->getLatestPricesForProduct($productId);

                if ($latestPrices->isEmpty()) {
                    $skippedCount++;

                    continue;
                }

                foreach ($latestPrices as $sourcePrice) {
                    $newPrice = (float) $sourcePrice->price;
                    $newSalePrice = $sourcePrice->sale_price !== null ? (float) $sourcePrice->sale_price : null;

                    if (in_array($validated['priceTarget'], ['price', 'both'], true)) {
                        $newPrice = $this->applyAdjustment($newPrice, $validated['adjustmentType'], $numericValue);
                    }

                    if (in_array($validated['priceTarget'], ['sale_price', 'both'], true)) {
                        if ($newSalePrice !== null || $validated['adjustmentType'] === 'set_price') {
                            $baseSalePrice = $newSalePrice ?? 0.0;
                            $newSalePrice = $this->applyAdjustment($baseSalePrice, $validated['adjustmentType'], $numericValue);
                        }
                    }

                    if ($sourcePrice->is_default) {
                        ProductPrice::query()
                            ->where('product_id', $productId)
                            ->when(
                                $sourcePrice->color_id,
                                fn ($query) => $query->where('color_id', $sourcePrice->color_id),
                                fn ($query) => $query->whereNull('color_id')
                            )
                            ->when(
                                $sourcePrice->warranty_id,
                                fn ($query) => $query->where('warranty_id', $sourcePrice->warranty_id),
                                fn ($query) => $query->whereNull('warranty_id')
                            )
                            ->update(['is_default' => false]);
                    }

                    ProductPrice::create([
                        'product_id' => $productId,
                        'color_id' => $sourcePrice->color_id,
                        'warranty_id' => $sourcePrice->warranty_id,
                        'price' => $newPrice,
                        'sale_price' => $newSalePrice,
                        'quantity' => $sourcePrice->quantity,
                        'is_default' => $sourcePrice->is_default,
                    ]);

                    $updatedCount++;
                }
            }
        });

        Flux::modal('panel.shop.product.pricing.bulk-change.modal')->close();

        if ($updatedCount > 0) {
            Flux::toast(
                variant: 'success',
                text: __('general.bulk_price_updated', ['count' => number_format($updatedCount)])
            );
        }

        if ($skippedCount > 0 && $updatedCount === 0) {
            Flux::toast(variant: 'danger', text: __('general.bulk_price_skipped_no_price'));
        } elseif ($skippedCount > 0) {
            Flux::toast(
                variant: 'warning',
                text: __('general.bulk_price_partial_skipped', ['count' => number_format($skippedCount)])
            );
        }

        $this->dispatch('panel.shop.product.index.clear-selection');
        $this->dispatch('panel.shop.product.index.render');
    }

    protected function getLatestPricesForProduct(int $productId): Collection
    {
        return ProductPrice::query()
            ->where('product_id', $productId)
            ->get()
            ->groupBy(fn (ProductPrice $price) => ($price->color_id ?? 'null').'-'.($price->warranty_id ?? 'null'))
            ->map(fn (Collection $group) => $group->sortByDesc('created_at')->first())
            ->values();
    }

    protected function applyAdjustment(float $currentPrice, string $type, float $value): float
    {
        $newPrice = match ($type) {
            'percentage_increase' => $currentPrice * (1 + ($value / 100)),
            'percentage_decrease' => $currentPrice * (1 - ($value / 100)),
            'fixed_increase' => $currentPrice + $value,
            'fixed_decrease' => $currentPrice - $value,
            'set_price' => $value,
            default => $currentPrice,
        };

        return max(0, round($newPrice));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.pricing.bulk-change');
    }
}
