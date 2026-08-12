<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Color;

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

    public function updatedName(string $value): void
    {
        $this->slug = \Illuminate\Support\Str::slug($value);
        $this->slug_fa = slug_fa($value);
    }

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('colors', 'slug')],
            'slug_fa' => ['required', 'string', 'max:255', Rule::unique('colors', 'slug_fa')],
            'hex' => ['required', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ]);

        Color::create($validated);

        Flux::modal('panel.shop.setting-management.color.create.modal')->close();
        $this->dispatch('panel.shop.setting-management.color.index.render');
        $this->reset(['name', 'slug', 'slug_fa', 'hex']);
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.color.create');
    }
}
