<x-slot name="title">
    {{ __('general.pricing') }} - {{ $product->name }}
</x-slot>
<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="{{ route('panel.shop.product.index') }}" wire:navigate>{{ __('general.products') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $product->name }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ __('general.pricing') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.pricing') }} - {{ $product->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.pricing_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="panel.shop.product.pricing.create.modal">
                <flux:button variant="primary">{{ __('general.create_price') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>
    <livewire:panel.shop.product.pricing.create :productId="$productId" />
    <livewire:panel.shop.product.pricing.edit />
    <livewire:panel.shop.product.pricing.history :productId="$productId" />

    @if ($this->latestPrices->isEmpty())
        <div class="text-center py-12">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('general.no_prices_found') }}</flux:text>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('general.color') }}</flux:table.column>
                <flux:table.column>{{ __('general.warranty') }}</flux:table.column>
                <flux:table.column>{{ __('general.price') }}</flux:table.column>
                <flux:table.column>{{ __('general.sale_price') }}</flux:table.column>
                <flux:table.column>{{ __('general.quantity') }}</flux:table.column>
                <flux:table.column>{{ __('general.is_default') }}</flux:table.column>
                <flux:table.column>{{ __('general.created_at') }}</flux:table.column>
                <flux:table.column>{{ __('general.options') }}</flux:table.column>
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
                            <span class="text-zinc-400 dark:text-zinc-500">{{ __('general.none') }}</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $price->warranty?->name ?? __('general.none') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ number_format($price->price, 0) }} {{ __('general.toman') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @if ($price->sale_price)
                            {{ number_format($price->sale_price, 0) }} {{ __('general.toman') }}
                        @else
                            <span class="text-zinc-400 dark:text-zinc-500">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ number_format($price->quantity, 0) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @if ($price->is_default)
                            <flux:badge variant="success">{{ __('general.yes') }}</flux:badge>
                        @else
                            <flux:badge variant="ghost">{{ __('general.no') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ jalali($price->created_at) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.product.pricing.edit.assign-data', { id: '{{ $price->id }}' })">{{ __('general.edit') }}</flux:button>
                            <flux:button size="xs" variant="primary" color="sky" wire:click="$dispatch('panel.shop.product.pricing.history.assign-data', { colorId: {{ $price->color_id ?? 'null' }}, warrantyId: {{ $price->warranty_id ?? 'null' }} })">{{ __('general.price_history') }}</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table>
    @endif
</div>
