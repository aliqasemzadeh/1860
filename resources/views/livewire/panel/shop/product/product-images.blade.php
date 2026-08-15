<flux:modal name="panel.shop.product.images.modal" class="md:w-[600px]" flyout position="right">
    <style>
        [data-flux-file-item] img {
            object-fit: cover;
        }
    </style>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.product_images') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.product_images_description') }}</flux:text>
        </div>

        @if ($product)
            <div class="space-y-4">
                <!-- Fetch Images from URL Button -->
                <flux:button
                    wire:click="openWizard"
                    variant="primary"
                    icon="link"
                >
                    {{ __('general.fetch_images_from_url') }}
                </flux:button>

                <!-- Upload Images Section -->
                    <flux:field>
                        <flux:label>{{ __('general.add_images') }}</flux:label>
                        <form wire:submit="save">
                            <flux:file-upload wire:model="images" label="{{ __('general.upload_images') }}" multiple>
                                <flux:file-upload.dropzone
                                    heading="{{ __('general.drop_files_here') }}"
                                    text="{{ __('general.image_upload_hint') }}"
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
                                                    aria-label="{{ __('general.remove_file') }}: {{ $image->getClientOriginalName() }}"
                                                />
                                            </x-slot>
                                        </flux:file-item>
                                    @endforeach
                                </div>
                            @endif

                                <flux:button type="submit" variant="primary" class="w-full mt-4" :disabled="count($images) === 0">
                                    {{ __('general.save') }}
                                </flux:button>
                        </form>
                    </flux:field>

                <!-- Images List -->
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <flux:heading size="sm" class="mb-4">{{ __('general.product_images_list') }}</flux:heading>

                    @if ($product->images->count() > 0)
                        <div class="mt-4 flex flex-col gap-2">
                            @foreach ($product->images as $image)
                                <div dir="ltr">
                                    @php
                                        $fileSize = \Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path) 
                                            ? \Illuminate\Support\Facades\Storage::disk('public')->size($image->file_path) 
                                            : 0;
                                        $imageUrl = asset('storage/' . $image->file_path);
                                    @endphp
                                    <flux:file-item
                                        :heading="$image->file_name"
                                        :image="$imageUrl"
                                        :size="$fileSize"
                                    >
                                        <x-slot name="actions">
                                            <flux:button
                                                wire:click="removeBackground({{ $image->id }})"
                                                wire:loading.attr="disabled"
                                                variant="ghost"
                                                icon="palette"
                                                size="sm"
                                                square
                                                aria-label="{{ __('general.remove_background') }}: {{ $image->file_name }}"
                                                title="{{ __('general.remove_background') }}"
                                            />
                                            <flux:file-item.remove 
                                                wire:click="removeProductImage({{ $image->id }})"
                                                wire:confirm="{{ __('general.are_you_sure') }}"
                                                aria-label="{{ __('general.remove_file') }}: {{ $image->file_name }}"
                                            />
                                        </x-slot>
                                    </flux:file-item>
                                    <div dir="ltr" class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate px-2">
                                        {{ $imageUrl }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-gray-500 dark:text-gray-400">{{ __('general.no_images_added') }}</flux:text>
                    @endif
                </div>
            </div>
        @endif
    </div>

</flux:modal>
