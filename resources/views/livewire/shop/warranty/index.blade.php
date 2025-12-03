<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.warranties') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.warranties_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="shop.warranty.create.modal">
                <flux:button variant="primary">{{ __('app.create_warranty') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:shop.warranty.create />
    <livewire:shop.warranty.edit />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('slug')">{{ __('app.slug') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('slug_fa')">{{ __('app.slug_fa') }}</flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
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
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('shop.warranty.edit.assign-data', { id: '{{ $warranty->id }}' })">{{ __('app.edit') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $warranty->id }})">{{ __('app.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
