<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.units') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.units_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="panel.shop.setting-management.unit.create.modal">
                <flux:button variant="primary">{{ __('app.create_unit') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.unit.create />
    <livewire:panel.shop.setting-management.unit.edit />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.date') }}</flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->units as $unit)
            <flux:table.row :key="$unit->id">
                <flux:table.cell class="whitespace-nowrap">
                    {{ $unit->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($unit->created_at) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.unit.edit.assign-data', { id: '{{ $unit->id }}' })">{{ __('app.edit') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $unit->id }})">{{ __('app.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>

    <div class="mt-4">
        {{ $this->units->links() }}
    </div>
</div>
