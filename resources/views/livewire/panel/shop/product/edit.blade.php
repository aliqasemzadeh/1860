<flux:modal name="panel.shop.product.edit.modal" class="md:w-2/3" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.edit_product') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.edit_product_description') }}</flux:text>
        </div>

        <form wire:submit="edit" method="post">
            <div class="space-y-6">
                <!-- First Row: 2 columns -->
                <div class="grid grid-cols-2 gap-6">
                    <!-- Column 1: name, description, category_id, brand_id, unit_id -->
                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('general.name') }}</flux:label>
                            <flux:input wire:model.live.debounce.500ms="name" type="text" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.en_name') }}</flux:label>
                            <flux:input wire:model="en_name" type="text" />
                            <flux:error name="en_name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.tags') }}</flux:label>
                            <flux:pillbox wire:model="tags" searchable />
                            <flux:error name="tags" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.description') }}</flux:label>
                            <flux:editor wire:model="description" />
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.category') }}</flux:label>
                            <flux:input.group>
                                <flux:select wire:model="category_id" variant="combobox" :filter="false" placeholder="{{ __('general.select_category') }}">
                                    <x-slot name="input">
                                        <flux:select.input wire:model.live="category_search" placeholder="{{ __('general.search') }}..." />
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
                            <flux:label>{{ __('general.brand') }}</flux:label>
                            <flux:input.group>
                                <flux:select wire:model="brand_id" variant="combobox" :filter="false" placeholder="{{ __('general.select_brand') }}">
                                    <x-slot name="input">
                                        <flux:select.input wire:model.live="brand_search" placeholder="{{ __('general.search') }}..." />
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

                        <flux:field>
                            <flux:label>{{ __('general.unit') }}</flux:label>
                            <flux:input.group>
                                <flux:select wire:model="unit_id" variant="combobox" :filter="false" placeholder="{{ __('general.select_unit') }}">
                                    <x-slot name="input">
                                        <flux:select.input wire:model.live="unit_search" placeholder="{{ __('general.search') }}..." />
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

                    <!-- Column 2: weight, file, slug, slug_fa -->
                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('general.weight') }}</flux:label>
                            <flux:input.group>
                                <flux:input.group.prefix>{{ __('general.gram') }}</flux:input.group.prefix>
                                <flux:input wire:model="weight" type="number" step="0.01" min="0" />
                            </flux:input.group>
                            <flux:error name="weight" />
                        </flux:field>

                        <flux:field>
                            @if (isset($product) && $product->file_path)
                                <div class="mb-3">
                                    <flux:text class="text-sm text-gray-500 mb-2">{{ __('general.current_file') }}: {{ $product->file_name }}</flux:text>
                                    <div class="mt-2 relative inline-block">
                                        <img src="{{ Storage::url($product->file_path) }}" alt="{{ $product->name }}" class="max-w-full h-auto rounded-lg border border-gray-200 dark:border-gray-700" style="max-height: 200px;" />
                                        <div class="absolute top-2 right-2 flex gap-2">
                                            <flux:button
                                                wire:click="removeBackground"
                                                wire:loading.attr="disabled"
                                                variant="ghost"
                                                icon="palette"
                                                size="sm"
                                                square
                                                class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm"
                                                aria-label="{{ __('general.remove_background') }}"
                                                title="{{ __('general.remove_background') }}"
                                            />
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <flux:file-upload wire:model="file" label="{{ __('general.file_upload') }}">
                                <flux:file-upload.dropzone
                                    heading="{{ __('general.file_upload_description') }}"
                                    text="JPG, PNG, GIF, PDF up to 10MB"
                                    with-progress
                                    inline
                                />
                            </flux:file-upload>
                            @if ($file)
                                <div class="mt-3 flex flex-col gap-2">
                                    <flux:file-item
                                        :heading="$file->getClientOriginalName()"
                                        :size="$file->getSize()"
                                    >
                                        <x-slot name="actions">
                                            <flux:file-item.remove wire:click="removeFile" aria-label="{{ __('general.file_removed') }}" />
                                        </x-slot>
                                    </flux:file-item>
                                </div>
                            @endif
                            <flux:error name="file" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.slug') }}</flux:label>
                            <flux:input wire:model="slug" type="text" />
                            <flux:error name="slug" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.slug_fa') }}</flux:label>
                            <flux:input wire:model="slug_fa" type="text" />
                            <flux:error name="slug_fa" />
                        </flux:field>

                        <div class="grid grid-cols-3 gap-6">
                            <flux:field>
                                <flux:label>{{ __('general.x_dimension') }}</flux:label>
                                <flux:input.group>
                                    <flux:input.group.prefix>mm</flux:input.group.prefix>
                                    <flux:input wire:model="x_dimension" type="number" step="0.01" min="0" />
                                </flux:input.group>
                                <flux:error name="x_dimension" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('general.y_dimension') }}</flux:label>
                                <flux:input.group>
                                    <flux:input.group.prefix>mm</flux:input.group.prefix>
                                    <flux:input wire:model="y_dimension" type="number" step="0.01" min="0" />
                                </flux:input.group>
                                <flux:error name="y_dimension" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('general.z_dimension') }}</flux:label>
                                <flux:input.group>
                                    <flux:input.group.prefix>mm</flux:input.group.prefix>
                                    <flux:input wire:model="z_dimension" type="number" step="0.01" min="0" />
                                </flux:input.group>
                                <flux:error name="z_dimension" />
                            </flux:field>
                        </div>
                    </div>
                </div>


            </div>
            <flux:button type="submit" class="w-full mt-6" variant="primary">
                {{ __('general.update') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
