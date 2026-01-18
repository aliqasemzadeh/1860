<x-slot name="title">
    {{ __('app.banks') }}
</x-slot>
<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.banks') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.banks_description') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                @can('accounting_bank_create')
                    <flux:modal.trigger name="accounting.bank.create.modal">
                        <flux:button variant="primary">{{ __('app.create_bank') }}</flux:button>
                    </flux:modal.trigger>
                @endcan
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <div class="mb-6">
        <flux:text>{{ __('app.total_balance') }}</flux:text>
        <flux:heading size="xl" class="mb-1">{{ number_format($this->totalBalance, 0) }} {{ __('app.rial') }}</flux:heading>
    </div>

    <livewire:panel.accounting.bank.create />
    <livewire:panel.accounting.bank.edit />

    <flux:table :paginate="$this->banks">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="6" class="bg-white dark:bg-zinc-900">
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
            <flux:table.column>{{ __('app.logo') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('app.bank_name') }}</flux:table.column>
            <flux:table.column>{{ __('app.branch') }}</flux:table.column>
            <flux:table.column>{{ __('app.account_number') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'balance'" :direction="$sortDirection" wire:click="sort('balance')">{{ __('app.bank_balance') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->banks as $bank)
                <flux:table.row :key="$bank->id">
                    <flux:table.cell>
                        @if(config('bank.'.$bank->bankAccount->bankBranch->bank->Title) && file_exists(public_path('images/banks/' . config('bank.'.$bank->bankAccount->bankBranch->bank->Title))))
                            <img src="{{ asset('images/banks/' . config('bank.'.$bank->bankAccount->bankBranch->bank->Title)) }}" alt="{{ $bank->name }}" class="h-8 w-8 object-contain">
                        @else
                            <div class="h-8 w-8 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded">
                                <span class="text-xs text-gray-400">—</span>
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="flex items-center gap-3">
                        {{ $bank->bankAccount->bankBranch->bank->Title }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $bank->bankAccount->bankBranch->Title }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $bank->bankAccount->AccountNo }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ number_format($bank->Balance, 0) }} {{ __('app.rial') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
