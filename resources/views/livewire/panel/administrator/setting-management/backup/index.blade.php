<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.backup') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.backups_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="panel.administrator.setting-management.backup.create.modal">
                <flux:button variant="primary" color="teal" icon="plus">{{ __('general.create_backup') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.administrator.setting-management.backup.create :key="'backup-create'" />

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('general.backup_disk') }}</flux:table.column>
            <flux:table.column>{{ __('general.backup_filename') }}</flux:table.column>
            <flux:table.column>{{ __('general.backup_date') }}</flux:table.column>
            <flux:table.column>{{ __('general.backup_size') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @forelse ($this->backups as $backup)
            <flux:table.row :key="$backup['disk'].'-'.$backup['path']">
                <flux:table.cell class="whitespace-nowrap">
                    {{ $backup['disk'] === 'backup_remote' ? __('general.backup_destination_remote') : __('general.backup_destination_local') }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $backup['filename'] }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($backup['date']) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ number_format($backup['size'] / 1048576, 2) }} MB
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:tooltip content="{{ __('general.download') }}">
                            <flux:button
                                size="xs"
                                variant="primary"
                                color="sky"
                                icon="download"
                                icon:variant="outline"
                                wire:click="download('{{ $backup['disk'] }}', '{{ $backup['path'] }}')"
                            />
                        </flux:tooltip>

                        <flux:tooltip content="{{ __('general.delete') }}">
                            <flux:modal.trigger name="panel.administrator.setting-management.backup.delete.{{ $loop->index }}">
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    color="red"
                                    icon="trash"
                                    icon:variant="outline"
                                />
                            </flux:modal.trigger>
                        </flux:tooltip>

                        <flux:modal name="panel.administrator.setting-management.backup.delete.{{ $loop->index }}" class="min-w-[22rem]">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>

                                    <flux:text class="mt-2">
                                        {{ __('general.delete_warning_message') }}<br>
                                        {{ __('general.action_cannot_be_reversed') }}
                                    </flux:text>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />

                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('general.cancel') }}</flux:button>
                                    </flux:modal.close>

                                    <flux:button type="submit" variant="danger" wire:click="delete('{{ $backup['disk'] }}', '{{ $backup['path'] }}')">{{ __('general.delete') }}</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="5">
                    {{ __('general.no_backups_found') }}
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table>

    <div class="mt-4">
        {{ $this->backups->links() }}
    </div>
</div>
