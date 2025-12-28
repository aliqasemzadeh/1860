<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Unit;

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

        $unit = Unit::create($validated);

        Flux::modal('panel.shop.setting-management.unit.create.modal')->close();
        $this->dispatch('shop.setting-management.unit.index.render');
        $this->dispatch('shop.product.unit.refresh', ['id' => $unit->id]);
        Flux::toast(variant: 'success', text: __('app.unit_created', ['name' => $validated['name']]));
        $this->reset(['name']);
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.unit.create');
    }
}
