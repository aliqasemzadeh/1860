<flux:modal name="panel.shop.product.price-fetchers.modal" class="md:w-[600px]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.product_price_fetchers') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.product_price_fetchers_description') }}</flux:text>
        </div>

        @if ($product)
            <div class="space-y-4">
                <!-- Add Price Fetcher Section -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:field>
                        <flux:label>{{ __('general.add_price_fetcher') }}</flux:label>
                        <div class="space-y-3">

                            <flux:field>
                                <flux:select wire:model="type" placeholder="{{ __('general.select_price_fetcher_type') }}">
                                    <flux:select.option value="digikala">{{ __('general.price_fetcher_type_digikala') }}</flux:select.option>
                                    <flux:select.option value="fafait">{{ __('general.price_fetcher_type_fafait') }}</flux:select.option>
                                    <flux:select.option value="markazi">{{ __('general.price_fetcher_type_markazi') }}</flux:select.option>
                                    <flux:select.option value="fater">{{ __('general.price_fetcher_type_fater') }}</flux:select.option>
                                    <flux:select.option value="setaregan">{{ __('general.price_fetcher_type_setaregan') }}</flux:select.option>
                                    <flux:select.option value="technolife">{{ __('general.price_fetcher_type_technolife') }}</flux:select.option>
                                </flux:select>
                                <flux:error name="type" />
                            </flux:field>
                            <flux:field>
                                <flux:input wire:model="url" type="url" placeholder="{{ __('general.price_fetcher_url_placeholder') }}" />
                                <flux:error name="url" />
                            </flux:field>


                            <flux:button wire:click="addPriceFetcher" variant="primary" color="zinc">
                                {{ __('general.add') }}
                            </flux:button>
                        </div>
                    </flux:field>
                </div>

                <!-- Price Fetchers List -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:heading size="sm" class="mb-4">{{ __('general.product_price_fetchers_list') }}</flux:heading>

                    @if ($product->priceFetchers->count() > 0)
                        <div class="space-y-2">
                            @foreach ($product->priceFetchers as $priceFetcher)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">{{ $priceFetcher->type_label }}</span>
                                                @if ($priceFetcher->last_price)
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ number_format($priceFetcher->last_price) }} {{ __('general.toman') }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-500 dark:text-gray-500">
                                                        {{ __('general.price_not_fetched') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 break-all">
                                                {{ $priceFetcher->url }}
                                            </div>
                                            @if ($priceFetcher->last_fetched_at)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('general.last_fetched_at') }}: {{ jalali($priceFetcher->last_fetched_at) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <flux:button
                                                size="xs"
                                                variant="primary"
                                                color="green"
                                                wire:click="fetchPrice({{ $priceFetcher->id }})"
                                            >
                                                {{ __('general.fetch_price') }}
                                            </flux:button>
                                            <flux:button
                                                size="xs"
                                                variant="danger"
                                                wire:click="removePriceFetcher({{ $priceFetcher->id }})"
                                                wire:confirm="{{ __('general.are_you_sure') }}"
                                            >
                                                {{ __('general.delete') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-gray-500 dark:text-gray-400">{{ __('general.no_price_fetchers_added') }}</flux:text>
                    @endif
                </div>
            </div>
        @endif
    </div>
</flux:modal>
