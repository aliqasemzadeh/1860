<?php

namespace App\Livewire\Panel\Accounting\Sepidar\Bank;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public $totalBalance = 0;

    public function render()
    {
        $bankAccounts = Cache::get('sepidar_bank_data', []);
        return view('livewire.panel.accounting.sepidar.bank.index', compact('bankAccounts'));
    }
}
