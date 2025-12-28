<flux:modal name="panel.shop.product.warranties.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.product_warranties') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.product_warranties_description') }}</flux:text>
        </div>

        @if ($product)
            <div class="space-y-4">
                <!-- Add Warranty Section -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:field>
                        <flux:label>{{ __('app.add_warranty') }}</flux:label>
                        <flux:select wire:model="selectedWarrantyId" variant="combobox" :filter="false" placeholder="{{ __('app.select_warranty') }}">
                            <x-slot name="input">
                                <flux:select.input wire:model.live="warrantySearch" placeholder="{{ __('app.search') }}..." />
                            </x-slot>
                            @foreach ($this->availableWarranties as $warranty)
                                <flux:select.option value="{{ $warranty->id }}" wire:key="warranty-{{ $warranty->id }}">
                                    {{ $warranty->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <div class="mt-3 flex items-center gap-2">
                            <flux:button wire:click="addWarranty" variant="primary" color="red" :disabled="!$selectedWarrantyId">
                                {{ __('app.add') }}
                            </flux:button>
                            <flux:modal.trigger name="shop.setting-management.warranty.create.modal">
                                <flux:button variant="ghost" color="red" icon="plus" />
                            </flux:modal.trigger>
                        </div>
                    </flux:field>
                </div>

                <!-- Warranties List -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:heading size="sm" class="mb-4">{{ __('app.product_warranties_list') }}</flux:heading>

                    @if ($product->warranties->count() > 0)
                        <div class="space-y-2">
                            @foreach ($product->warranties as $warranty)
                                <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium">{{ $warranty->name }}</span>
                                    </div>
                                    <flux:button
                                        size="xs"
                                        variant="danger"
                                        wire:click="removeWarranty({{ $warranty->id }})"
                                        wire:confirm="{{ __('app.are_you_sure') }}"
                                    >
                                        {{ __('app.delete') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-gray-500 dark:text-gray-400">{{ __('app.no_warranties_added') }}</flux:text>
                    @endif
                </div>
            </div>
        @endif
    </div>
</flux:modal>
