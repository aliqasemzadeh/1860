<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class BaleSettingForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $bot_username = '';

    #[Validate('required|string')]
    public string $bot_token = '';

    #[Validate('required|string|max:64')]
    public string $chat_id = '';
}
