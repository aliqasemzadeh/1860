<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('general.function') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('general.function_description') }}</flux:subheading>
        <flux:separator variant="subtle" />

        <div class="mt-6 space-y-6">
            <div class="flex gap-4">
                <flux:button
                    variant="primary"
                    color="teal"
                    icon="bolt"
                    class="w-full"
                    wire:click="runCommand('update_quick')"
                >
                    {{ __('general.quick_update') }}
                </flux:button>

                <flux:button
                    variant="primary"
                    color="blue"
                    icon="refresh-cw"
                    class="w-full"
                    wire:click="runCommand('update_full')"
                >
                    {{ __('general.full_update') }}
                </flux:button>
            </div>

            <flux:card>
                <flux:autocomplete wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.command_palette') }}..." clearable>
                    @foreach(collect($this->commands)->groupBy('category') as $category => $items)
                        <flux:autocomplete.item
                            class="px-2 py-1.5 text-xs font-semibold text-zinc-500 uppercase tracking-wider bg-zinc-50 dark:bg-zinc-800/50"
                            disabled
                        >
                            {{ $category }}
                        </flux:autocomplete.item>

                        @foreach($items as $key => $command)
                            <flux:autocomplete.item
                                wire:click="runCommand('{{ $key }}')"
                                icon="{{ $command['icon'] }}"
                            >
                                <div class="flex flex-col">
                                    <span>{{ $command['name'] }}</span>
                                    <span class="text-xs text-zinc-500">php artisan {{ $command['signature'] }}</span>
                                </div>
                            </flux:autocomplete.item>
                        @endforeach
                    @endforeach
                </flux:autocomplete>
            </flux:card>

            <div wire:loading.flex class="items-center gap-2 text-sm text-zinc-500">
                <flux:icon.rotate-cw class="h-4 w-4 animate-spin" />
                <span>{{ __('general.run_command') }}...</span>
            </div>

            @if($lastCommand)
                <flux:card class="overflow-hidden !p-0">
                    <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-2 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon.terminal class="h-4 w-4 text-zinc-500" />
                            <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ __('general.terminal') }}</span>
                            <flux:badge size="sm" :inset="false" :color="$lastStatus === 0 ? 'teal' : 'red'">
                                {{ $lastStatus === 0 ? __('general.success') : __('general.error') }}
                            </flux:badge>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-zinc-500">
                            <span>{{ __('general.execution_time') }}: {{ $executionDuration }}ms</span>
                            <flux:separator vertical class="h-3" />
                            <div class="flex gap-1">
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
                        <div class="mb-2 text-zinc-500">
                            <span class="text-emerald-500">$</span> {{ $lastCommand }}
                        </div>
                        <pre x-ref="output" class="whitespace-pre-wrap">{{ $lastOutput }}</pre>
                    </div>
                </flux:card>
            @endif
        </div>
    </div>
</div>
