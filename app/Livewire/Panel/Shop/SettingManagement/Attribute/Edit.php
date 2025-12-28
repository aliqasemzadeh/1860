<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeGroup;
use App\Models\Shop\AttributeOption;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public Attribute $attribute;

    public int $id;

    public ?int $attribute_group_id = null;

    public string $key = '';

    public string $label = '';

    public string $type = 'text';

    public bool $is_required = false;

    public int $sort_order = 0;

    public array $options = [];

    public array $meta = [];

    #[On('panel.shop.setting-management.attribute.edit.assign-data')]
    public function assignData($id): void
    {
        $this->attribute = Attribute::with('options')->findOrFail($id);
        $this->id = $this->attribute->id;
        $this->attribute_group_id = $this->attribute->attribute_group_id;
        $this->key = (string) $this->attribute->key;
        $this->label = (string) $this->attribute->label;
        $this->type = (string) $this->attribute->type;
        $this->is_required = (bool) $this->attribute->is_required;
        $this->sort_order = (int) $this->attribute->sort_order;
        $this->meta = $this->attribute->meta ?? [];
        $this->options = $this->attribute->options->map(function ($option) {
            return [
                'id' => $option->id,
                'value' => $option->value,
                'label' => $option->label,
                'sort_order' => $option->sort_order,
            ];
        })->toArray();
        Flux::modal('panel.shop.setting-management.attribute.edit.modal')->show();
    }

    public function addOption(): void
    {
        $this->options[] = ['id' => null, 'value' => '', 'label' => '', 'sort_order' => count($this->options)];
    }

    public function removeOption(int $index): void
    {
        $option = $this->options[$index];
        if (isset($option['id'])) {
            AttributeOption::query()->where('id', $option['id'])->delete();
        }
        unset($this->options[$index]);
        $this->options = array_values($this->options);
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

        $this->attribute->fill([
            'attribute_group_id' => $validated['attribute_group_id'],
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'is_required' => $validated['is_required'],
            'sort_order' => $validated['sort_order'],
            'meta' => $validated['meta'] ?? [],
        ])->save();

        // Update or create options
        $existingOptionIds = [];
        foreach ($validated['options'] as $optionData) {
            if (isset($optionData['id'])) {
                // Update existing option
                $option = AttributeOption::find($optionData['id']);
                if ($option) {
                    $option->update([
                        'value' => $optionData['value'],
                        'label' => $optionData['label'],
                        'sort_order' => $optionData['sort_order'] ?? 0,
                    ]);
                    $existingOptionIds[] = $option->id;
                }
            } else {
                // Create new option
                $option = AttributeOption::create([
                    'attribute_id' => $this->attribute->id,
                    'value' => $optionData['value'],
                    'label' => $optionData['label'],
                    'sort_order' => $optionData['sort_order'] ?? 0,
                ]);
                $existingOptionIds[] = $option->id;
            }
        }

        // Delete options that were removed
        AttributeOption::query()
            ->where('attribute_id', $this->attribute->id)
            ->whereNotIn('id', $existingOptionIds)
            ->delete();

        $this->dispatch('panel.shop.setting-management.attribute.index.render');
        Flux::modal('panel.shop.setting-management.attribute.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.attribute_updated'));
    }

    public function render(): View
    {
        $groups = AttributeGroup::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('livewire.panel.shop.setting-management.attribute.edit', compact('groups'));
    }
}
