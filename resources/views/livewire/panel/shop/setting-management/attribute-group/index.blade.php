<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.attribute_groups') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.attribute_groups_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="panel.shop.setting-management.attribute-group.create.modal">
                <flux:button variant="primary">{{ __('general.create_attribute_group') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.attribute-group.create />
    <livewire:panel.shop.setting-management.attribute-group.edit />

    <flux:table :paginate="$this->attributeGroups">
        <flux:table.columns>
            <flux:table.column sortable wire:click="sort('name')">{{ __('general.name') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('attributes_count')">{{ __('general.attributes_count') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('sort_order')">{{ __('general.sort_order') }}</flux:table.column>
            <flux:table.column>{{ __('general.actions') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->attributeGroups as $group)
            <flux:table.row :key="$group->id">
                <flux:table.cell class="flex items-center gap-3">
                    {{ $group->name }}
                </flux:table.cell>
                <flux:table.cell>
                    {{ $group->attributes_count }}
                </flux:table.cell>
                <flux:table.cell>
                    {{ $group->sort_order }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute-group.edit.assign-data', { id: '{{ $group->id }}' })">{{ __('general.edit') }}</flux:button>
                    <flux:button size="xs" variant="danger" wire:click="delete({{ $group->id }})" wire:confirm="{{ __('general.are_you_sure') }}">{{ __('general.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
