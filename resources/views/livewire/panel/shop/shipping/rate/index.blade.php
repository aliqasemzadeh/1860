<x-slot name="title">
    {{ __('general.shipping_rates') }}
</x-slot>
<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.shipping_rates') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.shipping_rates_description') }}</flux:subheading>
            </div>

            <flux:modal.trigger name="panel.shop.shipping.rate.create.modal">
                <flux:button variant="primary">{{ __('general.create_shipping_rate') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.shipping.rate.create />
    <livewire:panel.shop.shipping.rate.edit />

    <flux:table :paginate="$this->rates">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="9" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-1 pe-2 items-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('general.search_placeholder') }}"
                        wire:model.live="search"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>

        <flux:table.columns>
            <flux:table.column>{{ __('general.shipping_method') }}</flux:table.column>
            <flux:table.column>{{ __('general.shipping_zone') }}</flux:table.column>
            <flux:table.column>{{ __('general.rate_type') }}</flux:table.column>
            <flux:table.column>{{ __('general.min') }}</flux:table.column>
            <flux:table.column>{{ __('general.max') }}</flux:table.column>
            <flux:table.column>{{ __('general.amount') }}</flux:table.column>
            <flux:table.column>{{ __('general.status') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.date') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->rates as $rate)
                <flux:table.row :key="$rate->id">
                    <flux:table.cell>
                        {{ optional($rate->method)->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ optional($rate->zone)->name }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-xs">
                        {{ __('general.rate_type_'.$rate->rate_type) }}
                    </flux:table.cell>

                    @if($rate->rate_type === 'weight')
                    <flux:table.cell class="whitespace-nowrap text-xs">
                        {{ $rate->min_weight }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap text-xs">
                        {{ $rate->max_weight }}
                    </flux:table.cell>
                    @elseif($rate->rate_type === 'price')
                        <flux:table.cell class="whitespace-nowrap text-xs">
                            {{ $rate->min_price }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap text-xs">
                            {{ $rate->max_price }}
                        </flux:table.cell>
                    @else
                        <flux:table.cell class="whitespace-nowrap text-xs">
                            -
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap text-xs">
                            -
                        </flux:table.cell>
                    @endif

                    <flux:table.cell class="whitespace-nowrap">
                        {{ number_format($rate->amount, 0) }} {{ __('general.toman') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:badge :color="$rate->is_active ? 'green' : 'zinc'">
                            {{ $rate->is_active ? __('general.yes') : __('general.no') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ jalali($rate->created_at) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.shipping.rate.edit.assign-data', { id: '{{ $rate->id }}' })">
                            {{ __('general.edit') }}
                        </flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $rate->id }})">
                            {{ __('general.delete') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
