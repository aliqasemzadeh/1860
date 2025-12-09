<flux:modal name="shop.product.pricing.history.modal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.price_history') }} {{ $product->name ?? "" }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.price_history_description') }}</flux:text>
        </div>

        @if ($product)
            <flux:card>
                <flux:chart class="grid gap-6" wire:model="data">
                    <flux:chart.summary class="flex gap-12">
                        <div>
                            <flux:text>Today</flux:text>
                            <flux:heading size="xl" class="mt-2 tabular-nums">
                                <flux:chart.summary.value field="sales" :format="['style' => 'currency', 'currency' => 'USD']" />
                            </flux:heading>
                            <flux:text class="mt-2 tabular-nums">
                                <flux:chart.summary.value field="date" :format="['hour' => 'numeric', 'minute' => 'numeric', 'hour12' => true]" />
                            </flux:text>
                        </div>
                        <div>
                            <flux:text>Yesterday</flux:text>
                            <flux:heading size="lg" class="mt-2 tabular-nums">
                                <flux:chart.summary.value field="yesterday" :format="['style' => 'currency', 'currency' => 'USD']" />
                            </flux:heading>
                        </div>
                    </flux:chart.summary>
                    <flux:chart.viewport class="aspect-[3/1]">
                        <flux:chart.svg>
                            <flux:chart.line field="yesterday" class="text-zinc-300 dark:text-white/40" stroke-dasharray="4 4" curve="none" />
                            <flux:chart.line field="sales" class="text-sky-500 dark:text-sky-400" curve="none" />
                            <flux:chart.axis axis="x" field="date">
                                <flux:chart.axis.grid />
                                <flux:chart.axis.tick />
                                <flux:chart.axis.line />
                            </flux:chart.axis>
                            <flux:chart.axis axis="y">
                                <flux:chart.axis.tick />
                            </flux:chart.axis>
                            <flux:chart.cursor />
                        </flux:chart.svg>
                    </flux:chart.viewport>
                </flux:chart>
            </flux:card>
        @endif
    </div>
</flux:modal>
