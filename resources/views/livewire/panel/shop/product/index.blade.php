<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.products') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.products_description') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                @if(count($selectedProductIds) > 0)
                    <flux:button
                        variant="primary"
                        color="orange"
                        icon="banknotes"
                        wire:click="openBulkPriceChange"
                    >
                        {{ __('general.bulk_price_change') }} ({{ number_format(count($selectedProductIds)) }})
                    </flux:button>
                @endif
                <flux:tooltip content="{{ count($selectedProductIds) > 0 ? __('general.export_selected_products') : __('general.export_all_products') }}">
                    <flux:button
                        variant="primary"
                        color="teal"
                        icon="arrow-down-tray"
                        wire:click="export"
                    >
                        {{ __('general.export_excel') }}
                    </flux:button>
                </flux:tooltip>
                <flux:modal.trigger name="panel.shop.product.create.modal">
                    <flux:button variant="primary">{{ __('general.create_product') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <div class="mb-6">
        <flux:field>
            <flux:label>{{ __('general.search') }}</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" clearable type="text" placeholder="{{ __('general.search_in_products') }}" />
        </flux:field>
    </div>

    <livewire:panel.shop.product.create />
    <livewire:panel.shop.product.edit />
    <livewire:panel.shop.product.colors />
    <livewire:panel.shop.product.warranties />
    <livewire:panel.shop.product.price-fetchers />
    <livewire:panel.shop.product.product-images />
    <livewire:panel.shop.product.product-wizard />
    <livewire:panel.shop.product.product-image-wizard />

    <livewire:panel.shop.setting-management.category.create />
    <livewire:panel.shop.setting-management.brand.create />
    <livewire:panel.shop.setting-management.unit.create />
    <livewire:panel.shop.setting-management.color.create />
    <livewire:panel.shop.setting-management.warranty.create />

    <livewire:panel.shop.product.pricing.bulk-change />

    <flux:table :paginate="$this->products">
        <flux:table.columns>
            <flux:table.column class="w-10">
                <flux:checkbox
                    wire:click="toggleSelectAllOnPage"
                    :checked="count($selectedProductIds) > 0 && count(array_intersect($selectedProductIds, $this->products->getCollection()->pluck('id')->all())) === $this->products->count()"
                />
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('general.name') }}</flux:table.column>
            <flux:table.column>{{ __('general.category') }}</flux:table.column>
            <flux:table.column>{{ __('general.brand') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.date') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->products as $product)
            <flux:table.row :key="$product->id">
                <flux:table.cell>
                    <flux:checkbox
                        wire:model.live="selectedProductIds"
                        value="{{ $product->id }}"
                    />
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex flex-row items-center gap-2">
                        <flux:avatar src="{{ Storage::url($product->file_path) }}"/>
                        {{ $product->name }}
                    </div>
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->category?->name ?? '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $product->brand?->name ?? '-' }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($product->created_at) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.product.edit.assign-data', { id: '{{ $product->id }}' })">{{ __('general.edit') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="sky" wire:click="$dispatch('panel.shop.product.colors.assign-data', { id: '{{ $product->id }}' })">{{ __('general.colors') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="green" wire:click="$dispatch('panel.shop.product.warranties.assign-data', { id: '{{ $product->id }}' })">{{ __('general.warranties') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="purple" wire:click="$dispatch('panel.shop.product.price-fetchers.assign-data', { id: '{{ $product->id }}' })">{{ __('general.price_fetchers') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="orange" wire:click="$dispatch('panel.shop.product.images.assign-data', { id: '{{ $product->id }}' })">{{ __('general.images') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="teal" href="{{ route('panel.shop.product.pricing.index', ['productId' => $product->id]) }}" wire:navigate>{{ __('general.pricing') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="indigo" href="{{ route('panel.shop.product.attributes.index', ['id' => $product->id]) }}" wire:navigate>{{ __('general.product_attributes') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $product->id }})" wire:confirm="{{ __('general.are_you_sure') }}">{{ __('general.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
