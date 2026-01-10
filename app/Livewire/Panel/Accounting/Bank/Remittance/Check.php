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

    public string $final_amount = '0';

    #[On('panel.accounting.bank.remittance.check.assign-data')]
    public function assignData($id): void
    {
        $this->remittance = BankRemittance::findOrFail($id);
        $this->id = $this->remittance->id;
        $this->final_amount = (string) $this->remittance->final_amount;
        Flux::modal('panel.accounting.bank.remittance.check.modal')->show();
    }

    public function check(): void
    {
        $this->authorize('accounting_bank_remittance_check');

        if (! isset($this->remittance)) {
            return;
        }

        $validated = $this->validate([
            'final_amount' => ['required', 'min:0'],
        ]);

        $this->remittance->update([
            'final_amount' => (float) $validated['final_amount'],
            'checked_at' => now(),
            'status' => 'checked',
        ]);

        Flux::toast(__('app.remittance_checked'));
        $this->dispatch('panel.accounting.bank.remittance.index.render');
        Flux::modal('panel.accounting.bank.remittance.check.modal')->close();
    }

    public function reject(): void
    {
        $this->authorize('accounting_bank_remittance_check');

        if (! isset($this->remittance)) {
            return;
        }

        $this->remittance->update([
            'checked_at' => now(),
            'status' => 'rejected',
        ]);

        Flux::toast(__('app.remittance_rejected'));
        $this->dispatch('panel.accounting.bank.remittance.index.render');
        Flux::modal('panel.accounting.bank.remittance.check.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.accounting.bank.remittance.check');
    }
}
