<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.categories') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.categories_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="administrator.shop.category.create.modal">
                <flux:button variant="primary">{{ __('app.create_category') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:administrator.shop.category.create />
    <livewire:administrator.shop.category.edit />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable>{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable>{{ __('app.slug') }}</flux:table.column>
            <flux:table.column sortable>{{ __('app.slug_fa') }}</flux:table.column>
            <flux:table.column sortable>{{ __('app.date') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->categories as $category)
            <flux:table.row :key="$category->id">
                <flux:table.cell class="flex items-center gap-3">
                    {{ $category->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $category->slug }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $category->slug_fa }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('administrator.shop.category.edit.assign-data', { id: '{{ $category->id }}' })">{{ __('app.edit') }}</flux:button>
                    <flux:button size="xs" variant="danger">{{ __('app.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
