<?php

namespace App\Livewire\Panel\Accounting\Bank\Transaction;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.accounting')]
    public function render()
    {
        return view('livewire.panel.accounting.bank.transaction.index');
    }
}
