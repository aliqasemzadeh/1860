<?php

namespace App\Livewire\Panel\Accounting\Sepidar\Bank;

use App\Models\Sepidar\RPA\BankAccountBalance;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public $totalBalance = 0;

    public function render()
    {
        $bankAccounts = BankAccountBalance::all();
        return view('livewire.panel.accounting.sepidar.bank.index', compact('bankAccounts'));
    }
}
