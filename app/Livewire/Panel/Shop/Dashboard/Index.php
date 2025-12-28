<?php

namespace App\Livewire\Panel\Shop\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.shop')]
    public function render()
    {
        return view('livewire.panel.shop.dashboard.index');
    }
}
