<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Jobs\Shop\PriceFetcher\FetchPriceJob;
use App\Jobs\Shop\TorobPriceSetterJob;
use App\Models\Shop\PriceFetcher;
use App\Models\Shop\Product;
use App\Models\Shop\TorobPriceSetter;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class PriceFetchers extends Component
{
    public ?Product $product = null;

    public ?int $productId = null;

    public ?int $editingPriceFetcherId = null;

    public string $type = 'digikala';

    public string $url = '';

    public ?int $productPriceId = null;

    public string $ownShopNames = 'هجده شصت';

    public string $stepAmount = '';

    public string $minPrice = '';

    public string $maxPrice = '';

    public bool $torobEnabled = true;

    public function mount(): void
    {
        // This component can also be embedded on the public product page.
    }

    #[On('panel.shop.product.price-fetchers.assign-data')]
    public function assignData($id): void
    {
        $this->authorize('shop_access');

        $this->productId = (int) $id;
        $this->refreshProduct();
        $this->resetForm();

        Flux::modal('panel.shop.product.price-fetchers.modal')->show();
    }

    public function updatedType(): void
    {
        if ($this->editingPriceFetcherId !== null) {
            $this->type = 'torob';

            return;
        }

        $this->resetValidation();

        if ($this->type === 'torob' && $this->productPriceId === null) {
            $this->productPriceId = $this->product?->prices
                ->sortByDesc('is_default')
                ->first()?->id;
        }
    }

    public function addPriceFetcher(): void
    {
        $this->authorize('shop_access');

        if (! $this->product || $this->editingPriceFetcherId !== null) {
            return;
        }

        if ($this->type === 'torob') {
            $this->normalizeTorobAmounts();
        }

        $this->validate(
            $this->type === 'torob'
                ? $this->torobValidationRules()
                : [
                    'type' => 'required|in:digikala,fafait,markazi,fater,setaregan,technolife,torob',
                    'url' => ['required', 'url', 'max:500'],
                ],
            [],
            $this->validationAttributes(),
        );

        DB::transaction(function (): void {
            $priceFetcher = $this->product->priceFetchers()->create([
                'type' => $this->type,
                'url' => $this->url,
            ]);

            if ($this->type === 'torob') {
                $priceFetcher->torobPriceSetter()->create([
                    'product_price_id' => $this->productPriceId,
                    'own_shop_names' => $this->parsedOwnShopNames(),
                    'step_amount' => (int) $this->stepAmount,
                    'min_price' => (int) $this->minPrice,
                    'max_price' => (int) $this->maxPrice,
                    'is_active' => $this->torobEnabled,
                ]);
            }
        });

        $this->refreshProduct();
        $this->resetForm();
        Flux::toast(variant: 'success', text: __('general.price_fetcher_added'));
    }

    public function editTorobPriceFetcher(int $priceFetcherId): void
    {
        $this->authorize('shop_access');

        $priceFetcher = $this->findProductPriceFetcher($priceFetcherId);
        $setter = $priceFetcher?->torobPriceSetter;

        if (! $priceFetcher || ! $setter) {
            Flux::toast(variant: 'danger', text: __('general.torob_rule_not_found'));

            return;
        }

        $this->resetValidation();
        $this->editingPriceFetcherId = $priceFetcher->id;
        $this->type = 'torob';
        $this->url = $priceFetcher->url;
        $this->productPriceId = $setter->product_price_id;
        $this->ownShopNames = implode('، ', $setter->own_shop_names ?? []);
        $this->stepAmount = (string) $setter->step_amount;
        $this->minPrice = (string) $setter->min_price;
        $this->maxPrice = (string) $setter->max_price;
        $this->torobEnabled = (bool) $setter->is_active;

        $this->dispatch('panel.shop.product.price-fetchers.scroll-to-form');
    }

    public function updateTorobPriceFetcher(): void
    {
        $this->authorize('shop_access');

        if (! $this->product || $this->editingPriceFetcherId === null) {
            return;
        }

        $priceFetcher = $this->findProductPriceFetcher($this->editingPriceFetcherId);
        $setter = $priceFetcher?->torobPriceSetter;

        if (! $priceFetcher || ! $setter) {
            Flux::toast(variant: 'danger', text: __('general.torob_rule_not_found'));
            $this->resetForm();

            return;
        }

        $this->type = 'torob';
        $this->normalizeTorobAmounts();
        $this->validate($this->torobValidationRules($setter), [], $this->validationAttributes());

        DB::transaction(function () use ($priceFetcher, $setter): void {
            $priceFetcher->update([
                'url' => $this->url,
            ]);

            $setter->update([
                'product_price_id' => $this->productPriceId,
                'own_shop_names' => $this->parsedOwnShopNames(),
                'step_amount' => (int) $this->stepAmount,
                'min_price' => (int) $this->minPrice,
                'max_price' => (int) $this->maxPrice,
                'is_active' => $this->torobEnabled,
                'status' => $this->torobEnabled
                    ? TorobPriceSetter::STATUS_IDLE
                    : TorobPriceSetter::STATUS_INACTIVE,
                'last_error' => null,
            ]);
        });

        $this->refreshProduct();
        $this->resetForm();
        Flux::toast(variant: 'success', text: __('general.torob_policy_updated'));
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function removePriceFetcher(int $priceFetcherId): void
    {
        $this->authorize('shop_access');

        if (! $this->product) {
            return;
        }

        PriceFetcher::where('product_id', $this->product->id)
            ->where('id', $priceFetcherId)
            ->delete();

        if ($this->editingPriceFetcherId === $priceFetcherId) {
            $this->resetForm();
        }

        $this->refreshProduct();
        Flux::toast(variant: 'success', text: __('general.price_fetcher_removed'));
    }

    public function fetchPrice(int $priceFetcherId): void
    {
        $this->authorize('shop_access');

        if (! $this->product) {
            return;
        }

        $priceFetcher = PriceFetcher::where('product_id', $this->product->id)
            ->where('id', $priceFetcherId)
            ->first();

        if (! $priceFetcher) {
            Flux::toast(variant: 'danger', text: __('general.price_fetcher_not_found'));

            return;
        }

        try {
            FetchPriceJob::dispatch($priceFetcher)->onConnection('sync');
            $this->refreshProduct();
            Flux::toast(variant: 'success', text: __('general.price_fetcher_fetched'));
        } catch (\Throwable $exception) {
            Flux::toast(variant: 'danger', text: __('general.price_fetcher_fetch_failed').': '.$exception->getMessage());
        }
    }

    public function runTorobPriceSetter(int $priceFetcherId): void
    {
        $this->authorize('shop_access');

        $priceSetter = $this->findProductPriceFetcher($priceFetcherId)?->torobPriceSetter;

        if (! $priceSetter) {
            Flux::toast(variant: 'danger', text: __('general.torob_rule_not_found'));

            return;
        }

        try {
            TorobPriceSetterJob::dispatch($priceSetter)->onConnection('sync');
            $this->refreshProduct();
            Flux::toast(variant: 'success', text: __('general.torob_rule_ran'));
        } catch (\Throwable $exception) {
            $this->refreshProduct();
            Flux::toast(variant: 'danger', text: __('general.torob_rule_run_failed').': '.$exception->getMessage());
        }
    }

    public function toggleTorobPriceSetter(int $priceFetcherId): void
    {
        $this->authorize('shop_access');

        $priceSetter = $this->findProductPriceFetcher($priceFetcherId)?->torobPriceSetter;

        if (! $priceSetter) {
            Flux::toast(variant: 'danger', text: __('general.torob_rule_not_found'));

            return;
        }

        $isActive = ! $priceSetter->is_active;
        $priceSetter->update([
            'is_active' => $isActive,
            'status' => $isActive
                ? TorobPriceSetter::STATUS_IDLE
                : TorobPriceSetter::STATUS_INACTIVE,
            'last_error' => null,
        ]);
        $this->refreshProduct();
        Flux::toast(variant: 'success', text: __('general.torob_rule_toggled'));
    }

    public function formatNumber(int|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $formatted = number_format((int) $value);

        return app()->isLocale('fa')
            ? strtr($formatted, [
                '0' => '۰',
                '1' => '۱',
                '2' => '۲',
                '3' => '۳',
                '4' => '۴',
                '5' => '۵',
                '6' => '۶',
                '7' => '۷',
                '8' => '۸',
                '9' => '۹',
            ])
            : $formatted;
    }

    private function findProductPriceFetcher(int $priceFetcherId): ?PriceFetcher
    {
        if (! $this->product) {
            return null;
        }

        return PriceFetcher::query()
            ->with('torobPriceSetter')
            ->whereBelongsTo($this->product)
            ->whereKey($priceFetcherId)
            ->where('type', 'torob')
            ->first();
    }

    private function refreshProduct(): void
    {
        $this->product = Product::with([
            'prices.color',
            'prices.warranty',
            'priceFetchers.torobPriceSetter.productPrice.color',
            'priceFetchers.torobPriceSetter.productPrice.warranty',
        ])->findOrFail($this->productId);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingPriceFetcherId = null;
        $this->type = 'digikala';
        $this->url = '';
        $this->productPriceId = null;
        $this->ownShopNames = 'هجده شصت';
        $this->stepAmount = '';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->torobEnabled = true;
    }

    /** @return array<string, mixed> */
    private function torobValidationRules(?TorobPriceSetter $ignoreSetter = null): array
    {
        $uniqueProductPrice = Rule::unique('torob_price_setters', 'product_price_id');

        if ($ignoreSetter) {
            $uniqueProductPrice->ignore($ignoreSetter->id);
        }

        return [
            'type' => 'required|in:torob',
            'url' => [
                'required',
                'url',
                'max:500',
                'regex:~^https?://(?:www\.)?torob\.com/p/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}(?:/|$)~i',
            ],
            'productPriceId' => [
                'required',
                'integer',
                Rule::exists('product_prices', 'id')->where(
                    fn ($query) => $query
                        ->where('product_id', $this->product->id)
                        ->whereNull('deleted_at')
                ),
                $uniqueProductPrice,
            ],
            'ownShopNames' => ['required', 'string', 'max:1000'],
            'stepAmount' => ['required', 'integer', 'min:1'],
            'minPrice' => ['required', 'integer', 'min:0'],
            'maxPrice' => ['required', 'integer', 'gte:minPrice'],
            'torobEnabled' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    private function validationAttributes(): array
    {
        return [
            'type' => __('general.price_fetcher_type'),
            'url' => __('general.price_fetcher_url'),
            'productPriceId' => __('general.torob_target_variant'),
            'ownShopNames' => __('general.torob_own_shop_names'),
            'stepAmount' => __('general.torob_step_amount'),
            'minPrice' => __('general.torob_min_price'),
            'maxPrice' => __('general.torob_max_price'),
            'torobEnabled' => __('general.torob_enabled'),
        ];
    }

    private function normalizeTorobAmounts(): void
    {
        $persianDigits = array_combine(mb_str_split('۰۱۲۳۴۵۶۷۸۹'), range(0, 9));
        $arabicDigits = array_combine(mb_str_split('٠١٢٣٤٥٦٧٨٩'), range(0, 9));

        foreach (['stepAmount', 'minPrice', 'maxPrice'] as $property) {
            $normalized = strtr($this->{$property}, $persianDigits + $arabicDigits);
            $this->{$property} = preg_replace('/[^0-9]/', '', $normalized) ?? '';
        }
    }

    /** @return array<int, string> */
    private function parsedOwnShopNames(): array
    {
        return collect(preg_split('/[,،\n]+/u', $this->ownShopNames) ?: [])
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.price-fetchers');
    }
}
