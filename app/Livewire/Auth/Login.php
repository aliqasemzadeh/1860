<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Login extends Component
{
    public $mobile = '';
    public $otp = '';

    public function request()
    {

    }
    public function verify()
    {

    }

    public function resend()
    {

    }

    #[Layout('layouts.auth')]
    public function render()
    {
        return view('livewire.auth.login');
    }
}
