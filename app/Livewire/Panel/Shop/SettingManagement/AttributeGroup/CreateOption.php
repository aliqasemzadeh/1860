<?php

namespace App\Livewire\Panel\Shop\SettingManagement\AttributeGroup;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeOption;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateOption extends Component
{
    public int $attributeId;

    public string $value = '';

    public string $label = '';

    public int $sort_order = 1;

    #[On('panel.shop.setting-management.attribute-group.attribute.option.create.set-attribute')]
    public function setAttribute(int $id): void
    {
        $this->attributeId = $id;
        $this->reset(['value', 'label', 'sort_order']);
        
        // Set next sort_order
        $lastOption = AttributeOption::where('attribute_id', $id)->orderBy('sort_order', 'desc')->first();
        $this->sort_order = $lastOption ? $lastOption->sort_order + 1 : 1;
        
        Flux::modal('shop.setting-management.attribute-group.attribute.option.create.modal')->show();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'attributeId' => ['required', 'exists:attributes,id'],
            'value' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        // Check for duplicate value in same attribute
        $exists = AttributeOption::where('attribute_id', $validated['attributeId'])
            ->where('value', $validated['value'])
            ->exists();

        if ($exists) {
            $this->addError('value', __('app.attribute_option_value_exists'));
            return;
        }

        AttributeOption::create([
            'attribute_id' => $validated['attributeId'],
            'value' => $validated['value'],
            'label' => $validated['label'],
            'sort_order' => $validated['sort_order'],
        ]);

        Flux::modal('shop.setting-management.attribute-group.attribute.option.create.modal')->close();
        $this->dispatch('panel.shop.setting-management.attribute-group.attribute.options.refresh');
        Flux::toast(variant: 'success', text: __('app.attribute_option_created'));
        
        // Keep attributeId, just reset form fields
        $this->reset(['value', 'label']);
        
        // Set next sort_order for potential next option
        $lastOption = AttributeOption::where('attribute_id', $this->attributeId)->orderBy('sort_order', 'desc')->first();
        $this->sort_order = $lastOption ? $lastOption->sort_order + 1 : 1;
    }

    public function render(): View
    {
        return view('livewire.panel.shop.setting-management.attribute-group.create-option');
    }
}

