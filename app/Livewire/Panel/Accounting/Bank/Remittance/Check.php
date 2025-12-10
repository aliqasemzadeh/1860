<?php

namespace App\Livewire\Panel\Accounting\Bank\Remittance;

use App\Models\Accounting\BankRemittance;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Check extends Component
{
    public ?BankRemittance $remittance = null;

    public int $id;

    public float $final_amount = 0;

    #[On('accounting.bank.remittance.check.assign-data')]
    public function assignData($id): void
    {
        $this->remittance = BankRemittance::findOrFail($id);
        $this->id = $this->remittance->id;
        $this->final_amount = (float) $this->remittance->final_amount;
        Flux::modal('accounting.bank.remittance.check.modal')->show();
    }

    public function check(): void
    {
        $this->authorize('accounting_bank_remittance_check');

        if (! isset($this->remittance)) {
            return;
        }

        $validated = $this->validate([
            'final_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $this->remittance->update([
            'final_amount' => $validated['final_amount'],
            'checked_at' => now(),
            'status' => 'checked',
        ]);

        $this->dispatch('accounting.bank.remittance.index.render');
        Flux::modal('accounting.bank.remittance.check.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.accounting.bank.remittance.check');
    }
}
