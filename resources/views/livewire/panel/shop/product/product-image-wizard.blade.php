<flux:modal name="panel.shop.product.images.wizard.modal" class="md:w-[800px]" flyout position="right">
    <style>
        [data-flux-file-item] img {
            object-fit: cover;
        }
    </style>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.fetch_images_from_url') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.fetch_images_description') }}</flux:text>
        </div>

        @if ($product)
            <div class="space-y-4">
                <!-- Site Type Selection -->
                <flux:field>
                    <flux:label>{{ __('app.site_type') }}</flux:label>
                    <flux:select wire:model.live="site_type" placeholder="{{ __('app.select_site_type') }}">
                        @foreach($this->getSiteTypes() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="site_type" />
                </flux:field>

                <!-- URL Input Section -->
                <flux:field>
                    <flux:label>{{ __('app.product_page_url') }}</flux:label>
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
                            {{ __('app.fetch') }}
                        </flux:button>
                    </div>
                    <flux:error name="url" />
                    @if ($isLoading)
                        <flux:text class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('app.loading') }}...
                        </flux:text>
                    @endif
                </flux:field>

                <!-- Images List -->
                @if (count($images) > 0)
                    <div class="space-y-4">
                        <flux:heading size="sm">{{ __('app.fetched_images') }} ({{ count($images) }})</flux:heading>
                        
                        <div class="mt-4 flex flex-col gap-2 max-h-[500px] overflow-y-auto">
                            @foreach ($images as $index => $image)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2">
                                    <div dir="ltr">
                                        <flux:file-item
                                            :heading="$image['name']"
                                            :image="$image['url']"
                                            size="0"
                                        >
                                            <x-slot name="actions">
                                                <flux:file-item.remove 
                                                    wire:click="removeImage('{{ $image['id'] }}')"
                                                    aria-label="{{ __('app.remove_file') }}: {{ $image['name'] }}"
                                                />
                                            </x-slot>
                                        </flux:file-item>
                                    </div>
                                    <div dir="ltr" class="space-y-2">
                                        <flux:field>
                                            <flux:label class="text-xs">{{ __('app.image_name') }}</flux:label>
                                            <flux:input 
                                                wire:model.blur="images.{{ $index }}.name"
                                                placeholder="{{ __('app.image_name_placeholder') }}"
                                                class="text-sm"
                                            />
                                        </flux:field>
                                        <flux:field>
                                            <flux:checkbox 
                                                wire:model.live="images.{{ $index }}.optimize"
                                                label="{{ __('app.optimize_image_remove_background') }}"
                                            />
                                            <flux:description class="text-xs">
                                                {{ __('app.optimize_image_description') }}
                                            </flux:description>
                                        </flux:field>
                                        <div dir="ltr" class="text-xs text-gray-500 dark:text-gray-400 truncate px-1">
                                            {{ $image['url'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Save Button -->
                        <flux:button 
                            wire:click="save" 
                            variant="primary" 
                            class="w-full"
                            :disabled="$isLoading"
                        >
                            {{ __('app.upload_images') }} ({{ count($images) }})
                        </flux:button>
                    </div>
                @else
                    <flux:text class="text-gray-500 dark:text-gray-400">
                        {{ __('app.no_images_fetched_yet') }}
                    </flux:text>
                @endif
            </div>
        @endif
    </div>
</flux:modal>
