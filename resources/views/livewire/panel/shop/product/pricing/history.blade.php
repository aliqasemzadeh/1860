<flux:modal name="panel.shop.product.pricing.history.modal" class="min-h-full min-w-full">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.price_history') }}</flux:heading>
            <flux:text class="mt-2">
                {{ $product->name }}
                @if ($this->colorName)
                    - {{ __('app.color') }}: {{ $this->colorName }}
                @endif
                @if ($this->warrantyName)
                    - {{ __('app.warranty') }}: {{ $this->warrantyName }}
                @endif
            </flux:text>
        </div>

        @if ($this->priceHistory->isEmpty())
            <div class="text-center py-12">
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('app.no_prices_found') }}</flux:text>
            </div>
        @else
            <flux:card>
                <flux:chart class="grid gap-6" :value="$this->chartData()">
                    <flux:chart.summary class="flex gap-12">
                        <div>
                            <flux:text>{{ __('app.price_today') }}</flux:text>
                            <flux:heading size="xl" class="mt-2 tabular-nums">
                                @if ($this->priceHistory->isNotEmpty())
                                    {{ number_format($this->priceHistory->last()->price, 0) }} {{ __('general.toman') }}
                                @else
                                    -
                                @endif
                            </flux:heading>
                        </div>
                        <div>
                            <flux:text>{{ __('app.sale_price_yesterday') }}</flux:text>
                            <flux:heading size="lg" class="mt-2 tabular-nums">
                                @if ($this->priceHistory->isNotEmpty() && $this->priceHistory->last()->sale_price)
                                    {{ number_format($this->priceHistory->last()->sale_price, 0) }} {{ __('general.toman') }}
                                @else
                                    -
                                @endif
                            </flux:heading>
                        </div>
                    </flux:chart.summary>
                    <flux:chart.viewport class="aspect-[3/1]">
                        <flux:chart.svg>
                            <flux:chart.line field="sale_price" class="text-zinc-300 dark:text-white/40" stroke-dasharray="4 4" curve="none" />
                            <flux:chart.line field="price" class="text-sky-500 dark:text-sky-400" curve="none" />
                            <flux:chart.axis axis="x" field="date">
                                <flux:chart.axis.grid />
                                <flux:chart.axis.line />
                            </flux:chart.axis>
                            <flux:chart.axis axis="y">
                                <flux:chart.axis.tick :format='["style" => "currency", "currency" => "IRR", "minimumFractionDigits" => 0]' />
                            </flux:chart.axis>
                            <flux:chart.cursor />
                        </flux:chart.svg>
                    </flux:chart.viewport>
                </flux:chart>
            </flux:card>

            <!-- Price history table below the chart -->
            <flux:card class="mt-6">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('app.date') }}</flux:table.column>
                        <flux:table.column>{{ __('app.price') }}</flux:table.column>
                        <flux:table.column>{{ __('app.sale_price') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->priceHistory as $row)
                            <flux:table.row>
                                <flux:table.cell>
                                    {{ jalali($row->created_at) }}
                                </flux:table.cell>
                                <flux:table.cell class="tabular-nums">
                                    {{ number_format($row->price, 0) }} {{ __('general.toman') }}
                                </flux:table.cell>
                                <flux:table.cell class="tabular-nums">
                                    @if ($row->sale_price)
                                        {{ number_format($row->sale_price, 0) }} {{ __('general.toman') }}
                                    @else
                                        -
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif
    </div>
</flux:modal>
