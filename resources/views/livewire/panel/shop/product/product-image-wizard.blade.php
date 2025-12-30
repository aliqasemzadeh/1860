<flux:modal name="panel.shop.product.images.wizard.modal" class="md:w-[800px]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.fetch_images_from_url') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.fetch_images_description') }}</flux:text>
        </div>

        @if ($product)
            <div class="space-y-4">
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
                            :disabled="$isLoading || !$url"
                        >
                            {{ __('app.fetch') }}
                        </flux:button>
                    </div>
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
                        
                        <div class="space-y-3 max-h-[500px] overflow-y-auto">
                            @foreach ($images as $index => $image)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-3">
                                    <div class="flex items-start gap-4">
                                        <!-- Image Preview -->
                                        <flux:avatar size="xl" src="{{ $image['url'] }}" class="flex-shrink-0" />
                                        
                                        <div class="flex-1 space-y-3">
                                            <!-- Image Name Input -->
                                            <flux:field>
                                                <flux:label>{{ __('app.image_name') }}</flux:label>
                                                <flux:input 
                                                    wire:model.blur="images.{{ $index }}.name"
                                                    placeholder="{{ __('app.image_name_placeholder') }}"
                                                />
                                            </flux:field>
                                            
                                            <!-- Image URL Input (copyable) -->
                                            <flux:field>
                                                <flux:label>{{ __('app.image_url') }}</flux:label>
                                                <flux:input 
                                                    value="{{ $image['url'] }}" 
                                                    icon="link"
                                                    copyable
                                                    readonly
                                                />
                                            </flux:field>
                                        </div>
                                        
                                        <!-- Remove Button -->
                                        <flux:button 
                                            square
                                            variant="ghost"
                                            wire:click="removeImage('{{ $image['id'] }}')"
                                            class="flex-shrink-0"
                                        >
                                            <flux:icon name="x-mark" />
                                        </flux:button>
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
