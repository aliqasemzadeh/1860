<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">
                    {{ __('app.attribute_options') }} - {{ $attribute->label }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ __('app.create_attribute_option_description') }}
                </flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:modal.trigger name="panel.shop.setting-management.attribute.option.create.modal">
                    <flux:button variant="primary">{{ __('app.add_option') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.setting-management.attribute.option.create :id="$attributeId" />
    <livewire:panel.shop.setting-management.attribute.option.edit />

    <flux:table :paginate="$this->optionsList">
        <flux:table.columns>
            <flux:table.column sortable wire:click="sort('value')">{{ __('app.option_value') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('label')">{{ __('app.option_label') }}</flux:table.column>
            <flux:table.column sortable wire:click="sort('sort_order')">{{ __('app.sort_order') }}</flux:table.column>
            <flux:table.column>{{ __('app.actions') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->optionsList as $option)
            <flux:table.row :key="$option->id">
                <flux:table.cell>
                    <code class="text-xs">{{ $option->value }}</code>
                </flux:table.cell>
                <flux:table.cell>
                    {{ $option->label }}
                </flux:table.cell>
                <flux:table.cell>
                    {{ $option->sort_order }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.shop.setting-management.attribute.option.edit.assign-data', { id: '{{ $option->id }}' })">{{ __('app.edit') }}</flux:button>
                    <flux:button size="xs" variant="danger" wire:click="delete({{ $option->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
