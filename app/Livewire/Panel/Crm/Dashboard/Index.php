<?php

namespace App\Livewire\Panel\Crm\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.crm')]
    public function render()
    {
        return view('livewire.panel.crm.dashboard.index');
    }
}
