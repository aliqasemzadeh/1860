<?php

namespace App\Livewire\Panel\Accounting\Bank;

use App\Models\Accounting\Bank;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public Bank $bank;

    public int $id;

    public string $name = '';

    public ?string $code = null;

    public ?string $number = null;

    public ?string $iban = null;

    public ?string $card_number = null;

    public ?string $description = null;

    public int $sort_order = 0;

    public string $init_balance = '0';

    #[On('accounting.bank.edit.assign-data')]
    public function assignData($id): void
    {
        $this->bank = Bank::findOrFail($id);
        $this->id = $this->bank->id;
        $this->name = $this->bank->name;
        $this->code = $this->bank->code;
        $this->number = $this->bank->number;
        $this->iban = $this->bank->iban;
        $this->card_number = $this->bank->card_number;
        $this->description = $this->bank->description;
        $this->sort_order = $this->bank->sort_order;
        $this->init_balance = (string) $this->bank->init_balance;
        Flux::modal('accounting.bank.edit.modal')->show();
    }

    public function edit(): void
    {
        $this->authorize('accounting_bank_edit');

        if (! isset($this->bank)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'ir_iban'],
            'card_number' => ['nullable', 'string', 'ir_bank_card_number'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'init_balance' => ['required', 'string'],
        ]);

        $initBalance = (int) str_replace(",", "", $validated['init_balance']);

        $this->bank->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'number' => $validated['number'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'card_number' => $validated['card_number'] ?? null,
            'description' => $validated['description'],
            'sort_order' => $validated['sort_order'],
            'init_balance' => $initBalance,
        ]);

        $this->bank->updateBalance();

        Flux::toast(variant: 'success', text: __('app.bank_updated'));
        $this->dispatch('accounting.bank.index.render');
        Flux::modal('accounting.bank.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.accounting.bank.edit');
    }
}
