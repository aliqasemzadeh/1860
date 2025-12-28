<?php

namespace App\Livewire\Panel\ServiceCenter\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('service_center_dashboard_index');
    }

    #[Layout('layouts.panels.service-center')]
    public function render()
    {
        return view('livewire.panel.service-center.dashboard.index');
    }
}
