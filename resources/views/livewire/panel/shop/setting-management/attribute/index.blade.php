<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.attributes') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.attributes_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="shop.setting-management.attribute.create.modal">
                <flux:button variant="primary">{{ __('app.create_attribute') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.attribute.create />
    <livewire:panel.shop.setting-management.attribute.edit />

    <flux:table :paginate="$this->attributesList">
        <flux:table.columns>
            <flux:table.column sortable wire:click="sort('label')">{{ __('app.attribute_label') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('key')">{{ __('app.attribute_key') }}</flux:table.column>
            <flux:table.column>{{ __('app.attribute_group') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('type')">{{ __('app.attribute_type') }}</flux:table.column>
            <flux:table.column>{{ __('app.options_count') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('sort_order')">{{ __('app.sort_order') }}</flux:table.column>
            <flux:table.column>{{ __('app.actions') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->attributesList as $attribute)
            <flux:table.row :key="$attribute->id">
                <flux:table.cell class="flex items-center gap-3">
                    {{ $attribute->label }}
                    @if($attribute->is_required)
                        <span class="text-red-500">*</span>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <code class="text-xs">{{ $attribute->key }}</code>
                </flux:table.cell>
                <flux:table.cell>
                    {{ $attribute->attributeGroup?->name ?? __('app.ungrouped_attributes') }}
                </flux:table.cell>
                <flux:table.cell>
                    @if($attribute->type === 'text')
                        {{ __('app.attribute_type_text') }}
                    @elseif($attribute->type === 'textarea')
                        {{ __('app.attribute_type_textarea') }}
                    @elseif($attribute->type === 'number')
                        {{ __('app.attribute_type_number') }}
                    @elseif($attribute->type === 'boolean')
                        {{ __('app.attribute_type_boolean') }}
                    @elseif($attribute->type === 'date')
                        {{ __('app.attribute_type_date') }}
                    @elseif($attribute->type === 'select')
                        {{ __('app.attribute_type_select') }}
                    @elseif($attribute->type === 'multiselect')
                        {{ __('app.attribute_type_multiselect') }}
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    @if(in_array($attribute->type, ['select', 'multiselect']))
                        <a href="{{ route('panel.shop.setting-management.attribute.options.index', $attribute->id) }}" class="text-blue-500 hover:underline" wire:navigate>
                            {{ $attribute->options_count }}
                        </a>
                    @else
                        -
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    {{ $attribute->sort_order }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute.edit.assign-data', { id: '{{ $attribute->id }}' })">{{ __('app.edit') }}</flux:button>
                    @if(in_array($attribute->type, ['select', 'multiselect']))
                        <flux:button size="xs" variant="ghost" href="{{ route('panel.shop.setting-management.attribute.options.index', $attribute->id) }}" wire:navigate>{{ __('app.attribute_options') }}</flux:button>
                    @endif
                    <flux:button size="xs" variant="danger" wire:click="delete({{ $attribute->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
