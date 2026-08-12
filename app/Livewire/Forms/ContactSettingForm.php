<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class ContactSettingForm extends Form
{
    #[Validate('nullable|string|max:1000')]
    public ?string $address = null;

    #[Validate('nullable|string|max:20')]
    public ?string $mobile = null;

    #[Validate('nullable|string|max:20')]
    public ?string $phone = null;

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;
}
