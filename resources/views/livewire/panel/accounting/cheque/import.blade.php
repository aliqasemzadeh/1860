<flux:modal name="accounting.cheque.import.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.import_cheques') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.import_cheques_description') }}</flux:text>
        </div>

        <form wire:submit="import" method="post">
            <div class="pb-2 space-y-4">
                <flux:field>
                    <flux:file-upload wire:model="file" label="{{ __('app.file_upload') }}">
                        <flux:file-upload.dropzone
                            heading="{{ __('app.file_upload_description') }}"
                            text="XLSX, XLS, CSV up to 10MB"
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
                                    <flux:file-item.remove wire:click="$set('file', null)" />
                                </x-slot>
                            </flux:file-item>
                        </div>
                    @endif
                    <flux:error name="file" />
                </flux:field>

                <flux:callout variant="subtle">
                    <div class="font-semibold mb-1">{{ __('app.import_cheques') }}</div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-300">
                        {{ __('app.import_cheques_help') }}
                    </div>
                </flux:callout>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.import_cheques') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
