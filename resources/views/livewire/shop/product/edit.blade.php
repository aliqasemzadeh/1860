<flux:modal name="shop.product.edit.modal" class="md:w-[600px]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.edit_product') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.edit_product_description') }}</flux:text>
        </div>

        <form wire:submit="edit" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('app.name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

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

                <flux:field>
                    <flux:label>{{ __('app.file_upload') }}</flux:label>
                    @if (isset($product) && $product->file_path)
                        <div class="mb-3">
                            <flux:text class="text-sm text-gray-500">{{ __('app.current_file') }}: {{ $product->file_name }}</flux:text>
                        </div>
                    @endif
                    <flux:file-upload wire:model="file" label="{{ __('app.file_upload') }}">
                        <flux:file-upload.dropzone
                            heading="{{ __('app.file_upload_description') }}"
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
                                    <flux:file-item.remove wire:click="removeFile" aria-label="{{ __('app.file_removed') }}" />
                                </x-slot>
                            </flux:file-item>
                        </div>
                    @endif
                    <flux:error name="file" />
                </flux:field>

                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>{{ __('app.weight') }}</flux:label>
                        <flux:input wire:model="weight" type="number" step="0.01" min="0" />
                        <flux:error name="weight" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.unit') }}</flux:label>
                        <flux:select searchable wire:model="unit_id" placeholder="{{ __('app.select_unit') }}">
                            @foreach($units as $unit)
                                <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="unit_id" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label>{{ __('app.x_dimension') }}</flux:label>
                        <flux:input wire:model="x_dimension" type="number" step="0.01" min="0" />
                        <flux:error name="x_dimension" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.y_dimension') }}</flux:label>
                        <flux:input wire:model="y_dimension" type="number" step="0.01" min="0" />
                        <flux:error name="y_dimension" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.z_dimension') }}</flux:label>
                        <flux:input wire:model="z_dimension" type="number" step="0.01" min="0" />
                        <flux:error name="z_dimension" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('app.category') }}</flux:label>
                    <flux:select searchable wire:model="category_id" placeholder="{{ __('app.select_category') }}">
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.brand') }}</flux:label>
                    <flux:select searchable wire:model="brand_id" placeholder="{{ __('app.select_brand') }}">
                        @foreach($brands as $brand)
                            <flux:select.option value="{{ $brand->id }}">{{ $brand->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="brand_id" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.update') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
