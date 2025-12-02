<?php

namespace App\Livewire\Auth;

use Flux\Flux;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Logout extends Component
{
    public function confirmLogout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Flux::toast(variant: 'success', text: __("1860.logout.success"));

        $this->redirectIntended(route('login'));
    }

    public function cancel()
    {
        $this->redirectIntended(route('home'));
    }

    #[Layout('layouts.auth')]
    public function render()
    {
        return view('livewire.auth.logout');
    }
}
