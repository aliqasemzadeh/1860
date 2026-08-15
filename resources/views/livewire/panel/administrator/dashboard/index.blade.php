<x-slot name="title">
    {{ __('general.dashboard') }}
</x-slot>
<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.administrator') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.administrator_description') }}</flux:subheading>
            </div>
            <flux:tooltip content="{{ __('general.refresh') }}">
                <flux:button
                    size="sm"
                    variant="primary"
                    color="teal"
                    icon="rotate-cw"
                    icon:variant="outline"
                    wire:click="refresh"
                />
            </flux:tooltip>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        @can('administrator_user_management_index')
            <a href="{{ route('panel.administrator.user-management.user.index') }}" wire:navigate class="block">
                <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors space-y-2">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.total_users') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['users_total'] }}</flux:heading>
                    <flux:text class="text-xs text-zinc-500">
                        {{ __('general.verified_users') }}: {{ $this->stats['users_verified'] }}
                        ·
                        {{ __('general.new_users_this_month') }}: {{ $this->stats['users_recent'] }}
                    </flux:text>
                </flux:card>
            </a>
        @endcan

        @can('administrator_user_management_role_index')
            <a href="{{ route('panel.administrator.user-management.role.index') }}" wire:navigate class="block">
                <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors space-y-2">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.total_roles') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['roles_total'] }}</flux:heading>
                </flux:card>
            </a>
        @endcan

        @can('administrator_user_management_permission_index')
            <a href="{{ route('panel.administrator.user-management.permission.index') }}" wire:navigate class="block">
                <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors space-y-2">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.total_permissions') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['permissions_total'] }}</flux:heading>
                </flux:card>
            </a>
        @endcan

        @can('administrator_setting_backup_index')
            <a href="{{ route('panel.administrator.setting-management.backup.index') }}" wire:navigate class="block">
                <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors space-y-2">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.total_backups') }}</flux:text>
                    <flux:heading size="xl">{{ $this->systemStatus['backups_count'] }}</flux:heading>
                    <flux:text class="text-xs text-zinc-500">
                        {{ __('general.last_backup') }}:
                        @if ($this->systemStatus['last_backup_at'])
                            {{ jalali($this->systemStatus['last_backup_at'], 'Y/m/d H:i') }}
                        @else
                            {{ __('general.never') }}
                        @endif
                    </flux:text>
                </flux:card>
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <flux:card class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">{{ __('general.latest_users') }}</flux:heading>
                @can('administrator_user_management_index')
                    <flux:button
                        size="xs"
                        variant="ghost"
                        href="{{ route('panel.administrator.user-management.user.index') }}"
                        wire:navigate
                    >
                        {{ __('general.view_all') }}
                    </flux:button>
                @endcan
            </div>

            @if ($this->latestUsers->isEmpty())
                <flux:text class="text-sm text-zinc-500">{{ __('general.no_data') }}</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('general.name') }}</flux:table.column>
                        <flux:table.column>{{ __('general.mobile') }}</flux:table.column>
                        <flux:table.column>{{ __('general.role') }}</flux:table.column>
                        <flux:table.column>{{ __('general.registered_at') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->latestUsers as $user)
                            <flux:table.row :key="$user->id">
                                <flux:table.cell>{{ $user->name ?: '-' }}</flux:table.cell>
                                <flux:table.cell dir="ltr">{{ $user->mobile }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <flux:badge size="sm" color="blue">{{ $role->name }}</flux:badge>
                                        @empty
                                            <flux:text class="text-sm text-zinc-500">-</flux:text>
                                        @endforelse
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ jalali($user->created_at, 'Y/m/d') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        <div class="space-y-6">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('general.system_status') }}</flux:heading>

                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-sm text-zinc-500">{{ __('general.maintenance_mode') }}</flux:text>
                        <flux:badge size="sm" :color="$this->systemStatus['maintenance'] ? 'red' : 'green'">
                            {{ $this->systemStatus['maintenance'] ? __('general.active') : __('general.inactive') }}
                        </flux:badge>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-sm text-zinc-500">{{ __('general.app_environment') }}</flux:text>
                        <flux:text class="font-medium" dir="ltr">{{ $this->systemStatus['environment'] }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-sm text-zinc-500">{{ __('general.laravel_version') }}</flux:text>
                        <flux:text class="font-medium" dir="ltr">{{ $this->systemStatus['laravel_version'] }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-sm text-zinc-500">{{ __('general.php_version') }}</flux:text>
                        <flux:text class="font-medium" dir="ltr">{{ $this->systemStatus['php_version'] }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-sm text-zinc-500">{{ __('general.last_backup') }}</flux:text>
                        <flux:text class="font-medium">
                            @if ($this->systemStatus['last_backup_at'])
                                {{ jalali($this->systemStatus['last_backup_at'], 'Y/m/d H:i') }}
                            @else
                                {{ __('general.never') }}
                            @endif
                        </flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-sm text-zinc-500">{{ __('general.backup_size') }}</flux:text>
                        <flux:text class="font-medium" dir="ltr">
                            {{ number_format($this->systemStatus['backup_size'] / 1048576, 2) }} MB
                        </flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="lg">{{ __('general.quick_access') }}</flux:heading>

                @can('administrator_setting_option_index')
                    <flux:button
                        class="w-full"
                        variant="primary"
                        color="indigo"
                        href="{{ route('panel.administrator.setting-management.option.index') }}"
                        wire:navigate
                    >
                        {{ __('general.options') }}
                    </flux:button>
                @endcan

                @can('administrator_setting_function_index')
                    <flux:button
                        class="w-full"
                        variant="primary"
                        color="violet"
                        href="{{ route('panel.administrator.setting-management.function.index') }}"
                        wire:navigate
                    >
                        {{ __('general.function') }}
                    </flux:button>
                @endcan

                @can('administrator_setting_backup_index')
                    <flux:button
                        class="w-full"
                        variant="primary"
                        color="amber"
                        href="{{ route('panel.administrator.setting-management.backup.index') }}"
                        wire:navigate
                    >
                        {{ __('general.backup') }}
                    </flux:button>
                @endcan
            </flux:card>
        </div>
    </div>

    @can('administrator_setting_function_index')
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <flux:heading size="lg">{{ __('general.recent_commands') }}</flux:heading>
                    @if ($this->runningCommands > 0)
                        <flux:badge size="sm" color="amber">
                            {{ __('general.running_commands') }}: {{ $this->runningCommands }}
                        </flux:badge>
                    @endif
                </div>
                <flux:button
                    size="xs"
                    variant="ghost"
                    href="{{ route('panel.administrator.setting-management.function.index') }}"
                    wire:navigate
                >
                    {{ __('general.view_all') }}
                </flux:button>
            </div>

            @if ($this->recentCommands->isEmpty())
                <flux:text class="text-sm text-zinc-500">{{ __('general.no_command_logs') }}</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('general.command') }}</flux:table.column>
                        <flux:table.column>{{ __('general.status') }}</flux:table.column>
                        <flux:table.column>{{ __('general.execution_time') }}</flux:table.column>
                        <flux:table.column>{{ __('general.date') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->recentCommands as $log)
                            <flux:table.row :key="$log->id">
                                <flux:table.cell class="max-w-xs truncate font-mono text-xs" title="{{ $log->command }}">
                                    {{ $log->command }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge
                                        size="sm"
                                        :color="$log->status === 'success' ? 'teal' : ($log->status === 'running' ? 'amber' : 'red')"
                                    >
                                        {{ __('general.' . $log->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    {{ $log->execution_time_ms ? $log->execution_time_ms . 'ms' : '-' }}
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ jalali($log->created_at, 'Y/m/d H:i') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    @endcan
</div>
