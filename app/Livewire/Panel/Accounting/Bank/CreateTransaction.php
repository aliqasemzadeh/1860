<?php

namespace App\Livewire\Panel\Accounting\Bank;

use App\Models\Accounting\Bank;
use App\Models\Accounting\BankTransaction;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateTransaction extends Component
{
    public int $bank_id = 0;

    public string $linker = '';

    public string $amount = '0';

    public ?string $description = null;

    public ?int $linker_id = null;

    #[On('accounting.bank.create-transaction.assign-data')]
    public function assignData($id): void
    {
        $this->bank_id = $id;
        Flux::modal('accounting.bank.create-transaction.modal')->show();
    }

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

    public function create()
    {
        $this->authorize('accounting_bank_transaction_create');

        $validated = $this->validate([
            'bank_id' => ['required', 'exists:banks,id'],
            'linker' => ['required', 'string', 'in:'.implode(',', $this->transactionTypes())],
            'amount' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'linker_id' => ['nullable', 'integer'],
        ]);

        // Convert amount string to float (remove formatting)
        $amountValue = (float) str_replace(',', '', $validated['amount']);

        // Transaction types that decrease balance (should be stored as negative)
        $decreasingTypes = ['Expense', 'Remittance', 'Payment'];
        
        // Check if transaction would result in negative balance
        $bank = Bank::findOrFail($validated['bank_id']);
        $currentBalance = $bank->calculateBalance();
        
        if (in_array($validated['linker'], $decreasingTypes)) {
            // For decreasing types, check if balance would go negative
            if ($amountValue > $currentBalance) {
                Flux::toast(variant: 'danger', text: __('app.insufficient_balance'));
                return;
            }
            // Store as negative amount
            $amountValue = -$amountValue;
        }

        BankTransaction::create([
            'bank_id' => $validated['bank_id'],
            'user_id' => Auth::id(),
            'linker' => $validated['linker'],
            'amount' => $amountValue,
            'description' => $validated['description'],
            'linker_id' => $validated['linker_id'] ?? 0,
        ]);

        Flux::toast(variant: 'success', text: __('app.transaction_created'));
        $this->dispatch('accounting.bank.transaction.index.render');
        $this->dispatch('accounting.bank.index.render');
        Flux::modal('accounting.bank.create-transaction.modal')->close();

        $this->reset(['bank_id', 'linker', 'amount', 'description', 'linker_id']);
    }

    public function render()
    {
        return view('livewire.panel.accounting.bank.create-transaction');
    }
}
