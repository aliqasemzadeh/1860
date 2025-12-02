<?php

namespace App\Livewire\Administrator\UserManagement\User;

use App\Models\User;
use Flux\Flux;
use Livewire\Component;

class Create extends Component
{
    public string $mobile = '';

    public function create()
    {
        $validated = $this->validate([
            'mobile' => ['required', 'string', 'lowercase', 'ir_mobile', 'max:255', 'unique:'.User::class],
        ]);

        User::firstOrCreate($validated);

        $this->dispatch('pg:eventRefresh-administrator.user-management.user.table');
        Flux::modal('administrator.user-management.user.create.modal')->close();
    }

    public function render()
    {
        $this->authorize('administrator_user_create');

        return view('livewire.administrator.user-management.user.create');
    }
}
