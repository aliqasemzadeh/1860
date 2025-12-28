<?php

namespace App\Livewire\Panel\Shop\SettingManagement\AttributeGroup;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeGroup;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Attributes extends Component
{
    use \Livewire\WithPagination;

    public AttributeGroup $attributeGroup;

    public int $attributeGroupId;

    public string $sortBy = 'sort_order';

    public string $sortDirection = 'asc';

    public function mount(int $attributeGroupId): void
    {
        $this->attributeGroupId = $attributeGroupId;
        $this->loadAttributeGroup();
    }

    #[On('panel.shop.setting-management.attribute-group.attributes.refresh')]
    public function loadAttributeGroup(): void
    {
        $this->attributeGroup = AttributeGroup::findOrFail($this->attributeGroupId);
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $attribute = Attribute::query()->find($id);
        if ($attribute !== null) {
            $attribute->delete();
            Flux::toast(variant: 'success', text: __('app.attribute_deleted'));
            $this->loadAttributeGroup();
            $this->resetPage();
        }
    }

    #[Computed]
    public function attributes(): LengthAwarePaginator
    {
        return Attribute::query()
            ->where('attribute_group_id', $this->attributeGroupId)
            ->withCount('options')
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    public function render()
    {
        return view('livewire.panel.shop.setting-management.attribute-group.attributes');
    }
}
