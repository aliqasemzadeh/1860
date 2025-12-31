<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Category;

use App\Models\Shop\Attribute;
use App\Models\Shop\Category;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Attributes extends Component
{
    public Category $category;

    public int $categoryId;

    public array $selectedAttributes = [];

    public function mount(int $id): void
    {
        $this->categoryId = $id;
        $this->loadCategory();
    }

    #[On('panel.shop.setting-management.category.attributes.refresh')]
    public function loadCategory(): void
    {
        $this->category = Category::with('attributes')->findOrFail($this->categoryId);
        $this->selectedAttributes = $this->category->attributes->pluck('id')->toArray();
    }

    public function save(): void
    {
        $this->category = Category::findOrFail($this->categoryId);

        // Get all attributes with their pivot data
        $attributesToSync = [];
        foreach ($this->selectedAttributes as $attributeId) {
            $pivot = $this->category->attributes()->where('attributes.id', $attributeId)->first()?->pivot;
            $attributesToSync[$attributeId] = [
                'is_required' => $pivot?->is_required ?? false,
                'sort_order' => $pivot?->sort_order ?? 0,
            ];
        }

        $this->category->attributes()->sync($attributesToSync);

        Flux::toast(variant: 'success', text: __('app.category_attributes_updated'));
        $this->loadCategory();
    }

    public function toggleAttribute(int $attributeId): void
    {
        if (in_array($attributeId, $this->selectedAttributes)) {
            $this->selectedAttributes = array_values(array_diff($this->selectedAttributes, [$attributeId]));
        } else {
            $this->selectedAttributes[] = $attributeId;
        }
    }

    #[Layout('layouts.panels.shop')]
    public function render()
    {
        $allAttributes = Attribute::query()
            ->with(['attributeGroup'])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('attributeGroup.name');

        return view('livewire.panel.shop.setting-management.category.attributes', [
            'allAttributes' => $allAttributes,
        ]);
    }
}










