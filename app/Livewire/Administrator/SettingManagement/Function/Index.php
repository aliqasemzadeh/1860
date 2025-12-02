<?php

namespace App\Livewire\Administrator\SettingManagement\Function;

use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public function updatePermissions()
    {
        Artisan::call('system:administrator:create-permissions-command');
        Flux::toast('success', __('app.permissions_updated'));
    }

    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.administrator.setting-management.function.index');
    }
}
