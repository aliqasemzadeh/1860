<?php

namespace App\Livewire\Panel\User\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.user')]
    public function render()
    {
        return view('livewire.panel.user.dashboard.index');
    }
}
