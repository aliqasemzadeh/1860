<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute\Group;

use App\Models\Shop\AttributeGroup;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public AttributeGroup $group;

    public int $id;

    public string $name = '';

    public int $sort_order = 0;

    #[On('panel.shop.setting-management.attribute.group.edit.assign-data')]
    public function assignData($id): void
    {
        $this->group = AttributeGroup::findOrFail($id);
        $this->id = $this->group->id;
        $this->name = (string) $this->group->name;
        $this->sort_order = (int) $this->group->sort_order;
        Flux::modal('panel.shop.setting-management.attribute.group.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->group)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->group->fill($validated)->save();

        $this->dispatch('panel.shop.setting-management.attribute.group.index.render');
        Flux::modal('panel.shop.setting-management.attribute.group.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.attribute_group_updated'));
    }

    public function render()
    {
        return view('livewire.panel.shop.setting-management.attribute.group.edit');
    }
}
