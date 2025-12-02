<?php

namespace App\Livewire\ServiceCenter\Repair;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.service-center')]
    public function render()
    {
        return view('livewire.service-center.repair.index');
    }
}
