<flux:modal name="panel.shop.product.price-fetchers.modal" class="md:w-[760px]" flyout position="right">
    <div class="space-y-6">
        <header>
            <div class="flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <flux:icon name="arrow-path-rounded-square" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('general.product_price_fetchers') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('general.product_price_fetchers_description') }}</flux:text>
                </div>
            </div>
        </header>

        @if ($product)
            <section aria-labelledby="new-price-fetcher-heading" class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading id="new-price-fetcher-heading" size="sm">{{ __('general.add_price_fetcher') }}</flux:heading>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('general.price_fetcher_type') }}</flux:label>
                        <flux:select wire:model.live="type" placeholder="{{ __('general.select_price_fetcher_type') }}">
                            <flux:select.option value="digikala">{{ __('general.price_fetcher_type_digikala') }}</flux:select.option>
                            <flux:select.option value="fafait">{{ __('general.price_fetcher_type_fafait') }}</flux:select.option>
                            <flux:select.option value="markazi">{{ __('general.price_fetcher_type_markazi') }}</flux:select.option>
                            <flux:select.option value="fater">{{ __('general.price_fetcher_type_fater') }}</flux:select.option>
                            <flux:select.option value="setaregan">{{ __('general.price_fetcher_type_setaregan') }}</flux:select.option>
                            <flux:select.option value="technolife">{{ __('general.price_fetcher_type_technolife') }}</flux:select.option>
                            <flux:select.option value="torob">{{ __('general.price_fetcher_type_torob') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('general.price_fetcher_url') }}</flux:label>
                        <flux:input
                            wire:model="url"
                            type="url"
                            dir="ltr"
                            placeholder="{{ $type === 'torob' ? 'https://torob.com/p/...' : __('general.price_fetcher_url_placeholder') }}"
                        />
                        <flux:error name="url" />
                    </flux:field>
                </div>

                @if ($type === 'torob')
                    <div class="mt-5 overflow-hidden rounded-xl border border-rose-200 bg-rose-50/40 dark:border-rose-900/70 dark:bg-rose-950/15">
                        <div class="flex items-start justify-between gap-4 border-b border-rose-200 px-4 py-3 dark:border-rose-900/70">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-rose-500"></span>
                                    <flux:heading size="sm">{{ __('general.torob_competitive_policy') }}</flux:heading>
                                </div>
                                <p class="mt-1 text-xs leading-5 text-zinc-600 dark:text-zinc-400">
                                    {{ __('general.torob_competitive_policy_help') }}
                                </p>
                            </div>
                            <flux:switch wire:model="torobEnabled" :label="__('general.torob_enabled')" />
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-2">
                            <flux:field class="sm:col-span-2">
                                <flux:label>{{ __('general.torob_target_variant') }}</flux:label>
                                <flux:select wire:model="productPriceId" placeholder="{{ __('general.torob_select_target_variant') }}">
                                    @foreach ($product->prices->sortByDesc('is_default') as $price)
                                        <flux:select.option :value="$price->id">
                                            #{{ $this->formatNumber($price->id) }}
                                            · {{ $price->color?->name ?? __('general.none') }}
                                            · {{ $price->warranty?->name ?? __('general.none') }}
                                            · {{ $this->formatNumber($price->sale_price ?: $price->price) }} {{ __('general.toman') }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="productPriceId" />
                                @if ($product->prices->isEmpty())
                                    <p class="mt-1 text-xs font-medium text-amber-700 dark:text-amber-400">{{ __('general.torob_no_target_variants') }}</p>
                                @endif
                            </flux:field>

                            <flux:field class="sm:col-span-2">
                                <flux:label>{{ __('general.torob_own_shop_names') }}</flux:label>
                                <flux:input wire:model="ownShopNames" type="text" placeholder="{{ __('general.torob_own_shop_names_placeholder') }}" />
                                <flux:description>{{ __('general.torob_own_shop_names_help') }}</flux:description>
                                <flux:error name="ownShopNames" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('general.torob_step_amount') }}</flux:label>
                                <flux:input wire:model="stepAmount" type="text" inputmode="numeric" dir="ltr" placeholder="100,000" mask:dynamic="$money($input)" />
                                <flux:error name="stepAmount" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('general.torob_min_price') }}</flux:label>
                                <flux:input wire:model="minPrice" type="text" inputmode="numeric" dir="ltr" placeholder="18,000,000" mask:dynamic="$money($input)" />
                                <flux:error name="minPrice" />
                            </flux:field>

                            <flux:field class="sm:col-start-2">
                                <flux:label>{{ __('general.torob_max_price') }}</flux:label>
                                <flux:input wire:model="maxPrice" type="text" inputmode="numeric" dir="ltr" placeholder="24,000,000" mask:dynamic="$money($input)" />
                                <flux:error name="maxPrice" />
                            </flux:field>
                        </div>
                    </div>
                @endif

                <div class="mt-4 flex justify-end">
                    <flux:button
                        wire:click="addPriceFetcher"
                        wire:loading.attr="disabled"
                        wire:target="addPriceFetcher"
                        variant="primary"
                        color="{{ $type === 'torob' ? 'rose' : 'zinc' }}"
                    >
                        <span wire:loading.remove wire:target="addPriceFetcher">{{ __('general.add') }}</span>
                        <span wire:loading wire:target="addPriceFetcher">{{ __('general.saving') }}</span>
                    </flux:button>
                </div>
            </section>

            <section aria-labelledby="price-fetchers-list-heading">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <flux:heading id="price-fetchers-list-heading" size="sm">{{ __('general.product_price_fetchers_list') }}</flux:heading>
                    <span class="text-xs tabular-nums text-zinc-500 dark:text-zinc-400">
                        {{ $this->formatNumber($product->priceFetchers->count()) }} {{ __('general.items') }}
                    </span>
                </div>

                @if ($product->priceFetchers->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($product->priceFetchers as $priceFetcher)
                            @php
                                $setter = $priceFetcher->type === 'torob' ? $priceFetcher->torobPriceSetter : null;
                                $status = $setter?->status ?: 'idle';
                                $statusColor = match ($status) {
                                    'updated', 'unchanged' => 'green',
                                    'floor_reached', 'no_competitor', 'product_unavailable' => 'amber',
                                    'fetch_failed' => 'red',
                                    default => 'zinc',
                                };
                                $targetPrice = $setter?->productPrice;
                                $effectiveTargetPrice = $targetPrice?->sale_price ?: $targetPrice?->price;
                            @endphp

                            <article
                                wire:key="price-fetcher-{{ $priceFetcher->id }}"
                                class="overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-zinc-900 {{ $setter ? 'border-rose-200 dark:border-rose-900/70' : 'border-zinc-200 dark:border-zinc-700' }}"
                            >
                                <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $priceFetcher->type_label }}</span>
                                            @if ($setter)
                                                <flux:badge size="sm" :color="$setter->is_active ? 'green' : 'zinc'">
                                                    {{ $setter->is_active ? __('general.active') : __('general.inactive') }}
                                                </flux:badge>
                                                <flux:badge size="sm" :color="$statusColor">
                                                    {{ __('general.torob_status_'.$status) }}
                                                </flux:badge>
                                            @elseif ($priceFetcher->last_price)
                                                <span class="text-sm font-medium tabular-nums text-zinc-700 dark:text-zinc-300">
                                                    {{ $this->formatNumber($priceFetcher->last_price) }} {{ __('general.toman') }}
                                                </span>
                                            @else
                                                <span class="text-sm text-zinc-500">{{ __('general.price_not_fetched') }}</span>
                                            @endif
                                        </div>

                                        <a
                                            href="{{ $priceFetcher->url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            dir="ltr"
                                            class="mt-1 block truncate text-xs text-zinc-500 underline decoration-zinc-300 underline-offset-2 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:text-zinc-400 dark:hover:text-zinc-200"
                                        >
                                            {{ $priceFetcher->url }}
                                        </a>
                                    </div>

                                    <div class="flex w-full shrink-0 items-center justify-between gap-2 sm:w-auto sm:justify-end">
                                        @if ($setter)
                                            <flux:button
                                                size="xs"
                                                variant="primary"
                                                color="rose"
                                                icon="arrow-path"
                                                wire:click="runTorobPriceSetter({{ $priceFetcher->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="runTorobPriceSetter({{ $priceFetcher->id }})"
                                            >
                                                {{ __('general.torob_run_now') }}
                                            </flux:button>
                                            <div class="flex items-center gap-1">
                                                <flux:tooltip content="{{ $setter->is_active ? __('general.disable') : __('general.enable') }}">
                                                    <flux:button
                                                        size="xs"
                                                        variant="ghost"
                                                        icon="{{ $setter->is_active ? 'pause-circle' : 'play-circle' }}"
                                                        icon:variant="outline"
                                                        aria-label="{{ $setter->is_active ? __('general.disable') : __('general.enable') }}"
                                                        wire:click="toggleTorobPriceSetter({{ $priceFetcher->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="toggleTorobPriceSetter({{ $priceFetcher->id }})"
                                                    />
                                                </flux:tooltip>

                                                <flux:tooltip content="{{ __('general.delete') }}">
                                                    <flux:button
                                                        size="xs"
                                                        variant="danger"
                                                        icon="trash"
                                                        icon:variant="outline"
                                                        aria-label="{{ __('general.delete') }}"
                                                        wire:click="removePriceFetcher({{ $priceFetcher->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="removePriceFetcher({{ $priceFetcher->id }})"
                                                        wire:confirm="{{ __('general.are_you_sure') }}"
                                                    />
                                                </flux:tooltip>
                                            </div>
                                        @else
                                            <flux:button
                                                size="xs"
                                                variant="primary"
                                                color="green"
                                                wire:click="fetchPrice({{ $priceFetcher->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="fetchPrice({{ $priceFetcher->id }})"
                                            >
                                                {{ __('general.fetch_price') }}
                                            </flux:button>
                                            <flux:button
                                                size="xs"
                                                variant="danger"
                                                wire:click="removePriceFetcher({{ $priceFetcher->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="removePriceFetcher({{ $priceFetcher->id }})"
                                                wire:confirm="{{ __('general.are_you_sure') }}"
                                            >
                                                {{ __('general.delete') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>

                                @if ($setter)
                                    <div class="border-t border-rose-100 bg-zinc-50/70 px-4 py-3 dark:border-rose-950 dark:bg-zinc-950/40">
                                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                                {{ __('general.torob_target_variant') }}:
                                                #{{ $this->formatNumber($targetPrice?->id) }}
                                                · {{ $targetPrice?->color?->name ?? __('general.none') }}
                                                · {{ $targetPrice?->warranty?->name ?? __('general.none') }}
                                            </span>
                                            <span class="text-zinc-500 dark:text-zinc-400">
                                                {{ __('general.torob_step') }}:
                                                <strong class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $this->formatNumber($setter->step_amount) }}</strong>
                                                {{ __('general.toman') }}
                                            </span>
                                        </div>

                                        <ol class="mt-3 overflow-hidden rounded-lg border border-zinc-200 bg-white sm:hidden dark:border-zinc-700 dark:bg-zinc-900">
                                            <li class="flex min-w-0 items-center justify-between gap-3 px-3 py-2.5">
                                                <div class="flex min-w-0 items-center gap-2">
                                                    <span class="size-2 shrink-0 rounded-full bg-amber-500"></span>
                                                    <span class="truncate text-[11px] font-medium text-zinc-600 dark:text-zinc-300">{{ __('general.torob_floor') }}</span>
                                                </div>
                                                <span dir="ltr" class="shrink-0 whitespace-nowrap text-xs font-bold tabular-nums text-amber-700 dark:text-amber-400">{{ $this->formatNumber($setter->min_price) }}</span>
                                            </li>
                                            <li class="flex min-w-0 items-center justify-between gap-3 border-y border-zinc-100 bg-zinc-50/70 px-3 py-2.5 dark:border-zinc-800 dark:bg-zinc-950/30">
                                                <div class="flex min-w-0 items-center gap-2">
                                                    <span class="size-2 shrink-0 rounded-full bg-rose-500 ring-4 ring-rose-100 dark:ring-rose-950"></span>
                                                    <span class="truncate text-[11px] font-semibold text-zinc-800 dark:text-zinc-100">{{ __('general.torob_target_price') }}</span>
                                                </div>
                                                <span dir="ltr" class="shrink-0 whitespace-nowrap text-xs font-bold tabular-nums text-zinc-950 dark:text-white">{{ $this->formatNumber($setter->last_target_price ?? $effectiveTargetPrice) }}</span>
                                            </li>
                                            <li class="flex min-w-0 items-center justify-between gap-3 px-3 py-2.5">
                                                <div class="flex min-w-0 items-center gap-2">
                                                    <span class="size-2 shrink-0 rounded-full bg-zinc-400"></span>
                                                    <span class="truncate text-[11px] font-medium text-zinc-600 dark:text-zinc-300">{{ __('general.torob_ceiling') }}</span>
                                                </div>
                                                <span dir="ltr" class="shrink-0 whitespace-nowrap text-xs font-bold tabular-nums text-zinc-700 dark:text-zinc-300">{{ $this->formatNumber($setter->max_price) }}</span>
                                            </li>
                                        </ol>

                                        <div class="mt-3 hidden grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-center sm:grid dark:border-zinc-700 dark:bg-zinc-900">
                                            <div>
                                                <div class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.torob_floor') }}</div>
                                                <div dir="ltr" class="mt-0.5 truncate whitespace-nowrap text-xs font-bold tabular-nums text-amber-700 dark:text-amber-400">{{ $this->formatNumber($setter->min_price) }}</div>
                                            </div>
                                            <flux:icon name="chevron-left" class="size-4 text-zinc-300 dark:text-zinc-600 rtl:rotate-180" />
                                            <div>
                                                <div class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.torob_target_price') }}</div>
                                                <div dir="ltr" class="mt-0.5 truncate whitespace-nowrap text-xs font-bold tabular-nums text-zinc-900 dark:text-white">{{ $this->formatNumber($setter->last_target_price ?? $effectiveTargetPrice) }}</div>
                                            </div>
                                            <flux:icon name="chevron-left" class="size-4 text-zinc-300 dark:text-zinc-600 rtl:rotate-180" />
                                            <div>
                                                <div class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.torob_ceiling') }}</div>
                                                <div dir="ltr" class="mt-0.5 truncate whitespace-nowrap text-xs font-bold tabular-nums text-zinc-700 dark:text-zinc-300">{{ $this->formatNumber($setter->max_price) }}</div>
                                            </div>
                                        </div>

                                        <dl class="mt-3 grid gap-px overflow-hidden rounded-lg border border-zinc-200 bg-zinc-200 sm:grid-cols-3 dark:border-zinc-700 dark:bg-zinc-700">
                                            <div class="bg-white px-3 py-2 dark:bg-zinc-900">
                                                <dt class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ __('general.torob_latest_competitor') }}</dt>
                                                <dd class="mt-1 text-sm font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
                                                    {{ $this->formatNumber($setter->last_competitor_price) }}
                                                    @if ($setter->last_competitor_price) <span class="text-[10px] font-normal">{{ __('general.toman') }}</span> @endif
                                                </dd>
                                                @if ($setter->last_competitor_shop)
                                                    <dd class="mt-0.5 truncate text-[10px] text-zinc-500 dark:text-zinc-400">{{ $setter->last_competitor_shop }}</dd>
                                                @endif
                                            </div>
                                            <div class="bg-white px-3 py-2 dark:bg-zinc-900">
                                                <dt class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ __('general.torob_calculated_target') }}</dt>
                                                <dd class="mt-1 text-sm font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
                                                    {{ $this->formatNumber($setter->last_target_price) }}
                                                    @if ($setter->last_target_price) <span class="text-[10px] font-normal">{{ __('general.toman') }}</span> @endif
                                                </dd>
                                            </div>
                                            <div class="bg-white px-3 py-2 dark:bg-zinc-900">
                                                <dt class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ __('general.torob_applied_price') }}</dt>
                                                <dd class="mt-1 text-sm font-semibold tabular-nums text-green-700 dark:text-green-400">
                                                    {{ $this->formatNumber($setter->last_applied_price) }}
                                                    @if ($setter->last_applied_price) <span class="text-[10px] font-normal">{{ __('general.toman') }}</span> @endif
                                                </dd>
                                            </div>
                                        </dl>

                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-zinc-500 dark:text-zinc-400">
                                            <span>
                                                {{ __('general.torob_last_checked') }}:
                                                {{ $setter->last_checked_at ? jalali($setter->last_checked_at) : __('general.never') }}
                                            </span>
                                            @if ($setter->last_changed_at)
                                                <span>{{ __('general.torob_last_changed') }}: {{ jalali($setter->last_changed_at) }}</span>
                                            @endif
                                        </div>

                                        @if ($setter->last_error)
                                            <div role="alert" class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs leading-5 text-red-800 dark:border-red-900/70 dark:bg-red-950/30 dark:text-red-300">
                                                <span class="font-semibold">{{ __('general.error') }}:</span>
                                                {{ $setter->last_error }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($priceFetcher->last_fetched_at)
                                    <div class="border-t border-zinc-100 px-4 py-2 text-xs text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                        {{ __('general.last_fetched_at') }}: {{ jalali($priceFetcher->last_fetched_at) }}
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-zinc-300 px-6 py-9 text-center dark:border-zinc-700">
                        <span class="mx-auto flex size-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <flux:icon name="link" class="size-5" />
                        </span>
                        <flux:heading size="sm" class="mt-3">{{ __('general.no_price_fetchers_added') }}</flux:heading>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('general.torob_empty_state_help') }}</p>
                    </div>
                @endif
            </section>
        @endif
    </div>
</flux:modal>
