<div
    @if($this->hasRunningLogs || $activeLogId)
        wire:poll.5s="refreshActiveLog"
    @endif
>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('general.function') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('general.function_description') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row">
            <flux:button
                variant="primary"
                color="teal"
                icon="zap"
                class="w-full !h-14 !px-6 !text-base"
                wire:click="runQuickUpdate"
                wire:confirm="{{ __('general.are_you_sure') }}"
            >
                {{ __('general.quick_update') }}
            </flux:button>

            <flux:button
                variant="primary"
                color="blue"
                icon="refresh-cw"
                class="w-full !h-14 !px-6 !text-base"
                wire:click="runFullUpdate"
                wire:confirm="{{ __('general.are_you_sure') }}"
            >
                {{ __('general.full_update') }}
            </flux:button>
        </div>

        <flux:card>
            <flux:autocomplete wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.command_palette') }}..." clearable>
                @foreach(collect($this->commands)->groupBy('category') as $category => $items)
                    <flux:autocomplete.item
                        class="px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50"
                        disabled
                    >
                        {{ $category }}
                    </flux:autocomplete.item>

                    @foreach($items as $command)
                        <flux:autocomplete.item
                            wire:click="runCommand('{{ $command['key'] }}')"
                            icon="{{ $command['icon'] }}"
                        >
                            <div class="flex flex-col">
                                <span>{{ $command['name'] }}</span>
                                <span class="text-xs text-zinc-500">
                                    @if(($command['mode'] ?? 'sync') === 'service' || str_starts_with($command['signature'], 'Job:'))
                                        {{ $command['signature'] }}
                                    @else
                                        php artisan {{ $command['signature'] }}
                                    @endif
                                </span>
                            </div>
                        </flux:autocomplete.item>
                    @endforeach
                @endforeach
            </flux:autocomplete>
        </flux:card>

        <div wire:loading.flex wire:target="runCommand, runQuickUpdate, runFullUpdate, rerunLastCommand, rerunLog" class="items-center gap-2 text-sm text-zinc-500">
            <flux:icon.rotate-cw class="h-4 w-4 animate-spin" />
            <span>{{ __('general.run_command') }}...</span>
        </div>

        @if($lastCommand)
            <flux:card class="overflow-hidden !p-0">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-200 bg-zinc-50 px-4 py-2 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon.terminal class="h-4 w-4 text-zinc-500" />
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ __('general.terminal') }}</span>
                        @if($activeLogId)
                            <flux:badge size="sm" color="blue">
                                {{ __('general.running') }}
                            </flux:badge>
                        @else
                            <flux:badge size="sm" :color="$lastStatus === 0 ? 'teal' : 'red'">
                                {{ $lastStatus === 0 ? __('general.success') : __('general.error') }}
                            </flux:badge>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                        <span>{{ __('general.execution_time') }}: {{ $executionDuration }}ms</span>
                        <flux:separator vertical class="h-3" />
                        <div class="flex gap-1">
                            @if($lastCommandKey && ! $activeLogId)
                                <flux:tooltip content="{{ __('general.rerun') }}">
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="rotate-cw"
                                        wire:click="rerunLastCommand"
                                        wire:confirm="{{ __('general.are_you_sure') }}"
                                    />
                                </flux:tooltip>
                            @endif
                            <flux:tooltip content="{{ __('general.copy_output') }}">
                                <flux:button
                                    size="xs"
                                    variant="ghost"
                                    icon="clipboard"
                                    x-on:click="window.navigator.clipboard.writeText($refs.output.innerText); Flux.toast('{{ __('general.success') }}')"
                                />
                            </flux:tooltip>
                            <flux:tooltip content="{{ __('general.clear_console') }}">
                                <flux:button
                                    size="xs"
                                    variant="ghost"
                                    icon="x"
                                    wire:click="clearConsole"
                                />
                            </flux:tooltip>
                        </div>
                    </div>
                </div>
                <div class="max-h-[500px] overflow-auto bg-zinc-950 p-4 font-mono text-sm leading-relaxed text-zinc-300">
                    <div class="mb-3 flex items-center gap-2 text-zinc-500">
                        <span class="text-emerald-400">$</span>
                        <span class="text-sky-300">{{ $lastCommand }}</span>
                    </div>
                    <pre x-ref="output" class="whitespace-pre-wrap break-words text-zinc-200">{{ $lastOutput }}</pre>
                </div>
            </flux:card>
        @endif

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
            </div>

            @if ($this->logs->isEmpty())
                <flux:text class="text-sm text-zinc-500">{{ __('general.no_command_logs') }}</flux:text>
            @else
                <flux:table :paginate="$this->logs">
                    <flux:table.columns>
                        <flux:table.column>{{ __('general.command') }}</flux:table.column>
                        <flux:table.column>{{ __('general.status') }}</flux:table.column>
                        <flux:table.column>{{ __('general.execution_time') }}</flux:table.column>
                        <flux:table.column>{{ __('general.date') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->logs as $log)
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
                                <flux:table.cell align="end">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($log->status !== 'running')
                                            <flux:tooltip content="{{ __('general.rerun') }}">
                                                <flux:button
                                                    size="xs"
                                                    variant="primary"
                                                    color="blue"
                                                    icon="rotate-cw"
                                                    icon:variant="outline"
                                                    wire:click="rerunLog({{ $log->id }})"
                                                    wire:confirm="{{ __('general.are_you_sure') }}"
                                                />
                                            </flux:tooltip>
                                        @endif
                                        <flux:tooltip content="{{ __('general.view_output') }}">
                                            <flux:button
                                                size="xs"
                                                variant="primary"
                                                color="teal"
                                                icon="eye"
                                                icon:variant="outline"
                                                wire:click="viewLog({{ $log->id }})"
                                            />
                                        </flux:tooltip>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    </div>

    <flux:modal name="panels.administrator.setting-management.function.command-log.detail" class="md:w-[42rem]" flyout position="right">
        @if($this->selectedLog)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ __('general.log_details') }}</flux:heading>
                    <flux:subheading>{{ $this->selectedLog->command }}</flux:subheading>
                </div>

                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <span class="text-zinc-500">{{ __('general.status') }}:</span>
                        <flux:badge
                            size="sm"
                            class="ms-1"
                            :color="$this->selectedLog->status === 'success' ? 'teal' : ($this->selectedLog->status === 'running' ? 'blue' : 'red')"
                        >
                            {{ __('general.' . $this->selectedLog->status) }}
                        </flux:badge>
                    </div>
                    <div>
                        <span class="text-zinc-500">{{ __('general.execution_time') }}:</span>
                        <span>{{ $this->selectedLog->execution_time_ms ? $this->selectedLog->execution_time_ms . 'ms' : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500">{{ __('general.date') }}:</span>
                        <span>{{ jalali($this->selectedLog->created_at) }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500">{{ __('general.status_code') }}:</span>
                        <span class="font-mono">{{ $this->selectedLog->status_code }}</span>
                    </div>
                </div>

                <div class="max-h-[60vh] overflow-auto rounded-lg bg-zinc-950 p-4 font-mono text-xs leading-relaxed text-zinc-300">
                    <div class="mb-2 text-zinc-500">
                        <span class="text-emerald-400">$</span>
                        <span class="text-sky-300">{{ $this->selectedLog->command }}</span>
                    </div>
                    <pre class="whitespace-pre-wrap break-words">{{ \App\Support\SystemCommandGuard::stripAnsi($this->selectedLog->output) }}</pre>
                </div>

                <div class="space-y-2">
                    @if($this->selectedLog->status !== 'running')
                        <flux:button
                            variant="primary"
                            color="orange"
                            icon="rotate-cw"
                            class="w-full"
                            wire:click="rerunLog({{ $this->selectedLog->id }})"
                            wire:confirm="{{ __('general.are_you_sure') }}"
                        >
                            {{ __('general.rerun') }}
                        </flux:button>
                    @endif
                    <flux:modal.close>
                        <flux:button variant="ghost" class="w-full">{{ __('general.close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
