<?php

namespace App\Livewire\Administrator\SettingManagement\Option;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.administrator.setting-management.option.index');
    }
}
