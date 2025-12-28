<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.products') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.products_description') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="shop.product.product-wizard.modal">
                    <flux:button variant="primary" color="purple">
                        {{ __('app.product_wizard') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="shop.product.create.modal">
                    <flux:button variant="primary">{{ __('app.create_product') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:shop.product.create />
    <livewire:shop.product.edit />
    <livewire:shop.product.colors />
    <livewire:shop.product.warranties />
    <livewire:shop.product.price-fetchers />
    <livewire:shop.product.product-images />
    <livewire:shop.product.product-wizard />

    <livewire:shop.setting-management.category.create />
    <livewire:shop.setting-management.brand.create />
    <livewire:shop.setting-management.unit.create />
    <livewire:shop.setting-management.color.create />
    <livewire:shop.setting-management.warranty.create />

    <flux:table :paginate="$this->products">
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
                    {{ $product->created_at ? \Morilog\Jalali\Jalalian::fromCarbon($product->created_at)->format('%Y-%m-%d %H:%M') : '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('shop.product.edit.assign-data', { id: '{{ $product->id }}' })">{{ __('app.edit') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="sky" wire:click="$dispatch('shop.product.colors.assign-data', { id: '{{ $product->id }}' })">{{ __('app.colors') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="green" wire:click="$dispatch('shop.product.warranties.assign-data', { id: '{{ $product->id }}' })">{{ __('app.warranties') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="purple" wire:click="$dispatch('shop.product.price-fetchers.assign-data', { id: '{{ $product->id }}' })">{{ __('app.price_fetchers') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="orange" wire:click="$dispatch('shop.product.images.assign-data', { id: '{{ $product->id }}' })">{{ __('app.images') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="teal" href="{{ route('shop.product.pricing.index', ['productId' => $product->id]) }}">{{ __('app.pricing') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $product->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
