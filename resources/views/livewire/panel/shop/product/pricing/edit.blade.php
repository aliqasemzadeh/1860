<flux:modal name="panel.shop.product.pricing.edit.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.edit_price') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.edit_price_description') }}</flux:text>
        </div>

        @if ($productPrice)
            <form wire:submit="update" method="post">
                <div class="space-y-6">
                    <flux:field>
                        <flux:label>{{ __('app.color') }}</flux:label>
                        @if ($this->colors->isEmpty())
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">{{ __('app.no_colors_added_to_product') }}</flux:text>
                            <flux:input wire:model="color_id" type="hidden" />
                        @else
                            <flux:select wire:model="color_id" variant="combobox" :filter="false" placeholder="{{ __('app.select_color_optional') }}">
                                <x-slot name="input">
                                    <flux:select.input wire:model.live="color_search" placeholder="{{ __('general.search') }}..." />
                                </x-slot>
                                <flux:select.option value="">{{ __('app.none') }}</flux:select.option>
                                @foreach ($this->colors as $color)
                                    <flux:select.option value="{{ $color->id }}" wire:key="color-{{ $color->id }}">
                                        <div class="flex items-center gap-2">
                                            @if($color->hex)
                                                <div class="w-4 h-4 rounded border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $color->hex }}"></div>
                                            @endif
                                            <span>{{ $color->name }}</span>
                                        </div>
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                        <flux:error name="color_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.warranty') }}</flux:label>
                        @if ($this->warranties->isEmpty())
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">{{ __('app.no_warranties_added_to_product') }}</flux:text>
                            <flux:input wire:model="warranty_id" type="hidden" />
                        @else
                            <flux:select wire:model="warranty_id" variant="combobox" :filter="false" placeholder="{{ __('app.select_warranty_optional') }}">
                                <x-slot name="input">
                                    <flux:select.input wire:model.live="warranty_search" placeholder="{{ __('general.search') }}..." />
                                </x-slot>
                                <flux:select.option value="">{{ __('app.none') }}</flux:select.option>
                                @foreach ($this->warranties as $warranty)
                                    <flux:select.option value="{{ $warranty->id }}" wire:key="warranty-{{ $warranty->id }}">
                                        {{ $warranty->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                        <flux:error name="warranty_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.price') }}</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>{{ __('general.toman') }}</flux:input.group.prefix>
                            <flux:input wire:model="price" type="text" mask:dynamic="$money($input)" />
                        </flux:input.group>
                        <flux:error name="price" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.sale_price') }}</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>{{ __('general.toman') }}</flux:input.group.prefix>
                            <flux:input wire:model="sale_price" type="text" mask:dynamic="$money($input)" />
                        </flux:input.group>
                        <flux:error name="sale_price" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('general.quantity') }}</flux:label>
                        <flux:input wire:model="quantity" type="number" step="0.01" min="0" placeholder="0" />
                        <flux:error name="quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:checkbox wire:model="is_default" label="{{ __('app.set_as_default_price') }}" />
                        <flux:error name="is_default" />
                    </flux:field>
                </div>

                <flux:button type="submit" class="w-full mt-6" variant="primary">
                    {{ __('app.update') }}
                </flux:button>
            </form>
        @endif
    </div>
</flux:modal>
