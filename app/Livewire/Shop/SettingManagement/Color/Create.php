<?php

namespace App\Livewire\Shop\SettingManagement\Color;

use App\Models\Shop\Color;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    public string $hex = '#14b8a6';

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('colors', 'slug')],
            'slug_fa' => ['required', 'string', 'max:255', Rule::unique('colors', 'slug_fa')],
            'hex' => ['required', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ]);

        Color::create($validated);

        Flux::modal('shop.color.create.modal')->close();
        $this->dispatch('shop.color.index.render');
        $this->reset(['name', 'slug', 'slug_fa', 'hex']);
    }

    public function render(): View
    {
        return view('livewire.shop.color.create');
    }
}
