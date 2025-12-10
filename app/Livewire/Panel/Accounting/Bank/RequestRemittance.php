<?php

namespace App\Livewire\Panel\Accounting\Bank;

use App\Models\Accounting\Bank;
use App\Models\Accounting\BankRemittance;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class RequestRemittance extends Component
{
    public int $bank_id = 0;

    public string $description = '';

    public float $draft_amount = 0;

    #[On('accounting.bank.request-remittance.assign-data')]
    public function assignData($id): void
    {
        $this->bank_id = $id;
        Flux::modal('accounting.bank.request-remittance.modal')->show();
    }

    #[\Livewire\Attributes\Computed]
    public function banks()
    {
        return Bank::orderBy('sort_order')->get();
    }

    public function request()
    {
        $this->authorize('accounting_bank_remittance_request');

        $validated = $this->validate([
            'bank_id' => ['required', 'exists:banks,id'],
            'description' => ['required', 'string', 'max:255'],
            'draft_amount' => ['required', 'numeric', 'min:0'],
        ]);

        BankRemittance::create([
            'bank_id' => $validated['bank_id'],
            'user_id' => Auth::id(),
            'description' => $validated['description'],
            'draft_amount' => $validated['draft_amount'],
            'final_amount' => $validated['draft_amount'],
            'status' => 'pending',
        ]);

        Flux::toast(__('app.remittance_requested'));
        $this->dispatch('accounting.bank.remittance.index.render');
        Flux::modal('accounting.bank.request-remittance.modal')->close();

        $this->reset(['bank_id', 'description', 'draft_amount']);
    }

    public function render()
    {
        return view('livewire.panel.accounting.bank.request-remittance');
    }
}
