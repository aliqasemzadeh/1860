<div>
    <flux:modal name="panel.content.post.edit.modal" class="md:w-2/3" flyout position="right">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('app.edit_post') }}</flux:heading>
                <flux:text class="mt-2">{{ __('app.edit_post_description') }}</flux:text>
            </div>

            <form wire:submit="edit" method="post">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('app.title') }}</flux:label>
                            <flux:input wire:model.live.debounce.500ms="form.title" type="text" />
                            <flux:error name="form.title" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('app.slug') }}</flux:label>
                            <flux:input wire:model="form.slug" type="text" />
                            <flux:error name="form.slug" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('app.summary') }}</flux:label>
                            <flux:textarea wire:model="form.summary" rows="3" />
                            <flux:error name="form.summary" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.description') }}</flux:label>
                            <flux:editor wire:model="form.content" />
                            <flux:error name="form.content" />
                        </flux:field>
                    </div>

                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('general.status') }}</flux:label>
                            <flux:select wire:model="form.status" searchable>
                                <flux:select.option value="draft">{{ __('app.post_status_draft') }}</flux:select.option>
                                <flux:select.option value="published">{{ __('app.post_status_published') }}</flux:select.option>
                                <flux:select.option value="archived">{{ __('app.post_status_archived') }}</flux:select.option>
                            </flux:select>
                            <flux:error name="form.status" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('app.featured_image') }}</flux:label>
                            <flux:file-upload wire:model="featured_file" label="{{ __('app.select_featured_image') }}">
                                <flux:file-upload.dropzone
                                    heading="{{ __('app.select_featured_image') }}"
                                    text="JPG, PNG, WEBP up to 5MB"
                                    with-progress
                                    inline
                                />
                            </flux:file-upload>
                            <flux:error name="featured_file" />

                            @if ($featured_file)
                                <div class="mt-3 flex flex-col gap-2">
                                    <img src="{{ $featured_file->temporaryUrl() }}" alt="" class="h-32 w-full rounded-lg object-cover" />
                                    <flux:file-item
                                        :heading="$featured_file->getClientOriginalName()"
                                        :size="$featured_file->getSize()"
                                    >
                                        <x-slot name="actions">
                                            <flux:file-item.remove wire:click="removeFeaturedFile" aria-label="{{ __('app.file_removed') }}" />
                                        </x-slot>
                                    </flux:file-item>
                                </div>
                            @elseif ($form->featured_image)
                                <div class="mt-3 flex flex-col gap-2">
                                    <img
                                        src="{{ Storage::disk('public')->url($form->featured_image) }}"
                                        alt=""
                                        class="h-32 w-full rounded-lg object-cover"
                                    />
                                    <flux:button wire:click="removeFeaturedFile" variant="ghost" size="xs" icon="x-mark" class="w-fit">
                                        {{ __('app.remove') }}
                                    </flux:button>
                                </div>
                            @endif
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('app.related_products') }}</flux:label>
                            <flux:pillbox
                                variant="combobox"
                                multiple
                                :filter="false"
                                wire:model.live="form.product_ids"
                                placeholder="{{ __('app.select_products') }}"
                            >
                                <x-slot name="input">
                                    <flux:pillbox.input wire:model.live.debounce.300ms="product_search" placeholder="{{ __('general.search') }}..." />
                                </x-slot>
                                @foreach ($this->products as $product)
                                    <flux:pillbox.option value="{{ $product->id }}" wire:key="edit-product-{{ $product->id }}">
                                        {{ $product->name }}
                                    </flux:pillbox.option>
                                @endforeach
                            </flux:pillbox>
                            <flux:error name="form.product_ids" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.tags') }}</flux:label>
                            <flux:pillbox wire:model="form.tags_array" searchable placeholder="{{ __('general.tags') }}" />
                            <flux:error name="form.tags_array" />
                        </flux:field>
                    </div>
                </div>

                <flux:button type="submit" class="mt-6 w-full" variant="primary" color="orange">
                    {{ __('general.save') }}
                </flux:button>
            </form>
        </div>
    </flux:modal>
</div>
