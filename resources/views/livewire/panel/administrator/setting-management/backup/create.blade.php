<flux:modal name="panel.administrator.setting-management.backup.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.create_backup') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.create_backup_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('general.backup_type') }}</flux:label>
                    <flux:select wire:model="form.type" searchable>
                        <flux:select.option value="both">{{ __('general.backup_type_both') }}</flux:select.option>
                        <flux:select.option value="database">{{ __('general.backup_type_database') }}</flux:select.option>
                        <flux:select.option value="files">{{ __('general.backup_type_files') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="form.type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.backup_destination') }}</flux:label>
                    <flux:select wire:model="form.destination" searchable>
                        <flux:select.option value="local">{{ __('general.backup_destination_local') }}</flux:select.option>
                        <flux:select.option value="remote">{{ __('general.backup_destination_remote') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="form.destination" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary" color="teal">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
