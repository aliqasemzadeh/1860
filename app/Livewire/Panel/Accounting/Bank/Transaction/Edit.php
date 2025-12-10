<?php

namespace App\Livewire\Panel\Accounting\Bank\Transaction;

use App\Models\Accounting\Bank;
use App\Models\Accounting\BankTransaction;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?BankTransaction $transaction = null;

    public int $id;

    public int $bank_id = 0;

    public string $linker = '';

    public string $amount = '0';

    public ?string $description = null;

    public ?int $linker_id = null;

    #[\Livewire\Attributes\Computed]
    public function banks()
    {
        return Bank::orderBy('sort_order')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function transactionTypes()
    {
        return config('accounting.transaction_type', []);
    }

    #[On('accounting.bank.transaction.edit.assign-data')]
    public function assignData($id): void
    {
        $this->transaction = BankTransaction::findOrFail($id);
        $this->id = $this->transaction->id;
        $this->bank_id = $this->transaction->bank_id;
        $this->linker = $this->transaction->linker ?? '';
        $this->amount = (string) $this->transaction->amount;
        $this->description = $this->transaction->description;
        $this->linker_id = $this->transaction->linker_id;
        Flux::modal('accounting.bank.transaction.edit.modal')->show();
    }

    public function edit(): void
    {
        $this->authorize('accounting_bank_transaction_edit');

        if (! isset($this->transaction)) {
            return;
        }

        $validated = $this->validate([
            'bank_id' => ['required', 'exists:banks,id'],
            'linker' => ['required', 'string', 'in:'.implode(',', $this->transactionTypes())],
            'amount' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'linker_id' => ['nullable', 'integer'],
        ]);

        // Convert amount string to float (remove formatting)
        $amountValue = (float) str_replace(',', '', $validated['amount']);

        $this->transaction->update([
            'bank_id' => $validated['bank_id'],
            'linker' => $validated['linker'],
            'amount' => $amountValue,
            'description' => $validated['description'],
            'linker_id' => $validated['linker_id'] ?? 0,
        ]);

        Flux::toast(variant: 'success', text: __('app.transaction_updated'));
        $this->dispatch('accounting.bank.transaction.index.render');
        Flux::modal('accounting.bank.transaction.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.accounting.bank.transaction.edit');
    }
}
