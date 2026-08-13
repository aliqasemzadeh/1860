<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class SmsSettingForm extends Form
{
    #[Validate('required|string')]
    public string $token = '';

    #[Validate('required|string')]
    public string $gateway = '';
}
