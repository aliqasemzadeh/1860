<div>
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl" level="1">{{ __('app.accounting') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('app.accounting_description') }}</flux:subheading>
                </div>
            </div>

            <flux:separator variant="subtle" />
        </div>

        <div class="flex flex-col md:flex-row gap-4 md:gap-6 mb-6">
            @foreach ($this->stats as $stat)
                <div class="relative flex-1 rounded-lg px-6 py-4 bg-zinc-50 dark:bg-zinc-700">
                    <flux:subheading>{{ $stat['title'] }}</flux:subheading>

                    <flux:heading size="xl" class="mb-2">{{ $stat['value'] }}</flux:heading>

                    <div class="flex items-center gap-1 font-medium text-sm @if ($stat['trendUp']) text-green-600 dark:text-green-400 @else text-red-500 dark:text-red-400 @endif">
                        <flux:icon :icon="$stat['trendUp'] ? 'arrow-trending-up' : 'arrow-trending-down'" variant="micro" />
                        {{ $stat['trend'] }}
                    </div>

                    <div class="absolute top-0 right-0 pr-2 pt-2">
                        <flux:button icon="ellipsis-horizontal" variant="subtle" size="sm" />
                    </div>
                </div>
            @endforeach
        </div>

        <flux:card>
            <div class="flex items-start justify-between gap-2 mb-4">
                <div>
                    <flux:heading size="md">{{ __('app.upcoming_cheques') }}</flux:heading>
                    <flux:subheading>{{ __('app.upcoming_cheques_description') }}</flux:subheading>
                </div>
            </div>

            <flux:table class="mt-2">
                <flux:table.columns>
                    <flux:table.column class="max-md:hidden">{{ __('app.id') }}</flux:table.column>
                    <flux:table.column class="max-md:hidden">{{ __('app.cheque_due_at') }}</flux:table.column>
                    <flux:table.column>{{ __('app.cheque_description') }}</flux:table.column>
                    <flux:table.column>{{ __('app.cheque_amount') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->upcomingCheques as $cheque)
                        <flux:table.row>
                            <flux:table.cell class="max-md:hidden">#{{ $cheque->id }}</flux:table.cell>
                            <flux:table.cell class="max-md:hidden">
                                @if ($cheque->due_at)
                                    {{ \Morilog\Jalali\Jalalian::fromCarbon($cheque->due_at)->format('Y/m/d') }}
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="truncate">
                                {{ $cheque->description ?: __('app.empty_description') }}
                            </flux:table.cell>
                            <flux:table.cell class="font-semibold">
                                {{ number_format($cheque->amount) }} {{ __('app.toman') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center text-sm text-zinc-500">
                                {{ __('app.no_upcoming_cheques') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <flux:card class="mt-6">
            <div class="flex items-start justify-between gap-2 mb-4">
                <div>
                    <flux:heading size="md">{{ __('app.last_updates') }}</flux:heading>
                    <flux:subheading>{{ __('app.last_updates_description') }}</flux:subheading>
                </div>
            </div>

            <flux:table class="mt-2">
                <flux:table.columns>
                    <flux:table.column>{{ __('app.type') }}</flux:table.column>
                    <flux:table.column>{{ __('app.last_update_date') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->lastUpdates as $update)
                        <flux:table.row>
                            <flux:table.cell class="font-medium">
                                {{ $update['name'] }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">
                                @if ($update['updated_at'])
                                    {{ \Morilog\Jalali\Jalalian::fromCarbon($update['updated_at'])->format('Y/m/d H:i') }}
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">{{ __('app.no_data') }}</span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
</div>
