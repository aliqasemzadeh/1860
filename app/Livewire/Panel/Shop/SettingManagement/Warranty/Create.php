<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Warranty;

use App\Models\Shop\Warranty;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:warranties,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:warranties,slug_fa'],
        ]);

        Warranty::create($validated);

        Flux::modal('shop.setting-management.warranty.create.modal')->close();
        $this->dispatch('shop.setting-management.warranty.index.render');
        $this->reset(['name', 'slug', 'slug_fa']);
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.warranty.create');
    }
}
