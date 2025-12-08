<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.categories') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.categories_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="shop.setting-management.category.create.modal">
                <flux:button variant="primary">{{ __('app.create_category') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:shop.setting-management.category.create />
    <livewire:shop.setting-management.category.edit />

    <flux:table :paginate="$this->categories">
        <flux:table.columns>
            <flux:table.column sortable>{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable>{{ __('app.slug') }}</flux:table.column>
            <flux:table.column sortable>{{ __('app.slug_fa') }}</flux:table.column>
            <flux:table.column sortable>{{ __('app.date') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->categories as $category)
            <flux:table.row :key="$category->id">
                <flux:table.cell class="flex items-center gap-3">
                    <flux:icon name="{{ $category->icon }}" /> {{ $category->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $category->slug }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $category->slug_fa }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('shop.setting-management.category.edit.assign-data', { id: '{{ $category->id }}' })">{{ __('app.edit') }}</flux:button>
                    <flux:button size="xs" variant="danger" wire:click="delete({{ $category->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>

            @foreach ($category->children as $child)
                <flux:table.row :key="'child-'.$child->id">
                    <flux:table.cell class="flex items-center gap-3 pl-8 ms-5">
                       <flux:icon.corner-down-left /> {{ $child->name }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $child->slug }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $child->slug_fa }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('shop.setting-management.category.edit.assign-data', { id: '{{ $child->id }}' })">{{ __('app.edit') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $child->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        @endforeach
    </flux:table>
</div>
