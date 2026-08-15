<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.warranties') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.warranties_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="panel.shop.setting-management.warranty.create.modal">
                <flux:button variant="primary">{{ __('general.create_warranty') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.warranty.create />
    <livewire:panel.shop.setting-management.warranty.edit />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable wire:click="sort('name')">{{ __('general.name') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('slug')">{{ __('general.slug') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('slug_fa')">{{ __('general.slug_fa') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->warranties as $warranty)
            <flux:table.row :key="$warranty->id">
                <flux:table.cell class="whitespace-nowrap">
                    {{ $warranty->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $warranty->slug }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $warranty->slug_fa }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.warranty.edit.assign-data', { id: '{{ $warranty->id }}' })">{{ __('general.edit') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $warranty->id }})">{{ __('general.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
