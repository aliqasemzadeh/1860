<flux:modal name="panel.shop.shipping.rate.create.modal" class="md:w-[36rem]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.create_shipping_rate') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.create_shipping_rate_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>{{ __('general.shipping_method') }}</flux:label>
                        <flux:select variant="listbox" wire:model="shipping_method_id" placeholder="{{ __('general.select_shipping_method') }}">
                            @foreach($methods as $method)
                                <flux:select.option value="{{ $method->id }}">
                                    {{ $method->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="shipping_method_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('general.shipping_zone') }}</flux:label>
                        <flux:select variant="listbox" wire:model="shipping_zone_id" placeholder="{{ __('general.select_shipping_zone') }}">
                            @foreach($zones as $zone)
                                <flux:select.option value="{{ $zone->id }}">
                                    {{ $zone->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="shipping_zone_id" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>{{ __('general.rate_type') }}</flux:label>
                        <flux:select wire:model="rate_type">
                            <flux:select.option value="flat">{{ __('general.rate_type_flat') }}</flux:select.option>
                            <flux:select.option value="weight">{{ __('general.rate_type_weight') }}</flux:select.option>
                            <flux:select.option value="price">{{ __('general.rate_type_price') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="rate_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('general.amount') }}</flux:label>
                        <flux:input wire:model="amount" type="number" min="0" step="1000" />
                        <flux:error name="amount" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>{{ __('general.min_weight') }} ({{ __('general.gram') }})</flux:label>
                        <flux:input wire:model="min_weight" type="number" min="0" step="1" />
                        <flux:error name="min_weight" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('general.max_weight') }} ({{ __('general.gram') }})</flux:label>
                        <flux:input wire:model="max_weight" type="number" min="0" step="1" />
                        <flux:error name="max_weight" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>{{ __('general.min_price') }}</flux:label>
                        <flux:input wire:model="min_price" type="number" min="0" step="1000" />
                        <flux:error name="min_price" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('general.max_price') }}</flux:label>
                        <flux:input wire:model="max_price" type="number" min="0" step="1000" />
                        <flux:error name="max_price" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('general.estimated_days') }}</flux:label>
                    <flux:input wire:model="estimated_days" type="text" />
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('general.shipping_estimated_days_help') }}
                    </flux:text>
                    <flux:error name="estimated_days" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.status') }}</flux:label>
                    <flux:switch wire:model="is_active">
                        {{ __('general.is_active') }}
                    </flux:switch>
                    <flux:error name="is_active" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('general.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
