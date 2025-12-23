<div class="mb-6 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('app.cheque_report') }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('app.cheque_report_description') }}
            </flux:text>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:field>
            <flux:label>{{ __('app.cheque_report_mode') }}</flux:label>
            <flux:select wire:model.live="mode">
                <flux:select.option value="weekly">{{ __('app.cheque_report_mode_weekly') }}</flux:select.option>
                <flux:select.option value="ten_days">{{ __('app.cheque_report_mode_ten_days') }}</flux:select.option>
                <flux:select.option value="monthly">{{ __('app.cheque_report_mode_monthly') }}</flux:select.option>
                <flux:select.option value="quarterly">{{ __('app.cheque_report_mode_quarterly') }}</flux:select.option>
                <flux:select.option value="semi_annual">{{ __('app.cheque_report_mode_semi_annual') }}</flux:select.option>
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>{{ __('app.cheque_report_start_date') }}</flux:label>
            <flux:input
                wire:model.live="start_date"
                type="text"
                placeholder="1404/01/01"
                mask="9999/99/99"
            />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('app.cheque_report_end_date') }}</flux:label>
            <flux:input
                wire:model.live="end_date"
                type="text"
                placeholder="1404/03/31"
                mask="9999/99/99"
            />
        </flux:field>
    </div>

    <flux:separator variant="subtle" />

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <flux:heading size="sm">{{ __('app.cheque_report_periods') }}</flux:heading>
            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('app.cheque_report_hint') }}
            </flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('app.cheque_report_period_label') }}</flux:table.column>
                <flux:table.column class="text-end">{{ __('app.cheque_report_total') }}</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($periods as $period)
                    <flux:table.row>
                        <flux:table.cell>
                            {{ $period['label'] }}
                        </flux:table.cell>
                        <flux:table.cell class="text-end">
                            {{ number_format($period['total'], 0) }} {{ __('app.toman') }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="2">
                            <flux:callout variant="subtle">
                                {{ __('app.cheque_report_no_data') }}
                            </flux:callout>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
