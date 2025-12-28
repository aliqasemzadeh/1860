<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeGroup;
use App\Models\Shop\AttributeOption;
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

    public array $options = [];

    public array $meta = [];

    public function addOption(): void
    {
        $this->options[] = ['value' => '', 'label' => '', 'sort_order' => count($this->options)];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function create(): void
    {
        $validated = $this->validate([
            'attribute_group_id' => ['nullable', 'exists:attribute_groups,id'],
            'key' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:attributes,key'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:text,number,select,multiselect,boolean,textarea,date'],
            'is_required' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'options' => ['array'],
            'options.*.value' => ['required_with:options', 'string', 'max:255'],
            'options.*.label' => ['required_with:options', 'string', 'max:255'],
            'options.*.sort_order' => ['integer', 'min:0'],
            'meta' => ['array'],
        ]);

        // Validate options for select/multiselect types
        if (in_array($validated['type'], ['select', 'multiselect']) && empty($validated['options'])) {
            $this->addError('options', __('app.attribute_options_required_for_select'));

            return;
        }

        $attribute = Attribute::create([
            'attribute_group_id' => $validated['attribute_group_id'],
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'is_required' => $validated['is_required'],
            'sort_order' => $validated['sort_order'],
            'meta' => $validated['meta'] ?? [],
        ]);

        // Create options if provided
        if (! empty($validated['options'])) {
            foreach ($validated['options'] as $optionData) {
                AttributeOption::create([
                    'attribute_id' => $attribute->id,
                    'value' => $optionData['value'],
                    'label' => $optionData['label'],
                    'sort_order' => $optionData['sort_order'] ?? 0,
                ]);
            }
        }

        Flux::modal('panel.shop.setting-management.attribute.create.modal')->close();
        $this->dispatch('panel.shop.setting-management.attribute.index.render');
        Flux::toast(variant: 'success', text: __('app.attribute_created'));
        $this->reset(['attribute_group_id', 'key', 'label', 'type', 'is_required', 'sort_order', 'options', 'meta']);
    }

    public function render(): View
    {
        $groups = AttributeGroup::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('livewire.panel.shop.setting-management.attribute.create', compact('groups'));
    }
}
