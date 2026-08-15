<x-slot name="title">
    {{ __('general.shipping_methods') }}
</x-slot>
<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.shipping_methods') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.shipping_methods_description') }}</flux:subheading>
            </div>

            <flux:modal.trigger name="panel.shop.shipping.method.create.modal">
                <flux:button variant="primary">{{ __('general.create_shipping_method') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.shipping.method.create />
    <livewire:panel.shop.shipping.method.edit />

    <flux:table :paginate="$this->methods">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="5" class="bg-white dark:bg-zinc-900">
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
            <flux:table.column sortable>{{ __('general.name') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.slug') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.status') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.date') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->methods as $method)
                <flux:table.row :key="$method->id">
                    <flux:table.cell>
                        {{ $method->name }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $method->handle }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:badge :color="$method->is_active ? 'green' : 'zinc'">
                            {{ $method->is_active ? __('general.yes') : __('general.no') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ jalali($method->created_at) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.shipping.method.edit.assign-data', { id: '{{ $method->id }}' })">
                            {{ __('general.edit') }}
                        </flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $method->id }})">
                            {{ __('general.delete') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
