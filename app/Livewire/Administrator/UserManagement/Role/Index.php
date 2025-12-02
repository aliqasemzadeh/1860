<?php

namespace App\Livewire\Administrator\UserManagement\Role;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.administrator.user-management.role.index');
    }
}
