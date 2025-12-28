<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute\Option;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeOption as AttributeOptionModel;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public AttributeOptionModel $attributeOption;

    public int $id;

    public int $attributeId;

    public string $value = '';

    public string $label = '';

    public int $sort_order = 0;

    #[On('panel.shop.setting-management.attribute.option.edit.assign-data')]
    public function assignData($id): void
    {
        $this->attributeOption = AttributeOptionModel::findOrFail($id);
        $this->id = $this->attributeOption->id;
        $this->attributeId = $this->attributeOption->attribute_id;
        $this->value = (string) $this->attributeOption->value;
        $this->label = (string) $this->attributeOption->label;
        $this->sort_order = $this->attributeOption->sort_order;
        Flux::modal('panel.shop.setting-management.attribute.option.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->attributeOption)) {
            return;
        }

        $validated = $this->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_options')->where(function ($query) {
                    return $query->where('attribute_id', $this->attributeId);
                })->ignore($this->attributeOption),
            ],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->attributeOption->fill($validated)->save();

        $this->dispatch('panel.shop.setting-management.attribute.option.index.refresh');
        Flux::modal('panel.shop.setting-management.attribute.option.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.attribute_option_updated'));
    }

    public function render(): View
    {
        $attribute = Attribute::findOrFail($this->attributeId ?? $this->attributeOption->attribute_id ?? 0);

        return view('livewire.panel.shop.setting-management.attribute.option.edit', [
            'attribute' => $attribute,
        ]);
    }
}

