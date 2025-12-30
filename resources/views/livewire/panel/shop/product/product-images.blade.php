<flux:modal name="panel.shop.product.images.modal" class="md:w-[600px]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.product_images') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.product_images_description') }}</flux:text>
        </div>

        @if ($product)
            <div class="space-y-4">
                <!-- Fetch Images from URL Button -->
                <flux:button
                    wire:click="openWizard"
                    variant="primary"
                    icon="link"
                >
                    {{ __('app.fetch_images_from_url') }}
                </flux:button>

                <!-- Upload Images Section -->
                    <flux:field>
                        <flux:label>{{ __('app.add_images') }}</flux:label>
                        <form wire:submit="save">
                            <flux:file-upload wire:model="images" label="{{ __('app.upload_images') }}" multiple>
                                <flux:file-upload.dropzone
                                    heading="{{ __('app.drop_files_here') }}"
                                    text="{{ __('app.image_upload_hint') }}"
                                    with-progress
                                    inline
                                />
                            </flux:file-upload>
                            <flux:error name="images" />
                            <flux:error name="images.*" />

                            @if (count($images) > 0)
                                <div class="mt-3 flex flex-col gap-2">
                                    @foreach ($images as $index => $image)
                                        <flux:file-item
                                            :heading="$image->getClientOriginalName()"
                                            :image="$image->temporaryUrl()"
                                            :size="$image->getSize()"
                                        >
                                            <x-slot name="actions">
                                                <flux:file-item.remove
                                                    wire:click="removeImage({{ $index }})"
                                                    aria-label="{{ __('app.remove_file') }}: {{ $image->getClientOriginalName() }}"
                                                />
                                            </x-slot>
                                        </flux:file-item>
                                    @endforeach
                                </div>
                            @endif

                                <flux:button type="submit" variant="primary" class="w-full mt-4" :disabled="count($images) === 0">
                                    {{ __('app.save') }}
                                </flux:button>
                        </form>
                    </flux:field>

                <!-- Images List -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:heading size="sm" class="mb-4">{{ __('app.product_images_list') }}</flux:heading>

                    @if ($product->images->count() > 0)
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($product->images as $image)
                                <div class="relative group rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    <img
                                        src="{{ asset('storage/' . $image->file_path) }}"
                                        alt="{{ $image->file_name }}"
                                        class="w-full h-32 object-cover"

                                    />
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center">
                                        <flux:button
                                            size="xs"
                                            variant="danger"
                                            wire:click="removeProductImage({{ $image->id }})"
                                            wire:confirm="{{ __('app.are_you_sure') }}"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                                        >
                                            {{ __('app.delete') }}
                                        </flux:button>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800">
                                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $image->file_name }}">
                                            {{ $image->file_name }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-gray-500 dark:text-gray-400">{{ __('app.no_images_added') }}</flux:text>
                    @endif
                </div>
            </div>
        @endif
    </div>

</flux:modal>
