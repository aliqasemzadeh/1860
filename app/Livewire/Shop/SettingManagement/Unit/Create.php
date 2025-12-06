<?php

namespace App\Livewire\Shop\SettingManagement\Unit;

use App\Models\Shop\Unit;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
        ]);

        Unit::create($validated);

        Flux::modal('shop.setting-management.unit.create.modal')->close();
        $this->dispatch('shop.setting-management.unit.index.render');
        $this->reset(['name']);
    }

    public function render(): View
    {
        return view('livewire.shop.setting-management.unit.create');
    }
}
