<flux:modal name="shop.setting-management.attribute-group.attribute.create.modal" class="md:w-[600px]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.create_attribute') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.create_attribute_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('app.attribute_key') }}</flux:label>
                    <flux:input wire:model="key" type="text" placeholder="color, size, weight" />
                    <flux:text class="mt-1 text-xs text-gray-500">{{ __('app.attribute_key_help') }}</flux:text>
                    <flux:error name="key" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.attribute_label') }}</flux:label>
                    <flux:input wire:model="label" type="text" placeholder="Color, Size, Weight" />
                    <flux:error name="label" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.attribute_type') }}</flux:label>
                    <flux:select variant="listbox" wire:model.live="type">
                        <flux:select.option value="text">{{ __('app.attribute_type_text') }}</flux:select.option>
                        <flux:select.option value="textarea">{{ __('app.attribute_type_textarea') }}</flux:select.option>
                        <flux:select.option value="number">{{ __('app.attribute_type_number') }}</flux:select.option>
                        <flux:select.option value="boolean">{{ __('app.attribute_type_boolean') }}</flux:select.option>
                        <flux:select.option value="date">{{ __('app.attribute_type_date') }}</flux:select.option>
                        <flux:select.option value="select">{{ __('app.attribute_type_select') }}</flux:select.option>
                        <flux:select.option value="multiselect">{{ __('app.attribute_type_multiselect') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="type" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_required">{{ __('app.required') }}</flux:checkbox>
                    <flux:error name="is_required" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.sort_order') }}</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="1" />
                    <flux:error name="sort_order" />
                </flux:field>

                @if(in_array($type, ['select', 'multiselect']))
                    <flux:field>
                        <div class="flex items-center justify-between mb-2">
                            <flux:label>{{ __('app.attribute_options') }}</flux:label>
                            <flux:button type="button" size="xs" variant="primary" wire:click="addOption">
                                {{ __('app.add_option') }}
                            </flux:button>
                        </div>
                        <flux:text class="mb-2 text-xs text-gray-500">{{ __('app.attribute_options_required_for_select') }}</flux:text>
                        
                        <div class="space-y-3">
                            @foreach($options as $index => $option)
                                <div class="flex items-center gap-2 p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex-1 grid grid-cols-2 gap-2">
                                        <flux:field>
                                            <flux:input wire:model="options.{{ $index }}.value" type="text" placeholder="{{ __('app.option_value') }}" />
                                            <flux:error name="options.{{ $index }}.value" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:input wire:model="options.{{ $index }}.label" type="text" placeholder="{{ __('app.option_label') }}" />
                                            <flux:error name="options.{{ $index }}.label" />
                                        </flux:field>
                                    </div>
                                    <flux:button type="button" size="xs" variant="danger" wire:click="removeOption({{ $index }})">
                                        {{ __('app.delete') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                        <flux:error name="options" />
                    </flux:field>
                @endif
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>

