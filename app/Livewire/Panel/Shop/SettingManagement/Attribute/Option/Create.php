<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute\Option;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeOption;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public int $attributeId;

    public string $value = '';

    public string $label = '';

    public int $sort_order = 0;

    public function mount(int $id): void
    {
        $this->attributeId = $id;
    }

    public function create(): void
    {
        $validated = $this->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_options')->where(function ($query) {
                    return $query->where('attribute_id', $this->attributeId);
                }),
            ],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $validated['attribute_id'] = $this->attributeId;
        AttributeOption::create($validated);

        Flux::modal('panel.shop.setting-management.attribute.option.create.modal')->close();
        $this->dispatch('panel.shop.setting-management.attribute.option.index.refresh');
        Flux::toast(variant: 'success', text: __('general.attribute_option_created'));
        $this->reset(['value', 'label', 'sort_order']);
    }

    public function render(): View
    {
        $attribute = Attribute::findOrFail($this->attributeId);

        return view('livewire.panel.shop.setting-management.attribute.option.create', [
            'attribute' => $attribute,
        ]);
    }
}
