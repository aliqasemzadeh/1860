<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="{{ route('shop.product.index') }}" wire:navigate>{{ __('app.products') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $product->name }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ __('app.pricing') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.pricing') }} - {{ $product->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.pricing_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="shop.product.pricing.create.modal">
                <flux:button variant="primary">{{ __('app.create_price') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>
    <livewire:shop.product.pricing.create :productId="$productId" />

    @if ($this->latestPrices->isEmpty())
        <div class="text-center py-12">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('app.no_prices_found') }}</flux:text>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('app.color') }}</flux:table.column>
                <flux:table.column>{{ __('app.warranty') }}</flux:table.column>
                <flux:table.column>{{ __('app.price') }}</flux:table.column>
                <flux:table.column>{{ __('app.sale_price') }}</flux:table.column>
                <flux:table.column>{{ __('app.quantity') }}</flux:table.column>
                <flux:table.column>{{ __('app.is_default') }}</flux:table.column>
                <flux:table.column>{{ __('app.created_at') }}</flux:table.column>
                <flux:table.column>{{ __('app.options') }}</flux:table.column>
            </flux:table.columns>

            @foreach ($this->latestPrices as $price)
                <flux:table.row :key="$price->id">
                    <flux:table.cell class="whitespace-nowrap">
                        @if ($price->color)
                            <div class="flex items-center gap-2">
                                @if($price->color->hex)
                                    <div class="w-4 h-4 rounded border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $price->color->hex }}"></div>
                                @endif
                                <span>{{ $price->color->name }}</span>
                            </div>
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">{{ __('app.none') }}</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $price->warranty?->name ?? __('app.none') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ number_format($price->price, 0) }} {{ __('app.toman') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @if ($price->sale_price)
                            {{ number_format($price->sale_price, 0) }} {{ __('app.toman') }}
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ number_format($price->quantity, 0) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @if ($price->is_default)
                            <flux:badge variant="success">{{ __('app.yes') }}</flux:badge>
                        @else
                            <flux:badge variant="ghost">{{ __('app.no') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $price->created_at?->format('Y-m-d H:i') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <flux:button size="xs" variant="primary" wire:click="$dispatch('shop.product.pricing.edit.assign-data', { id: '{{ $price->id }}' })">{{ __('app.edit') }}</flux:button>
                            <flux:button size="xs" variant="primary" color="sky" wire:click="$dispatch('shop.product.pricing.history.assign-data', { colorId: '{{ $price->color_id ?? 'null' }}', warrantyId: '{{ $price->warranty_id ?? 'null' }}' })">{{ __('app.price_history') }}</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table>
    @endif
</div>
