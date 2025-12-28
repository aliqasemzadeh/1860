<?php

namespace App\Livewire\Panel\Administrator\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        $this->authorize('administrator_dashboard_index');
        return view('livewire.panel.administrator.dashboard.index');
    }
}
