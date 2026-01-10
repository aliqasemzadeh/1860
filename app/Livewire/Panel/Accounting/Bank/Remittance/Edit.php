<?php

namespace App\Livewire\Panel\Accounting\Bank\Remittance;

use App\Models\Accounting\Bank;
use App\Models\Accounting\BankRemittance;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?BankRemittance $remittance = null;

    public int $id;

    public int $bank_id = 0;

    public string $description = '';

    public string $draft_amount = '0';

    #[\Livewire\Attributes\Computed]
    public function banks()
    {
        return Bank::orderBy('sort_order')->get();
    }

    #[On('panel.accounting.bank.remittance.edit.assign-data')]
    public function assignData($id): void
    {
        $this->remittance = BankRemittance::findOrFail($id);

        if ($this->remittance->checked_at || $this->remittance->status === 'transferred' || $this->remittance->status === 'rejected') {
            return;
        }

        $this->id = $this->remittance->id;
        $this->bank_id = $this->remittance->bank_id;
        $this->description = $this->remittance->description;
        $this->draft_amount = (string) $this->remittance->draft_amount;
        Flux::modal('panel.accounting.bank.remittance.edit.modal')->show();
    }

    public function edit(): void
    {
        $this->authorize('accounting_bank_remittance_edit');

        if (! isset($this->remittance)) {
            return;
        }

        if ($this->remittance->checked_at || $this->remittance->status === 'transferred' || $this->remittance->status === 'rejected') {
            return;
        }

        $validated = $this->validate([
            'bank_id' => ['required', 'exists:banks,id'],
            'description' => ['required', 'string', 'max:255'],
            'draft_amount' => ['required', 'min:0'],
        ]);

        $this->remittance->update([
            'bank_id' => $validated['bank_id'],
            'description' => $validated['description'],
            'draft_amount' => (float) $validated['draft_amount'],
            'final_amount' => (float) $validated['draft_amount'],
        ]);

        Flux::toast(variant: 'success', text: __('app.remittance_updated'));
        $this->dispatch('panel.accounting.bank.remittance.index.render');
        Flux::modal('panel.accounting.bank.remittance.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.accounting.bank.remittance.edit');
    }
}
