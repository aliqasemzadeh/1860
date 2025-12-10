<?php

namespace App\Livewire\Panel\Accounting\Bank\Remittance;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.accounting')]
    public function render()
    {
        return view('livewire.panel.accounting.bank.remittance.index');
    }
}
