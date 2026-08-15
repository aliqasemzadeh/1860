<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.colors') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.colors_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="panel.shop.setting-management.color.create.modal">
                <flux:button variant="primary">{{ __('general.create_color') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.color.create />
    <livewire:panel.shop.setting-management.color.edit />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable>{{ __('general.name') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.slug') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.slug_fa') }}</flux:table.column>
            <flux:table.column>{{ __('general.hex') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->colors as $color)
            <flux:table.row :key="$color->id">
                <flux:table.cell class="flex items-center gap-3">
                    <div class="size-4 rounded-full border border-zinc-200 dark:border-zinc-600" style="background-color: {{ $color->hex }}"></div>
                    {{ $color->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $color->slug }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $color->slug_fa }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs">{{ $color->hex }}</span>
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.color.edit.assign-data', { id: '{{ $color->id }}' })">{{ __('general.edit') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $color->id }})">{{ __('general.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
