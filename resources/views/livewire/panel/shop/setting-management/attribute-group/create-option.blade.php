<flux:modal name="shop.setting-management.attribute-group.attribute.option.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.add_option') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.create_attribute_option_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('app.option_value') }}</flux:label>
                    <flux:input wire:model="value" type="text" placeholder="red, xl, large" />
                    <flux:text class="mt-1 text-xs text-gray-500">{{ __('app.option_value_help') }}</flux:text>
                    <flux:error name="value" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.option_label') }}</flux:label>
                    <flux:input wire:model="label" type="text" placeholder="Red, XL, Large" />
                    <flux:text class="mt-1 text-xs text-gray-500">{{ __('app.option_label_help') }}</flux:text>
                    <flux:error name="label" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.sort_order') }}</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="1" />
                    <flux:error name="sort_order" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>

