<?php

namespace App\Livewire\Panel\Shop\SettingManagement\AttributeGroup;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeGroup;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateAttribute extends Component
{
    public int $attributeGroupId;

    public string $key = '';

    public string $label = '';

    public string $type = 'text';

    public bool $is_required = false;

    public int $sort_order = 1;

    public array $options = [];

    public array $meta = [];

    #[On('panel.shop.setting-management.attribute-group.attribute.create.set-group')]
    public function setGroup(int $groupId): void
    {
        $this->attributeGroupId = $groupId;
        $this->reset(['key', 'label', 'type', 'is_required', 'sort_order', 'options', 'meta']);
        Flux::modal('shop.setting-management.attribute-group.attribute.create.modal')->show();
    }

    public function addOption(): void
    {
        $this->options[] = ['value' => '', 'label' => '', 'sort_order' => count($this->options) + 1];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function updatedType(): void
    {
        // Clear options if type is not select or multiselect
        if (!in_array($this->type, ['select', 'multiselect'])) {
            $this->options = [];
        }
    }

    public function create(): void
    {
        $rules = [
            'attributeGroupId' => ['required', 'exists:attribute_groups,id'],
            'key' => ['required', 'string', 'max:255', 'unique:attributes,key'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text,textarea,number,boolean,date,select,multiselect'],
            'is_required' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ];

        if (in_array($this->type, ['select', 'multiselect'])) {
            $rules['options'] = ['required', 'array', 'min:1'];
            $rules['options.*.value'] = ['required', 'string'];
            $rules['options.*.label'] = ['required', 'string'];
        }

        $validated = $this->validate($rules);

        $attribute = Attribute::create([
            'attribute_group_id' => $validated['attributeGroupId'],
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'is_required' => $validated['is_required'] ?? false,
            'sort_order' => $validated['sort_order'],
            'meta' => $this->meta ?: null,
        ]);

        // Create options if needed
        if (in_array($this->type, ['select', 'multiselect']) && !empty($this->options)) {
            foreach ($this->options as $option) {
                $attribute->options()->create([
                    'value' => $option['value'],
                    'label' => $option['label'],
                    'sort_order' => $option['sort_order'] ?? 1,
                ]);
            }
        }

        Flux::modal('shop.setting-management.attribute-group.attribute.create.modal')->close();
        $this->dispatch('panel.shop.setting-management.attribute-group.attributes.refresh');
        Flux::toast(variant: 'success', text: __('app.attribute_created'));
        $this->reset(['key', 'label', 'type', 'is_required', 'sort_order', 'options', 'meta', 'attributeGroupId']);
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.attribute-group.create-attribute');
    }
}

