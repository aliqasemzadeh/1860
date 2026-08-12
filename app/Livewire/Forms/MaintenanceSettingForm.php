<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class MaintenanceSettingForm extends Form
{
    #[Validate('required|string|max:500')]
    public string $message = '';

    #[Validate('nullable|string|min:8|max:64|regex:/^[A-Za-z0-9\-_]+$/')]
    public ?string $secret = null;

    #[Validate('nullable|integer|min:0|max:86400')]
    public ?int $retry = 60;

    #[Validate('nullable|integer|min:0|max:3600')]
    public ?int $refresh = 30;
}
