<?php

namespace App\Livewire\Panel\Accounting\Remittance;

use App\Models\Accounting\Remittance;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public string $description = '';

    public string $account_balance = '0';

    public string $payment = '0';

    public function create(): void
    {
        $this->authorize('accounting_remittance_create');

        $validated = $this->validate([
            'description' => ['required', 'string', 'max:255'],
            'account_balance' => ['required', 'min:0'],
            'payment' => ['nullable', 'min:0'],
        ]);

        // Clean money strings (remove thousands separators) before casting
        $accountBalance = (float) str_replace(',', '', $validated['account_balance']);
        $payment = $validated['payment'] !== null && $validated['payment'] !== ''
            ? (float) str_replace(',', '', $validated['payment'])
            : 0;

        Remittance::create([
            'description' => $validated['description'],
            'account_balance' => $accountBalance,
            'payment' => $payment,
        ]);

        Flux::toast(variant: 'success', text: __('app.remittance_created'));

        $this->reset(['description', 'account_balance', 'payment']);

        $this->dispatch('panel.accounting.remittance.index.render');
        Flux::modal('panel.accounting.remittance.create.modal')->close();
    }

    public function render()
    {
        return view('livewire.panel.accounting.remittance.create');
    }
}
