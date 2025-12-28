<?php

namespace App\Livewire\Panel\Shop\SettingManagement\AttributeGroup;

use App\Models\Shop\AttributeGroup as AttributeGroupModel;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public AttributeGroupModel $attributeGroup;

    public int $id;

    public string $name = '';

    public int $sort_order = 0;

    #[On('panel.shop.setting-management.attribute-group.edit.assign-data')]
    public function assignData($id): void
    {
        $this->attributeGroup = AttributeGroupModel::findOrFail($id);
        $this->id = $this->attributeGroup->id;
        $this->name = (string) $this->attributeGroup->name;
        $this->sort_order = $this->attributeGroup->sort_order;
        Flux::modal('panel.shop.setting-management.attribute-group.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->attributeGroup)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->attributeGroup->fill($validated)->save();

        $this->dispatch('panel.shop.setting-management.attribute-group.index.render');
        Flux::modal('panel.shop.setting-management.attribute-group.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.attribute_group_updated'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.attribute-group.edit');
    }
}

