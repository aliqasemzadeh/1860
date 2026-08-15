<flux:modal name="panel.shop.product.colors.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.product_colors') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.product_colors_description') }}</flux:text>
        </div>

        @if ($product)
            <div class="space-y-4">
                <!-- Add Color Section -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:field>
                        <flux:label>{{ __('general.add_color') }}</flux:label>
                        <flux:select wire:model="selectedColorId" variant="combobox" :filter="false" placeholder="{{ __('general.select_color') }}">
                            <x-slot name="input">
                                <flux:select.input wire:model.live="colorSearch" placeholder="{{ __('general.search') }}..." />
                            </x-slot>
                            @foreach ($this->availableColors as $color)
                                <flux:select.option value="{{ $color->id }}" wire:key="color-{{ $color->id }}">
                                    <div class="flex items-center gap-2">
                                        @if ($color->hex)
                                            <div class="h-4 w-4 rounded border border-gray-300 dark:border-gray-600" style="background-color: {{ $color->hex }}"></div>
                                        @endif
                                        <span>{{ $color->name }}</span>
                                    </div>
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <div class="mt-3 flex items-center gap-2">
                            <flux:button wire:click="addColor" variant="primary" color="zinc" :disabled="!$selectedColorId">
                                {{ __('general.add') }}
                            </flux:button>
                            <flux:modal.trigger name="panel.shop.setting-management.color.create.modal">
                                <flux:button variant="ghost" color="zinc" icon="plus" />
                            </flux:modal.trigger>
                        </div>
                    </flux:field>
                </div>

                <!-- Colors List -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:heading size="sm" class="mb-4">{{ __('general.product_colors_list') }}</flux:heading>

                    @if ($product->colors->count() > 0)
                        <div class="space-y-2">
                            @foreach ($product->colors as $color)
                                <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                    <div class="flex items-center gap-3">
                                        @if ($color->hex)
                                            <div class="h-6 w-6 rounded border border-gray-300 dark:border-gray-600" style="background-color: {{ $color->hex }}"></div>
                                        @endif
                                        <span class="font-medium">{{ $color->name }}</span>
                                    </div>
                                    <flux:button
                                        size="xs"
                                        variant="danger"
                                        wire:click="removeColor({{ $color->id }})"
                                        wire:confirm="{{ __('general.are_you_sure') }}"
                                    >
                                        {{ __('general.delete') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-gray-500 dark:text-gray-400">{{ __('general.no_colors_added') }}</flux:text>
                    @endif
                </div>
            </div>
        @endif
    </div>
</flux:modal>
