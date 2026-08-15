<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeGroup;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public ?int $attribute_group_id = null;

    public string $key = '';

    public string $label = '';

    public string $type = 'text';

    public bool $is_required = false;

    public int $sort_order = 0;

    public function create(): void
    {
        $validated = $this->validate([
            'attribute_group_id' => ['nullable', 'exists:attribute_groups,id'],
            'key' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:attributes,key'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:text,textarea,number,boolean,date,select,multiselect'],
            'is_required' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        Attribute::create($validated);

        Flux::modal('panel.shop.setting-management.attribute.create.modal')->close();
        $this->dispatch('panel.shop.setting-management.attribute.index.render');
        Flux::toast(variant: 'success', text: __('general.attribute_created'));
        $this->reset(['attribute_group_id', 'key', 'label', 'type', 'is_required', 'sort_order']);
    }

    public function render(): View
    {
        $attributeGroups = AttributeGroup::query()->orderBy('sort_order')->get();

        return view('livewire.panel.shop.setting-management.attribute.create', [
            'attributeGroups' => $attributeGroups,
        ]);
    }
}
