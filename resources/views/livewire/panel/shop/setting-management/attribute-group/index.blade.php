<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.attribute_groups') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.attribute_groups_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="shop.setting-management.attribute-group.create.modal">
                <flux:button variant="primary">{{ __('app.create_attribute_group') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.attribute-group.create />
    <livewire:panel.shop.setting-management.attribute-group.edit />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('sort_order')">{{ __('app.sort_order') }}</flux:table.column>
            <flux:table.column>{{ __('app.attributes_count') }}</flux:table.column>
            <flux:table.column>{{ __('app.date') }}</flux:table.column>
            <flux:table.column>{{ __('app.actions') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->attributeGroups as $group)
            <flux:table.row :key="$group->id">
                <flux:table.cell class="font-medium">
                    {{ $group->name }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $group->sort_order }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $group->attributes_count }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $group->created_at->format('Y-m-d') }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute-group.edit.assign-data', { id: {{ $group->id }} })">{{ __('app.edit') }}</flux:button>
                        <flux:button size="xs" variant="primary" color="purple" href="{{ route('panel.shop.setting-management.attribute-group.attributes', ['attributeGroupId' => $group->id]) }}" wire:navigate>{{ __('app.attributes') }}</flux:button>
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $group->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>

    <div class="mt-4">
        {{ $this->attributeGroups->links() }}
    </div>
</div>
