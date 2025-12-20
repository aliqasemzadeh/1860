<?php

namespace App\Livewire\Panel\Accounting\Bank;

use App\Models\Accounting\Bank;
use Flux\Flux;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public ?string $code = null;

    public ?string $number = null;

    public ?string $iban = null;

    public ?string $card_number = null;

    public ?string $description = null;

    public int $sort_order = 0;

    public string $init_balance = '0';

    public function create()
    {
        $this->authorize('accounting_bank_create');

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

        $bank = Bank::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'number' => $validated['number'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'card_number' => $validated['card_number'] ?? null,
            'description' => $validated['description'],
            'sort_order' => $validated['sort_order'],
            'init_balance' => $initBalance,
            'balance' => $initBalance,
        ]);

        Flux::toast(variant: 'success', text: __('app.bank_created'));
        $this->dispatch('accounting.bank.index.render');
        Flux::modal('accounting.bank.create.modal')->close();

        $this->reset(['name', 'code', 'number', 'iban', 'card_number', 'description', 'sort_order', 'init_balance']);
    }

    public function render()
    {
        return view('livewire.panel.accounting.bank.create');
    }
}
