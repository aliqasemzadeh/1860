<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.remittances') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.remittances_description') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                @can('accounting_remittance_create')
                    <flux:modal.trigger name="accounting.remittance.create.modal">
                        <flux:button variant="primary">{{ __('app.create_remittance') }}</flux:button>
                    </flux:modal.trigger>
                @endcan
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <div class="mb-6">
        <flux:text>{{ __('app.remittance_amount') }}</flux:text>
        <flux:heading size="xl" class="mb-1">{{ number_format($this->totalPayment, 0) }} {{ __('app.toman') }}</flux:heading>
    </div>

    <livewire:panel.accounting.remittance.create />
    <livewire:panel.accounting.remittance.edit />

    <flux:table :paginate="$this->remittances">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="6" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col md:flex-row gap-2 pe-2 items-end justify-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.live="search"
                    />
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.date') }} (1403/01/01)"
                        wire:model.live="date"
                        mask="9999/99/99"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column>{{ __('app.remittance_description') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'account_balance'" :direction="$sortDirection" wire:click="sort('account_balance')">{{ __('app.account_balance') }}</flux:table.column>
            <flux:table.column>{{ __('app.remittance_amount') }}</flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->remittances as $remittance)
                <flux:table.row :key="$remittance->id">
                    <flux:table.cell>
                        {{ $remittance->id }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $remittance->description }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ number_format($remittance->account_balance, 0) }} {{ __('app.toman') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:input
                                wire:model.defer="payments.{{ $remittance->id }}"
                                type="text"
                                size="sm"
                                mask:dynamic="$money($input)"
                                placeholder="{{ number_format($remittance->payment, 0) }}"
                            />
                            <flux:button
                                size="xs"
                                variant="primary"
                                wire:click="savePayment({{ $remittance->id }})"
                            >
                                {{ __('app.save') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @can('accounting_remittance_edit')
                            <flux:button size="xs" variant="primary" wire:click="$dispatch('accounting.remittance.edit.assign-data', { id: '{{ $remittance->id }}' })">{{ __('app.edit') }}</flux:button>
                        @endcan
                        @can('accounting_remittance_delete')
                            <flux:button size="xs" variant="danger" color="red" wire:click="delete({{ $remittance->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
