<flux:modal name="panel.shop.setting-management.attribute.option.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.add_option') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.create_attribute_option_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('general.option_value') }}</flux:label>
                    <flux:input wire:model="value" type="text" placeholder="red, xl, large" />
                    <flux:description>{{ __('general.option_value_help') }}</flux:description>
                    <flux:error name="value" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.option_label') }}</flux:label>
                    <flux:input wire:model="label" type="text" placeholder="Red, XL, Large" />
                    <flux:description>{{ __('general.option_label_help') }}</flux:description>
                    <flux:error name="label" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.sort_order') }}</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('general.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>

