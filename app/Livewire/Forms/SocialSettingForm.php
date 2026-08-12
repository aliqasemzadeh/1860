<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class SocialSettingForm extends Form
{
    /** @var array<string, string|null> */
    public array $links = [];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'links' => ['array'],
            'links.*' => ['nullable', 'url', 'max:255'],
        ];
    }
}
