<flux:modal name="panel.shop.product.pricing.bulk-change.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.bulk_price_change') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.bulk_price_change_description') }}</flux:text>
            @if (count($productIds) > 0)
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('general.selected_products_count', ['count' => number_format(count($productIds))]) }}
                </flux:text>
            @endif
        </div>

        <form wire:submit="apply" method="post">
            <div class="space-y-6">
                <flux:field>
                    <flux:label>{{ __('general.adjustment_type') }}</flux:label>
                    <flux:select wire:model.live="adjustmentType">
                        <option value="percentage_increase">{{ __('general.adjustment_percentage_increase') }}</option>
                        <option value="percentage_decrease">{{ __('general.adjustment_percentage_decrease') }}</option>
                        <option value="fixed_increase">{{ __('general.adjustment_fixed_increase') }}</option>
                        <option value="fixed_decrease">{{ __('general.adjustment_fixed_decrease') }}</option>
                        <option value="set_price">{{ __('general.adjustment_set_price') }}</option>
                    </flux:select>
                    <flux:error name="adjustmentType" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.price_target') }}</flux:label>
                    <flux:select wire:model="priceTarget">
                        <option value="price">{{ __('general.price_target_price') }}</option>
                        <option value="sale_price">{{ __('general.price_target_sale_price') }}</option>
                        <option value="both">{{ __('general.price_target_both') }}</option>
                    </flux:select>
                    <flux:error name="priceTarget" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.adjustment_value') }}</flux:label>
                    @if ($adjustmentType === 'percentage_increase' || $adjustmentType === 'percentage_decrease')
                        <flux:input.group>
                            <flux:input wire:model="value" type="number" min="0" step="0.01" />
                            <flux:input.group.suffix>%</flux:input.group.suffix>
                        </flux:input.group>
                    @else
                        <flux:input.group>
                            <flux:input.group.prefix>{{ __('general.toman') }}</flux:input.group.prefix>
                            <flux:input wire:model="value" type="text" mask:dynamic="$money($input)" />
                        </flux:input.group>
                    @endif
                    <flux:error name="value" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full mt-6" variant="primary" color="orange">
                {{ __('general.apply') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
