<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class GeneralSettingForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = null;

    #[Validate('nullable|string|max:500')]
    public ?string $keywords = null;

    #[Validate('nullable|file|mimes:ico,png,svg,jpg,jpeg,webp|max:512')]
    public $favicon = null;

    #[Validate('nullable|file|mimes:png,jpg,jpeg,webp,svg|max:2048')]
    public $logo = null;

    public ?string $favicon_path = null;

    public ?string $logo_path = null;
}
