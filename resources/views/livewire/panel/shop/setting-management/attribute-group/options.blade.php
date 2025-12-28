<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.attribute_options') }}: {{ $attribute->label }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ __('app.attribute') }}: {{ $attribute->attributeGroup->name ?? __('app.ungrouped_attributes') }}
                    <span class="mx-2">•</span>
                    {{ __('app.attribute_type_' . $attribute->type) }}
                </flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('panel.shop.setting-management.attribute-group.attributes', ['attributeGroupId' => $attribute->attribute_group_id]) }}" wire:navigate>
                    {{ __('app.back') }}
                </flux:button>
                <flux:button variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute-group.attribute.option.create.set-attribute', { id: {{ $attributeId }} })">
                    {{ __('app.add_option') }}
                </flux:button>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.attribute-group.create-option />
    <livewire:panel.shop.setting-management.attribute-group.edit-option />

    @if($attribute->type !== 'select' && $attribute->type !== 'multiselect')
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <flux:text class="text-yellow-800 dark:text-yellow-200">
                {{ __('app.attribute_options_only_for_select_types') }}
            </flux:text>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable wire:click="sort('label')">{{ __('app.option_label') }}</flux:table.column>
                <flux:table.column sortable wire:click="sort('value')">{{ __('app.option_value') }}</flux:table.column>
                <flux:table.column sortable wire:click="sort('sort_order')">{{ __('app.sort_order') }}</flux:table.column>
                <flux:table.column>{{ __('app.date') }}</flux:table.column>
                <flux:table.column>{{ __('app.actions') }}</flux:table.column>
            </flux:table.columns>

            @foreach ($this->options as $option)
                <flux:table.row :key="$option->id">
                    <flux:table.cell class="font-medium">
                        {{ $option->label }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $option->value }}</code>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $option->sort_order }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $option->created_at->format('Y-m-d') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute-group.attribute.option.edit.assign-data', { id: {{ $option->id }} })">{{ __('app.edit') }}</flux:button>
                            <flux:button size="xs" variant="danger" wire:click="delete({{ $option->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table>

        <div class="mt-4">
            {{ $this->options->links() }}
        </div>
    @endif
</div>

