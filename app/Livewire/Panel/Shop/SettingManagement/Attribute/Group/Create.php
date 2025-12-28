<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute\Group;

use App\Models\Shop\AttributeGroup;
use Flux\Flux;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public int $sort_order = 0;

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        AttributeGroup::create($validated);

        Flux::modal('panel.shop.setting-management.attribute.group.create.modal')->close();
        $this->dispatch('panel.shop.setting-management.attribute.group.index.render');
        Flux::toast(variant: 'success', text: __('app.attribute_group_created'));
        $this->reset(['name', 'sort_order']);
    }

    public function render()
    {
        return view('livewire.panel.shop.setting-management.attribute.group.create');
    }
}
