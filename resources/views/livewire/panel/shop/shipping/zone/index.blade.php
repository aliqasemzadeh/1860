<x-slot name="title">
    {{ __('app.shipping_zones') }}
</x-slot>
<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.shipping_zones') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.shipping_zones_description') }}</flux:subheading>
            </div>

            <flux:modal.trigger name="panel.shop.shipping.zone.create.modal">
                <flux:button variant="primary">{{ __('app.create_shipping_zone') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.shipping.zone.create />
    <livewire:panel.shop.shipping.zone.edit />

    <flux:table :paginate="$this->zones">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="6" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-1 pe-2 items-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.live="search"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>

        <flux:table.columns>
            <flux:table.column sortable>{{ __('app.name') }}</flux:table.column>
            <flux:table.column>{{ __('app.countries') }}</flux:table.column>
            <flux:table.column>{{ __('app.states') }}</flux:table.column>
            <flux:table.column>{{ __('app.cities') }}</flux:table.column>
            <flux:table.column>{{ __('app.areas') }}</flux:table.column>
            <flux:table.column sortable>{{ __('app.date') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->zones as $zone)
                <flux:table.row :key="$zone->id">
                    <flux:table.cell>
                        {{ $zone->name }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-xs">
                        {{ implode(', ', (array) $zone->countries) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-xs">
                        @php
                            $stateNames = $this->getStateNames($zone);
                        @endphp

                        @if (count($stateNames) > 3)
                            <flux:heading class="flex items-center gap-2">
                                {{ __('app.x_provinces', ['count' => count($stateNames)]) }}
                                <flux:tooltip toggleable>
                                    <flux:button icon="information-circle" size="sm" variant="ghost" />
                                    <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                        {{ implode(', ', $stateNames) }}
                                    </flux:tooltip.content>
                                </flux:tooltip>
                            </flux:heading>
                        @else
                            {{ empty($stateNames) ? __('app.all') : implode(', ', $stateNames) }}
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-xs">
                        {{ $this->formatCities($zone) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-xs">
                        {{ implode(', ', (array) $zone->areas) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.shipping.zone.edit.assign-data', { id: '{{ $zone->id }}' })">
                            {{ __('app.edit') }}
                        </flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $zone->id }})">
                            {{ __('app.delete') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
