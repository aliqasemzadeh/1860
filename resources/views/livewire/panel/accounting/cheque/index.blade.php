<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.cheques') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.cheques_description') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                @can('accounting_cheque_import')
                    <flux:modal.trigger name="accounting.cheque.import.modal">
                        <flux:button>{{ __('app.import_cheques') }}</flux:button>
                    </flux:modal.trigger>
                @endcan
                @can('accounting_cheque_create')
                    <flux:modal.trigger name="accounting.cheque.create.modal">
                        <flux:button variant="primary">{{ __('app.create_cheque') }}</flux:button>
                    </flux:modal.trigger>
                @endcan
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <div class="flex flex-row gap-6 mb-6">
        <div>
            <flux:text>{{ __('app.cheque_amount') }}</flux:text>
            <flux:heading size="xl" class="mb-1">{{ number_format($this->totalAmount, 0) }} {{ __('app.toman') }}</flux:heading>
        </div>
    </div>

    <livewire:panel.accounting.cheque.create />
    <livewire:panel.accounting.cheque.edit />
    <livewire:panel.accounting.cheque.import />
    <livewire:panel.accounting.cheque.report />

    <flux:table :paginate="$this->cheques">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="6" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-row md:flex-row gap-2 pe-2 items-end justify-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.live="search"
                    />
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.due_at') }} (1403/01/01)"
                        wire:model.live="date"
                        mask="9999/99/99"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">
                {{ __('app.id') }}
            </flux:table.column>
            <flux:table.column>{{ __('app.cheque_description') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'due_at'" :direction="$sortDirection" wire:click="sort('due_at')">
                {{ __('app.cheque_due_at') }}
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">
                {{ __('app.cheque_amount') }}
            </flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->cheques as $cheque)
                <flux:table.row :key="$cheque->id">
                    <flux:table.cell>
                        {{ $cheque->id }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $cheque->description }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ \Morilog\Jalali\Jalalian::fromCarbon($cheque->due_at)->format('Y/m/d') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ number_format($cheque->amount, 0) }} {{ __('app.toman') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @can('accounting_cheque_edit')
                            <flux:button size="xs" variant="primary" wire:click="$dispatch('accounting.cheque.edit.assign-data', { id: '{{ $cheque->id }}' })">
                                {{ __('app.edit') }}
                            </flux:button>
                        @endcan
                        @can('accounting_cheque_delete')
                            <flux:button size="xs" variant="danger" color="red" wire:click="delete({{ $cheque->id }})" wire:confirm="{{ __('app.are_you_sure') }}">
                                {{ __('app.delete') }}
                            </flux:button>
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
