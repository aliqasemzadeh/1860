<flux:modal name="panel.shop.product.create.image-wizard.modal" class="md:w-[800px]" flyout position="right">
    <style>
        [data-flux-file-item] img {
            object-fit: cover;
        }
        .image-selected {
            border: 3px solid rgb(59 130 246);
            border-radius: 0.5rem;
        }
    </style>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.select_product_image_from_url') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.select_product_image_description') }}</flux:text>
        </div>

        <div class="space-y-4">
            <!-- Site Type Selection -->
            <flux:field>
                <flux:label>{{ __('general.site_type') }}</flux:label>
                <flux:select wire:model.live="site_type" placeholder="{{ __('general.select_site_type') }}">
                    @foreach($this->getSiteTypes() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="site_type" />
            </flux:field>

            <!-- URL Input Section -->
            <flux:field>
                <flux:label>{{ __('general.product_page_url') }}</flux:label>
                <div class="flex gap-2">
                    <flux:input 
                        wire:model.live.debounce.500ms="url" 
                        placeholder="https://example.com/product"
                        icon="link"
                        class="flex-1"
                    />
                    <flux:button 
                        wire:click="fetchImages" 
                        variant="primary"
                        :disabled="$isLoading || !$url || !$site_type"
                    >
                        {{ __('general.fetch') }}
                    </flux:button>
                </div>
                <flux:error name="url" />
                @if ($isLoading)
                    <flux:text class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('general.loading') }}...
                    </flux:text>
                @endif
            </flux:field>

            <!-- Images List -->
            @if (count($images) > 0)
                <div class="space-y-4">
                    <flux:heading size="sm">{{ __('general.fetched_images') }} ({{ count($images) }})</flux:heading>
                    
                    <div class="mt-4 grid grid-cols-2 gap-4 max-h-[500px] overflow-y-auto">
                        @foreach ($images as $index => $image)
                            <div 
                                class="border rounded-lg p-3 space-y-2 cursor-pointer transition-all {{ $selectedImageUrl === $image['url'] ? 'image-selected bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}"
                                wire:click="selectImage('{{ $image['id'] }}')"
                            >
                                <div dir="ltr">
                                    <flux:file-item
                                        :heading="$image['name']"
                                        :image="$image['url']"
                                        size="0"
                                    >
                                        <x-slot name="actions">
                                            <flux:file-item.remove 
                                                wire:click.stop="removeImage('{{ $image['id'] }}')"
                                                aria-label="{{ __('general.remove_file') }}: {{ $image['name'] }}"
                                            />
                                        </x-slot>
                                    </flux:file-item>
                                </div>
                                <div dir="ltr" class="text-xs text-gray-500 dark:text-gray-400 truncate px-1">
                                    {{ $image['url'] }}
                                </div>
                                @if ($selectedImageUrl === $image['url'])
                                    <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                                        {{ __('general.selected') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Confirm Selection Button -->
                    <flux:button 
                        wire:click="confirmSelection" 
                        variant="primary" 
                        class="w-full"
                        :disabled="$isLoading || !$selectedImageUrl"
                    >
                        {{ __('general.confirm_selection') }}
                    </flux:button>
                </div>
            @else
                <flux:text class="text-gray-500 dark:text-gray-400">
                    {{ __('general.no_images_fetched_yet') }}
                </flux:text>
            @endif
        </div>
    </div>
</flux:modal>

