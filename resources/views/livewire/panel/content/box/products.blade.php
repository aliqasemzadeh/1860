<flux:modal name="content.box.products" flyout position="right" class="w-full max-w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.box_products') }}{{ $box ? ': ' . $box->title_fa : '' }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.box_products_description') }}</flux:text>
        </div>

        @if ($box)
            <div class="space-y-4">
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:field>
                        <flux:label>{{ __('general.add_product') }}</flux:label>
                        <flux:select wire:model="selectedProductId" variant="combobox" :filter="false" placeholder="{{ __('general.select_product') }}">
                            <x-slot name="input">
                                <flux:select.input wire:model.live="productSearch" placeholder="{{ __('general.search') }}..." />
                            </x-slot>
                            @foreach ($this->availableProducts as $product)
                                <flux:select.option value="{{ $product->id }}" wire:key="available-product-{{ $product->id }}">
                                    {{ $product->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <div class="mt-3">
                            <flux:button wire:click="addProduct" variant="primary" color="teal" class="w-full" :disabled="!$selectedProductId">
                                {{ __('general.add') }}
                            </flux:button>
                        </div>
                    </flux:field>
                </div>

                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:heading size="sm" class="mb-4">{{ __('general.box_products_list') }}</flux:heading>

                    <div class="mb-4">
                        <flux:input wire:model.live.debounce.300ms="listSearch" icon="search" placeholder="{{ __('general.search') }}..." clearable />
                    </div>

                    @if ($this->attachedProducts->count() > 0)
                        <div class="space-y-2">
                            @foreach ($this->attachedProducts as $product)
                                <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-3" wire:key="attached-product-{{ $product->id }}">
                                    <span class="font-medium truncate">{{ $product->name }}</span>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button
                                            size="xs"
                                            variant="danger"
                                            icon="trash"
                                            icon:variant="outline"
                                            wire:click="removeProduct({{ $product->id }})"
                                            wire:confirm="{{ __('general.are_you_sure') }}"
                                        />
                                    </flux:tooltip>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            {{ $listSearch !== '' ? __('general.no_results') : __('general.no_products_added') }}
                        </flux:text>
                    @endif
                </div>
            </div>
        @endif
    </div>
</flux:modal>
