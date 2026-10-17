<div>
    <flux:modal name="panel.content.banner.edit.modal" class="md:w-1/2" flyout position="right">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('general.edit_banner') }}</flux:heading>
                <flux:text class="mt-2">{{ __('general.edit_banner_description') }}</flux:text>
            </div>

            <form wire:submit="edit" method="post" class="space-y-5">
                <flux:field>
                    <flux:label>{{ __('general.title') }}</flux:label>
                    <flux:input wire:model="form.title" type="text" placeholder="{{ __('general.title') }}" />
                    <flux:error name="form.title" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.banner_link') }}</flux:label>
                    <flux:input
                        wire:model="form.link"
                        type="text"
                        dir="ltr"
                        placeholder="{{ __('general.banner_link_placeholder') }}"
                    />
                    <flux:error name="form.link" />
                </flux:field>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('general.sort_order') }}</flux:label>
                        <flux:input wire:model="form.sort_order" type="number" min="0" />
                        <flux:error name="form.sort_order" />
                    </flux:field>

                    <div class="flex items-end pb-2">
                        <flux:field variant="inline">
                            <flux:label>{{ __('general.active') }}</flux:label>
                            <flux:switch wire:model="form.is_active" />
                        </flux:field>
                    </div>
                </div>

                <flux:field>
                    <flux:label>{{ __('general.desktop_image') }}</flux:label>
                    <flux:file-upload wire:model="desktop_file" label="{{ __('general.select_desktop_image') }}">
                        <flux:file-upload.dropzone
                            heading="{{ __('general.select_desktop_image') }}"
                            text="JPG, PNG, WEBP -> Auto WebP (1600w / 1024w)"
                            with-progress
                            inline
                        />
                    </flux:file-upload>
                    <flux:error name="desktop_file" />

                    @if ($desktop_file)
                        <div class="mt-3 flex flex-col gap-2">
                            <img src="{{ $desktop_file->temporaryUrl() }}" alt="" class="h-36 w-full rounded-xl object-cover" />
                            <flux:file-item
                                :heading="$desktop_file->getClientOriginalName()"
                                :size="$desktop_file->getSize()"
                            >
                                <x-slot name="actions">
                                    <flux:file-item.remove wire:click="removeDesktopFile" aria-label="{{ __('general.file_removed') }}" />
                                </x-slot>
                            </flux:file-item>
                        </div>
                    @elseif ($banner?->desktop_image_url)
                        <div class="mt-3 flex flex-col gap-2">
                            <img
                                src="{{ $banner->desktop_image_url }}"
                                alt="{{ $form->title }}"
                                class="h-36 w-full rounded-xl border border-zinc-200 object-cover dark:border-zinc-700"
                            />
                        </div>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.mobile_image') }}</flux:label>
                    <flux:description>{{ __('general.mobile_image_optional_help') }}</flux:description>
                    <flux:file-upload wire:model="mobile_file" label="{{ __('general.select_mobile_image') }}">
                        <flux:file-upload.dropzone
                            heading="{{ __('general.select_mobile_image') }}"
                            text="JPG, PNG, WEBP -> Auto WebP (768w / 640w)"
                            with-progress
                            inline
                        />
                    </flux:file-upload>
                    <flux:error name="mobile_file" />

                    @if ($mobile_file)
                        <div class="mt-3 flex flex-col gap-2">
                            <img src="{{ $mobile_file->temporaryUrl() }}" alt="" class="h-36 w-48 rounded-xl object-cover" />
                            <flux:file-item
                                :heading="$mobile_file->getClientOriginalName()"
                                :size="$mobile_file->getSize()"
                            >
                                <x-slot name="actions">
                                    <flux:file-item.remove wire:click="removeMobileFile" aria-label="{{ __('general.file_removed') }}" />
                                </x-slot>
                            </flux:file-item>
                        </div>
                    @elseif ($banner?->mobile_image_url)
                        <div class="mt-3 flex flex-col gap-2">
                            <img
                                src="{{ $banner->mobile_image_url }}"
                                alt="{{ $form->title }}"
                                class="h-36 w-48 rounded-xl border border-zinc-200 object-cover dark:border-zinc-700"
                            />
                        </div>
                    @endif
                </flux:field>

                <flux:button type="submit" class="mt-6 w-full" variant="primary" color="orange">
                    {{ __('general.save') }}
                </flux:button>
            </form>
        </div>
    </flux:modal>
</div>
