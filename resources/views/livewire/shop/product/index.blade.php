<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.products') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.products_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="shop.product.create.modal">
                <flux:button variant="primary">{{ __('app.create_product') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:shop.product.create />
    <livewire:shop.product.edit />

    <livewire:shop.setting-management.category.create />
    <livewire:shop.setting-management.brand.create />
    <livewire:shop.setting-management.unit.create />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'slug'" :direction="$sortDirection" wire:click="sort('slug')">{{ __('app.slug') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'slug_fa'" :direction="$sortDirection" wire:click="sort('slug_fa')">{{ __('app.slug_fa') }}</flux:table.column>
            <flux:table.column>{{ __('app.category') }}</flux:table.column>
            <flux:table.column>{{ __('app.brand') }}</flux:table.column>
            <flux:table.column>{{ __('app.unit') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.date') }}</flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->products as $product)
            <flux:table.row :key="$product->id">
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->slug }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->slug_fa }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->category?->name ?? '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->brand?->name ?? '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->unit?->name ?? '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->created_at?->format('Y-m-d H:i') }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('shop.product.edit.assign-data', { id: '{{ $product->id }}' })">{{ __('app.edit') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $product->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>

    <div class="mt-4">
        {{ $this->products->links() }}
    </div>
</div>
