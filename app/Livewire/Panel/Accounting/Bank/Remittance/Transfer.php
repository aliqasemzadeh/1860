<?php

namespace App\Livewire\Panel\Accounting\Bank\Remittance;

use App\Models\Accounting\BankRemittance;
use App\Models\Accounting\BankTransaction;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Transfer extends Component
{
    public ?BankRemittance $remittance = null;

    public int $id;

    #[On('panel.accounting.bank.remittance.transfer.assign-data')]
    public function assignData($id): void
    {
        $this->remittance = BankRemittance::findOrFail($id);
        $this->id = $this->remittance->id;
        Flux::modal('panel.accounting.bank.remittance.transfer.modal')->show();
    }

    public function transfer(): void
    {
        $this->authorize('accounting_bank_remittance_transfer');

        if (! isset($this->remittance)) {
            return;
        }

        if ($this->remittance->status === 'transferred') {
            return;
        }

        DB::transaction(function () {
            // Update remittance
            $this->remittance->update([
                'transfer_at' => now(),
                'status' => 'transferred',
            ]);

            // Create transaction (negative amount)
            BankTransaction::create([
                'bank_id' => $this->remittance->bank_id,
                'user_id' => Auth::id(),
                'amount' => -$this->remittance->final_amount,
                'linker_id' => $this->remittance->id,
                'linker' => 'Remittance',
                'description' => 'پرداخت حواله #'.$this->remittance->id,
            ]);
        });

        Flux::toast(__('app.remittance_transferred'));
        $this->dispatch('panel.accounting.bank.remittance.index.render');
        Flux::modal('panel.accounting.bank.remittance.transfer.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.accounting.bank.remittance.transfer');
    }
}
