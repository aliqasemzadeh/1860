<?php

namespace App\Livewire\Crm\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.crm')]
    public function render()
    {
        return view('livewire.crm.dashboard.index');
    }
}
