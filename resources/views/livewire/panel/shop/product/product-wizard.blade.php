<flux:modal name="panel.shop.product.product-wizard.modal" class="md:w-3/4 lg:w-2/3" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.product_wizard') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.product_wizard_description') }}</flux:text>
        </div>

        @if ($step === 1)
            {{-- Step 1: Site selection, brand, category, and URL --}}
            <form wire:submit.prevent="nextStep">
                <div class="space-y-6">
                    <flux:field>
                        <flux:label>{{ __('app.site_type') }}</flux:label>
                        <flux:select wire:model="site_type" placeholder="{{ __('app.select_site_type') }}">
                            <flux:select.option value="fater">{{ __('app.price_fetcher_type_fater') }}</flux:select.option>
                            <flux:select.option value="gigabyte">{{ __('app.price_fetcher_type_gigabyte') }}</flux:select.option>
                            <flux:select.option value="setaregan">{{ __('app.price_fetcher_type_setaregan') }}</flux:select.option>
                            <flux:select.option value="technolife">{{ __('app.price_fetcher_type_technolife') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="site_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.product_wizard_url') }}</flux:label>
                        <flux:input wire:model="url" type="url" placeholder="{{ __('app.product_wizard_url_placeholder') }}" />
                        <flux:description>{{ __('app.product_wizard_url_help') }}</flux:description>
                        <flux:error name="url" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('app.category') }}</flux:label>
                            <flux:input.group>
                                <flux:select wire:model="category_id" variant="combobox" :filter="false" placeholder="{{ __('app.select_category') }}">
                                    <x-slot name="input">
                                        <flux:select.input wire:model.live="category_search" placeholder="{{ __('app.search') }}..." />
                                    </x-slot>
                                    @foreach ($this->categories as $category)
                                        <flux:select.option value="{{ $category->id }}" wire:key="category-{{ $category->id }}">
                                            {{ $category->main_category->name ?? '' }} - {{ $category->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:modal.trigger name="panel.shop.setting-management.category.create.modal">
                                    <flux:button icon="plus" variant="ghost" />
                                </flux:modal.trigger>
                            </flux:input.group>
                            <flux:error name="category_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('app.brand') }}</flux:label>
                            <flux:input.group>
                                <flux:select wire:model="brand_id" variant="combobox" :filter="false" placeholder="{{ __('app.select_brand') }}">
                                    <x-slot name="input">
                                        <flux:select.input wire:model.live="brand_search" placeholder="{{ __('app.search') }}..." />
                                    </x-slot>
                                    @foreach ($this->brands as $brand)
                                        <flux:select.option value="{{ $brand->id }}" wire:key="brand-{{ $brand->id }}">
                                            {{ $brand->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:modal.trigger name="panel.shop.setting-management.brand.create.modal">
                                    <flux:button icon="plus" variant="ghost" />
                                </flux:modal.trigger>
                            </flux:input.group>
                            <flux:error name="brand_id" />
                        </flux:field>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <flux:button type="button" wire:click="closeModal" variant="ghost">
                        {{ __('app.cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('app.fetch_product_info') }}
                    </flux:button>
                </div>
            </form>
        @elseif ($step === 2)
            {{-- Step 2: Preview and edit fetched product information --}}
            <div class="space-y-6">
                @if ($is_fetching)
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mb-4"></div>
                        <flux:text>{{ __('app.fetching_product_info') }}</flux:text>
                    </div>
                @elseif ($fetch_error)
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <flux:text class="text-red-800 dark:text-red-200">{{ $fetch_error }}</flux:text>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <flux:button wire:click="back" variant="ghost">
                            {{ __('app.back') }}
                        </flux:button>
                    </div>
                @elseif ($fetched_data)
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('app.product_info_preview') }}</flux:heading>

                        <form wire:submit.prevent="createProduct">
                            <div class="space-y-6">
                                {{-- Product Images Preview --}}
                                @if (!empty($image_urls))
                                    <div>
                                        <flux:label class="mb-2">{{ __('app.images') }}</flux:label>
                                        <div class="grid grid-cols-4 gap-4">
                                            @foreach ($image_urls as $index => $imageUrl)
                                                <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200">
                                                    <img src="{{ $imageUrl }}" alt="Product Image {{ $index + 1 }}" class="w-full h-full object-cover" />
                                                </div>
                                            @endforeach
                                        </div>
                                        <flux:description class="mt-2">{{ __('app.images_will_be_uploaded_after_confirmation') }}</flux:description>
                                    </div>
                                @endif

                                {{-- Basic Information --}}
                                <div class="grid grid-cols-2 gap-6">
                                    <flux:field>
                                        <flux:label>{{ __('app.name') }}</flux:label>
                                        <flux:input wire:model.live.debounce.500ms="name" type="text" />
                                        <flux:error name="name" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('app.unit') }}</flux:label>
                                        <flux:input.group>
                                            <flux:select wire:model="unit_id" variant="combobox" :filter="false" placeholder="{{ __('app.select_unit') }}">
                                                <x-slot name="input">
                                                    <flux:select.input wire:model.live="unit_search" placeholder="{{ __('app.search') }}..." />
                                                </x-slot>
                                                @foreach ($this->units as $unit)
                                                    <flux:select.option value="{{ $unit->id }}" wire:key="unit-{{ $unit->id }}">
                                                        {{ $unit->name }}
                                                    </flux:select.option>
                                                @endforeach
                                            </flux:select>
                                            <flux:modal.trigger name="panel.shop.setting-management.unit.create.modal">
                                                <flux:button icon="plus" variant="ghost" />
                                            </flux:modal.trigger>
                                        </flux:input.group>
                                        <flux:error name="unit_id" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('app.description') }}</flux:label>
                                    <flux:textarea wire:model="description" rows="8" />
                                    <flux:error name="description" />
                                </flux:field>

                                <div class="grid grid-cols-2 gap-6">
                                    <flux:field>
                                        <flux:label>{{ __('app.slug') }}</flux:label>
                                        <flux:input wire:model="slug" type="text" />
                                        <flux:error name="slug" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('app.slug_fa') }}</flux:label>
                                        <flux:input wire:model="slug_fa" type="text" />
                                        <flux:error name="slug_fa" />
                                    </flux:field>
                                </div>

                                {{-- Weight and Dimensions --}}
                                <div class="grid grid-cols-4 gap-6">
                                    <flux:field>
                                        <flux:label>{{ __('app.weight') }}</flux:label>
                                        <flux:input.group>
                                            <flux:input.group.prefix>{{ __('app.gram') }}</flux:input.group.prefix>
                                            <flux:input wire:model="weight" type="number" step="0.01" min="0" />
                                        </flux:input.group>
                                        <flux:error name="weight" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('app.x_dimension') }}</flux:label>
                                        <flux:input.group>
                                            <flux:input.group.prefix>mm</flux:input.group.prefix>
                                            <flux:input wire:model="x_dimension" type="number" step="0.01" min="0" />
                                        </flux:input.group>
                                        <flux:error name="x_dimension" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('app.y_dimension') }}</flux:label>
                                        <flux:input.group>
                                            <flux:input.group.prefix>mm</flux:input.group.prefix>
                                            <flux:input wire:model="y_dimension" type="number" step="0.01" min="0" />
                                        </flux:input.group>
                                        <flux:error name="y_dimension" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('app.z_dimension') }}</flux:label>
                                        <flux:input.group>
                                            <flux:input.group.prefix>mm</flux:input.group.prefix>
                                            <flux:input wire:model="z_dimension" type="number" step="0.01" min="0" />
                                        </flux:input.group>
                                        <flux:error name="z_dimension" />
                                    </flux:field>
                                </div>

                                {{-- Specifications Preview (if available) --}}
                                @if (isset($fetched_data['specifications']) && !empty($fetched_data['specifications']))
                                    <div>
                                        <flux:label class="mb-2">{{ __('app.technical_specifications') }}</flux:label>
                                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                            @foreach ($fetched_data['specifications'] as $key => $value)
                                                <div class="flex">
                                                    <span class="font-medium text-gray-700 min-w-[150px]">{{ $key }}:</span>
                                                    <span class="text-gray-600">{{ $value }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <flux:description class="mt-2">{{ __('app.specifications_in_description') }}</flux:description>
                                    </div>
                                @endif
                            </div>

                            <div class="flex justify-end gap-3 mt-6">
                                <flux:button type="button" wire:click="back" variant="ghost">
                                    {{ __('app.back') }}
                                </flux:button>
                                <flux:button type="button" wire:click="closeModal" variant="ghost">
                                    {{ __('app.cancel') }}
                                </flux:button>
                                <flux:button type="submit" variant="primary">
                                    {{ __('app.create_product_and_price_fetcher') }}
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>
</flux:modal>
