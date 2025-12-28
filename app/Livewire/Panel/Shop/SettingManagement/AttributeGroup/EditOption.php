<?php

namespace App\Livewire\Panel\Shop\SettingManagement\AttributeGroup;

use App\Models\Shop\AttributeOption;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class EditOption extends Component
{
    public AttributeOption $option;

    public int $id;

    public string $value = '';

    public string $label = '';

    public int $sort_order = 1;

    #[On('panel.shop.setting-management.attribute-group.attribute.option.edit.assign-data')]
    public function assignData($id): void
    {
        $this->option = AttributeOption::findOrFail($id);
        $this->id = $this->option->id;
        $this->value = (string) $this->option->value;
        $this->label = (string) $this->option->label;
        $this->sort_order = (int) $this->option->sort_order;
        Flux::modal('shop.setting-management.attribute-group.attribute.option.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->option)) {
            return;
        }

        $validated = $this->validate([
            'value' => ['required', 'string', 'max:255', Rule::unique('attribute_options', 'value')->where('attribute_id', $this->option->attribute_id)->ignore($this->option)],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $this->option->fill($validated)->save();

        $this->dispatch('panel.shop.setting-management.attribute-group.attribute.options.refresh');
        Flux::modal('shop.setting-management.attribute-group.attribute.option.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.attribute_option_updated'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.attribute-group.edit-option');
    }
}

