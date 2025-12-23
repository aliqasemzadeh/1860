<?php

namespace App\Livewire\Panel\Accounting\Remittance;

use App\Models\Accounting\Remittance;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?Remittance $remittance = null;

    public int $id;

    public string $description = '';

    public string $account_balance = '0';

    public string $payment = '0';

    #[On('accounting.remittance.edit.assign-data')]
    public function assignData($id): void
    {
        $this->remittance = Remittance::findOrFail($id);

        $this->id = $this->remittance->id;
        $this->description = $this->remittance->description;
        $this->account_balance = (string) $this->remittance->account_balance;
        $this->payment = (string) $this->remittance->payment;

        Flux::modal('accounting.remittance.edit.modal')->show();
    }

    public function edit(): void
    {
        $this->authorize('accounting_remittance_edit');

        if (! isset($this->remittance)) {
            return;
        }

        $validated = $this->validate([
            'description' => ['required', 'string', 'max:255'],
            'account_balance' => ['required', 'min:0'],
            'payment' => ['nullable', 'min:0'],
        ]);

        $accountBalance = (float) str_replace(',', '', $validated['account_balance']);
        $payment = $validated['payment'] !== null && $validated['payment'] !== ''
            ? (float) str_replace(',', '', $validated['payment'])
            : 0;

        $this->remittance->update([
            'description' => $validated['description'],
            'account_balance' => $accountBalance,
            'payment' => $payment,
        ]);

        Flux::toast(variant: 'success', text: __('app.remittance_updated'));
        $this->dispatch('accounting.remittance.index.render');
        Flux::modal('accounting.remittance.edit.modal')->close();
    }

    public function render()
    {
        return view('livewire.panel.accounting.remittance.edit');
    }
}
