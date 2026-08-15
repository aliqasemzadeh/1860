<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute;

use App\Models\Shop\Attribute as AttributeModel;
use App\Models\Shop\AttributeGroup;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public AttributeModel $attribute;

    public int $id;

    public ?int $attribute_group_id = null;

    public string $key = '';

    public string $label = '';

    public string $type = 'text';

    public bool $is_required = false;

    public int $sort_order = 0;

    #[On('panel.shop.setting-management.attribute.edit.assign-data')]
    public function assignData($id): void
    {
        $this->attribute = AttributeModel::findOrFail($id);
        $this->id = $this->attribute->id;
        $this->attribute_group_id = $this->attribute->attribute_group_id;
        $this->key = (string) $this->attribute->key;
        $this->label = (string) $this->attribute->label;
        $this->type = (string) $this->attribute->type;
        $this->is_required = $this->attribute->is_required;
        $this->sort_order = $this->attribute->sort_order;
        Flux::modal('panel.shop.setting-management.attribute.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->attribute)) {
            return;
        }

        $validated = $this->validate([
            'attribute_group_id' => ['nullable', 'exists:attribute_groups,id'],
            'key' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('attributes', 'key')->ignore($this->attribute)],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:text,textarea,number,boolean,date,select,multiselect'],
            'is_required' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->attribute->fill($validated)->save();

        $this->dispatch('panel.shop.setting-management.attribute.index.render');
        Flux::modal('panel.shop.setting-management.attribute.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('general.attribute_updated'));
    }

    public function render(): View
    {
        $attributeGroups = AttributeGroup::query()->orderBy('sort_order')->get();

        return view('livewire.panel.shop.setting-management.attribute.edit', [
            'attributeGroups' => $attributeGroups,
        ]);
    }
}
