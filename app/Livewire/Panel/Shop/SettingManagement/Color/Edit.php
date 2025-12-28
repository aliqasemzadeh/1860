<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Color;

use App\Models\Shop\Color;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public Color $color;

    public int $id;

    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    public string $hex = '#14b8a6';

    #[On('shop.setting-management.color.edit.assign-data')]
    public function assignData($id): void
    {
        $this->color = Color::findOrFail($id);
        $this->id = $this->color->id;
        $this->name = (string) $this->color->name;
        $this->slug = (string) $this->color->slug;
        $this->slug_fa = (string) $this->color->slug_fa;
        $this->hex = (string) $this->color->hex;
        Flux::modal('shop.setting-management.color.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->color)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('colors', 'slug')->ignore($this->color)],
            'slug_fa' => ['required', 'string', 'max:255', Rule::unique('colors', 'slug_fa')->ignore($this->color)],
            'hex' => ['required', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ]);

        $this->color->fill($validated)->save();

        $this->dispatch('shop.setting-management.color.index.render');
        Flux::modal('shop.setting-management.color.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.color.edit');
    }
}
