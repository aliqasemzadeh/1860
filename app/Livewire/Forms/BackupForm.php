<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class BackupForm extends Form
{
    #[Validate('required|in:database,files,both')]
    public string $type = 'both';

    #[Validate('required|in:local,remote')]
    public string $destination = 'local';
}
