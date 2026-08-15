<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.brands') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.brands_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="panel.shop.setting-management.brand.create.modal">
                <flux:button variant="primary">{{ __('general.create_brand') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.brand.create />
    <livewire:panel.shop.setting-management.brand.edit />

    <flux:table :paginate="$this->brands">
        <flux:table.columns>
            <flux:table.column sortable>{{ __('general.name') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.slug') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.slug_fa') }}</flux:table.column>
            <flux:table.column sortable>{{ __('general.date') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->brands as $brand)
            <flux:table.row :key="$brand->id">
                <flux:table.cell class="flex items-center gap-3">
                    {{ $brand->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $brand->slug }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $brand->slug_fa }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($brand->created_at) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.brand.edit.assign-data', { id: '{{ $brand->id }}' })">{{ __('general.edit') }}</flux:button>
                    <flux:button size="xs" variant="danger">{{ __('general.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
