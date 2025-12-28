<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Unit;

use App\Models\Shop\Unit as UnitModel;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public UnitModel $unit;

    public int $id;

    public string $name = '';

    #[On('panel.shop.setting-management.unit.edit.assign-data')]
    public function assignData($id): void
    {
        $this->unit = UnitModel::findOrFail($id);
        $this->id = $this->unit->id;
        $this->name = (string) $this->unit->name;
        Flux::modal('shop.setting-management.unit.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->unit)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units', 'name')->ignore($this->unit)],
        ]);

        $this->unit->fill($validated)->save();

        $this->dispatch('shop.setting-management.unit.index.render');
        Flux::modal('shop.setting-management.unit.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.unit.edit');
    }
}
