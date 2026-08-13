<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.my_addresses') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.my_addresses_description') }}</flux:subheading>
            </div>
            @can('user_address_create')
                <flux:modal.trigger name="panel.user.address.create.modal">
                    <flux:button variant="primary" color="teal" icon="plus">{{ __('app.create_address') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>

    <flux:card class="mb-6">
        <flux:field>
            <flux:label>{{ __('general.search') }}</flux:label>
            <flux:input wire:model.live.debounce.500ms="search" type="text" placeholder="{{ __('general.search') }}" />
        </flux:field>
    </flux:card>

    <livewire:panel.user.address.create />
    <livewire:panel.user.address.edit />

    <flux:table :paginate="$this->addresses">
        <flux:table.columns>
            <flux:table.column>{{ __('app.address_name') }}</flux:table.column>
            <flux:table.column>{{ __('app.province') }}</flux:table.column>
            <flux:table.column>{{ __('app.city') }}</flux:table.column>
            <flux:table.column>{{ __('app.address') }}</flux:table.column>
            <flux:table.column>{{ __('app.postal_code') }}</flux:table.column>
            <flux:table.column>{{ __('app.default_address') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @forelse ($this->addresses as $address)
            <flux:table.row :key="$address->id">
                <flux:table.cell class="whitespace-nowrap">
                    {{ $address->name ?: '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $address->province_name ?: '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $address->city_name ?: '-' }}
                </flux:table.cell>
                <flux:table.cell>
                    {{ \Illuminate\Support\Str::limit($address->address, 40) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $address->postal_code ?: '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    @if ($address->is_default)
                        <flux:badge color="green">{{ __('app.default') }}</flux:badge>
                    @else
                        -
                    @endif
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        @can('user_address_edit')
                            <flux:tooltip content="{{ __('general.edit') }}">
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    color="teal"
                                    icon="pencil"
                                    icon:variant="outline"
                                    wire:click="$dispatch('panel.user.address.edit.assign-data', { id: {{ $address->id }} })"
                                />
                            </flux:tooltip>

                            @unless ($address->is_default)
                                <flux:tooltip content="{{ __('app.set_as_default') }}">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        color="amber"
                                        icon="star"
                                        icon:variant="outline"
                                        wire:click="setDefault({{ $address->id }})"
                                    />
                                </flux:tooltip>
                            @endunless
                        @endcan

                        @can('user_address_delete')
                            <flux:tooltip content="{{ __('general.delete') }}">
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    color="red"
                                    icon="trash"
                                    icon:variant="outline"
                                    wire:click="delete({{ $address->id }})"
                                    wire:confirm="{{ __('general.are_you_sure') }}"
                                />
                            </flux:tooltip>
                        @endcan
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="7">
                    {{ __('app.no_addresses_found') }}
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table>
</div>
