<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.attributes') }}: {{ $attributeGroup->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.attributes_description') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('panel.shop.setting-management.attribute-group.index') }}" wire:navigate>
                    {{ __('app.back') }}
                </flux:button>
                <flux:button variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute-group.attribute.create.set-group', { groupId: {{ $attributeGroupId }} })">
                    {{ __('app.create_attribute') }}
                </flux:button>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.attribute-group.create-attribute />
    <livewire:panel.shop.setting-management.attribute-group.edit-attribute />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable wire:click="sort('label')">{{ __('app.attribute_label') }}</flux:table.column>
            <flux:table.column>{{ __('app.attribute_key') }}</flux:table.column>
            <flux:table.column>{{ __('app.attribute_type') }}</flux:table.column>
            <flux:table.column>{{ __('app.required') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('sort_order')">{{ __('app.sort_order') }}</flux:table.column>
            <flux:table.column>{{ __('app.options_count') }}</flux:table.column>
            <flux:table.column>{{ __('app.actions') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->attributes as $attribute)
            <flux:table.row :key="$attribute->id">
                <flux:table.cell class="font-medium">
                    {{ $attribute->label }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $attribute->key }}</code>
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ __('app.attribute_type_' . $attribute->type) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    @if($attribute->is_required)
                        <span class="text-red-500">{{ __('app.yes') }}</span>
                    @else
                        <span class="text-zinc-400">{{ __('app.no') }}</span>
                    @endif
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $attribute->sort_order }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $attribute->options_count }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute-group.attribute.edit.assign-data', { id: {{ $attribute->id }} })">{{ __('app.edit') }}</flux:button>
                        @if(in_array($attribute->type, ['select', 'multiselect']))
                            <flux:button size="xs" variant="primary" color="purple" href="{{ route('panel.shop.setting-management.attribute-group.attribute.options', ['attributeId' => $attribute->id]) }}" wire:navigate>{{ __('app.attribute_options') }}</flux:button>
                        @endif
                        <flux:button size="xs" variant="danger" wire:click="delete({{ $attribute->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>

    <div class="mt-4">
        {{ $this->attributes->links() }}
    </div>
</div>
