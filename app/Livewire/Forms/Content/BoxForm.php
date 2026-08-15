<?php

namespace App\Livewire\Forms\Content;

use App\Models\Content\Box;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BoxForm extends Form
{
    public ?Box $box = null;

    #[Validate('required|string|min:3')]
    public $title_fa = '';

    #[Validate('required|string|min:3')]
    public $title_en = '';

    #[Validate('nullable|array')]
    public $color_theme = [
        'bg' => '#ffffff',
        'text' => '#000000',
        'accent' => '#3b82f6',
    ];

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('nullable|image|max:2048')]
    public $image;

    public function setModel(Box $box)
    {
        $this->box = $box;
        $this->title_fa = $box->title_fa;
        $this->title_en = $box->title_en;
        $this->color_theme = $box->color_theme ?? [
            'bg' => '#ffffff',
            'text' => '#000000',
            'accent' => '#3b82f6',
        ];
        $this->is_active = $box->is_active;
    }

    public function store()
    {
        $this->validate();

        $box = Box::create([
            'title_fa' => $this->title_fa,
            'title_en' => $this->title_en,
            'color_theme' => $this->color_theme,
            'is_active' => $this->is_active,
        ]);

        if ($this->image) {
            $box->addMedia($this->image)->toMediaCollection('box_images');
        }

        $this->reset(['title_fa', 'title_en', 'color_theme', 'is_active', 'image']);
    }

    public function update()
    {
        $this->validate();

        $this->box->update([
            'title_fa' => $this->title_fa,
            'title_en' => $this->title_en,
            'color_theme' => $this->color_theme,
            'is_active' => $this->is_active,
        ]);

        if ($this->image) {
            $this->box->clearMediaCollection('box_images');
            $this->box->addMedia($this->image)->toMediaCollection('box_images');
        }
    }
}
