<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.transactions') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.transactions_description') }}</flux:subheading>
            </div>
            @can('accounting_bank_transaction_create')
                <flux:modal.trigger name="accounting.bank.transaction.create.modal">
                    <flux:button variant="primary">{{ __('app.create_transaction_button') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>
    <livewire:panel.accounting.bank.transaction.create />
    <livewire:panel.accounting.bank.transaction.edit />

    <flux:table :paginate="$this->transactions">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="9" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-row gap-2 pe-2 items-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.live="search"
                    />
                    <flux:select wire:model.live="bankFilter" size="sm" placeholder="{{ __('app.filter_bank') }}">
                        <flux:select.option value="">{{ __('app.all_banks') }}</flux:select.option>
                        @foreach ($this->banks as $bank)
                            <flux:select.option value="{{ $bank->id }}">{{ $bank->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="typeFilter" size="sm" placeholder="{{ __('app.filter_transaction_type') }}">
                        <flux:select.option value="">{{ __('app.all_types') }}</flux:select.option>
                        @foreach ($this->transactionTypes as $type)
                            @php
                                $typeEnum = \App\Enums\TransactionTypeEnum::tryFrom($type);
                            @endphp
                            @if ($typeEnum)
                                <flux:select.option value="{{ $type }}">{{ $typeEnum->label() }}</flux:select.option>
                            @endif
                        @endforeach
                    </flux:select>
                </div>
            </flux:table.column>
        </flux:table.columns>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'bank_id'" :direction="$sortDirection" wire:click="sort('bank_id')">{{ __('app.transaction_bank') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'linker'" :direction="$sortDirection" wire:click="sort('linker')">{{ __('app.transaction_type') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">{{ __('app.transaction_amount') }}</flux:table.column>
            <flux:table.column>{{ __('app.transaction_description') }}</flux:table.column>
            <flux:table.column>{{ __('app.transaction_user') }}</flux:table.column>
            <flux:table.column>{{ __('app.transaction_linker_id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.date') }}</flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->transactions as $transaction)
                <flux:table.row :key="$transaction->id">
                    <flux:table.cell>
                        {{ $transaction->id }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $transaction->bank->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $typeEnum = \App\Enums\TransactionTypeEnum::tryFromSafe($transaction->linker);
                        @endphp
                        <flux:badge variant="solid" color="{{ $typeEnum->color() }}" size="sm">
                            {{ $typeEnum->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ number_format(abs($transaction->amount), 0) }} {{ __('app.toman') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $transaction->description ?? '-' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $transaction->user?->first_name ?? '-' }} {{ $transaction->user?->last_name ?? '' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $transaction->linker_id ?? '-' }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ \Morilog\Jalali\Jalalian::fromCarbon($transaction->created_at)->format('%Y-%m-%d %H:%M') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @can('accounting_bank_transaction_edit')
                            <flux:button size="xs" variant="primary" wire:click="$dispatch('accounting.bank.transaction.edit.assign-data', { id: '{{ $transaction->id }}' })">{{ __('app.edit') }}</flux:button>
                        @endcan
                        @can('accounting_bank_transaction_delete')
                            <flux:button size="xs" variant="danger" color="red" wire:click="delete({{ $transaction->id }})" wire:confirm="{{ __('app.are_you_sure') }}">{{ __('app.delete') }}</flux:button>
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
