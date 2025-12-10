<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.remittances') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.remittances_description') }}</flux:subheading>
            </div>
            @can('accounting_bank_remittance_create')
                <flux:modal.trigger name="accounting.bank.remittance.create.modal">
                    <flux:button variant="primary">{{ __('app.create_remittance') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>
    <livewire:panel.accounting.bank.remittance.create />
    <livewire:panel.accounting.bank.remittance.edit />
    <livewire:panel.accounting.bank.remittance.check />

    <flux:table :paginate="$this->remittances">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="7" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-1 pe-2 items-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.live="search"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'bank_id'" :direction="$sortDirection" wire:click="sort('bank_id')">{{ __('app.remittance_bank') }}</flux:table.column>
            <flux:table.column>{{ __('app.remittance_description') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'draft_amount'" :direction="$sortDirection" wire:click="sort('draft_amount')">{{ __('app.remittance_draft_amount') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'final_amount'" :direction="$sortDirection" wire:click="sort('final_amount')">{{ __('app.remittance_final_amount') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">{{ __('app.remittance_status') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.date') }}</flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->remittances as $remittance)
                <flux:table.row :key="$remittance->id">
                    <flux:table.cell>
                        {{ $remittance->id }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $remittance->bank->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $remittance->description }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ number_format($remittance->draft_amount, 0) }} {{ __('app.toman') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ number_format($remittance->final_amount, 0) }} {{ __('app.toman') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($remittance->status === 'pending')
                            <span class="text-yellow-600">{{ __('app.remittance_status_pending') }}</span>
                        @elseif ($remittance->status === 'checked')
                            <span class="text-green-600">{{ __('app.remittance_status_checked') }}</span>
                        @elseif ($remittance->status === 'transferred')
                            <span class="text-blue-600">{{ __('app.remittance_status_transferred') }}</span>
                        @else
                            {{ $remittance->status }}
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ \Morilog\Jalali\Jalalian::fromCarbon($remittance->created_at)->format('%Y-%m-%d %H:%M') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @if (!$remittance->checked_at)
                            @can('accounting_bank_remittance_edit')
                                <flux:button size="xs" variant="primary" wire:click="$dispatch('accounting.bank.remittance.edit.assign-data', { id: '{{ $remittance->id }}' })">{{ __('app.edit') }}</flux:button>
                            @endcan
                        @endif
                        @if (!$remittance->checked_at)
                            @can('accounting_bank_remittance_check')
                                <flux:button size="xs" variant="primary" wire:click="$dispatch('accounting.bank.remittance.check.assign-data', { id: '{{ $remittance->id }}' })">{{ __('app.check_remittance_button') }}</flux:button>
                            @endcan
                        @endif
                        @can('accounting_bank_remittance_delete')
                            <flux:button size="xs" variant="danger" wire:click="delete({{ $remittance->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
