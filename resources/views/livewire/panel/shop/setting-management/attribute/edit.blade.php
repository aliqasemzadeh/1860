<flux:modal name="panel.shop.setting-management.attribute.edit.modal" class="md:w-[32rem]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.edit_attribute') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.edit_attribute_description') }}</flux:text>
        </div>

        <form wire:submit="edit" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('general.attribute_group') }}</flux:label>
                    <flux:select wire:model="attribute_group_id">
                        <option value="">{{ __('general.ungrouped_attributes') }}</option>
                        @foreach($attributeGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="attribute_group_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.attribute_key') }}</flux:label>
                    <flux:input wire:model="key" type="text" />
                    <flux:description>{{ __('general.attribute_key_help') }}</flux:description>
                    <flux:error name="key" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.attribute_label') }}</flux:label>
                    <flux:input wire:model="label" type="text" />
                    <flux:error name="label" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.attribute_type') }}</flux:label>
                    <flux:select wire:model="type">
                        <option value="text">{{ __('general.attribute_type_text') }}</option>
                        <option value="textarea">{{ __('general.attribute_type_textarea') }}</option>
                        <option value="number">{{ __('general.attribute_type_number') }}</option>
                        <option value="boolean">{{ __('general.attribute_type_boolean') }}</option>
                        <option value="date">{{ __('general.attribute_type_date') }}</option>
                        <option value="select">{{ __('general.attribute_type_select') }}</option>
                        <option value="multiselect">{{ __('general.attribute_type_multiselect') }}</option>
                    </flux:select>
                    <flux:error name="type" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:label>{{ __('general.required') }}</flux:label>
                    <flux:switch wire:model="is_required" />
                    <flux:error name="is_required" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.sort_order') }}</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('general.update') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
