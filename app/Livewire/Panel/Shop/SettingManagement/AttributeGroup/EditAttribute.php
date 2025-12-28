<?php

namespace App\Livewire\Panel\Shop\SettingManagement\AttributeGroup;

use App\Models\Shop\Attribute;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class EditAttribute extends Component
{
    public Attribute $attribute;

    public int $id;

    public string $key = '';

    public string $label = '';

    public string $type = 'text';

    public bool $is_required = false;

    public int $sort_order = 1;

    public array $options = [];

    public array $meta = [];

    #[On('panel.shop.setting-management.attribute-group.attribute.edit.assign-data')]
    public function assignData($id): void
    {
        $this->attribute = Attribute::with('options')->findOrFail($id);
        $this->id = $this->attribute->id;
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
        Flux::modal('shop.setting-management.attribute-group.attribute.edit.modal')->show();
    }

    public function addOption(): void
    {
        $this->options[] = ['id' => null, 'value' => '', 'label' => '', 'sort_order' => count($this->options) + 1];
    }

    public function removeOption(int $index): void
    {
        $option = $this->options[$index];
        if (isset($option['id'])) {
            \App\Models\Shop\AttributeOption::find($option['id'])?->delete();
        }
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

    public function edit(): void
    {
        if (! isset($this->attribute)) {
            return;
        }

        $rules = [
            'key' => ['required', 'string', 'max:255', Rule::unique('attributes', 'key')->ignore($this->attribute)],
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

        $this->attribute->fill([
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'is_required' => $validated['is_required'] ?? false,
            'sort_order' => $validated['sort_order'],
            'meta' => $this->meta ?: null,
        ])->save();

        // Update options
        if (in_array($this->type, ['select', 'multiselect'])) {
            // Delete removed options
            $existingIds = collect($this->options)->pluck('id')->filter()->toArray();
            $this->attribute->options()->whereNotIn('id', $existingIds)->delete();

            // Update or create options
            foreach ($this->options as $optionData) {
                if (isset($optionData['id'])) {
                    $this->attribute->options()->where('id', $optionData['id'])->update([
                        'value' => $optionData['value'],
                        'label' => $optionData['label'],
                        'sort_order' => $optionData['sort_order'] ?? 1,
                    ]);
                } else {
                    $this->attribute->options()->create([
                        'value' => $optionData['value'],
                        'label' => $optionData['label'],
                        'sort_order' => $optionData['sort_order'] ?? 1,
                    ]);
                }
            }
        } else {
            // Remove all options if type changed
            $this->attribute->options()->delete();
        }

        $this->dispatch('panel.shop.setting-management.attribute-group.attributes.refresh');
        Flux::modal('shop.setting-management.attribute-group.attribute.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.attribute_updated'));
        $this->reset(['id', 'key', 'label', 'type', 'is_required', 'sort_order', 'options', 'meta']);
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.attribute-group.edit-attribute');
    }
}

