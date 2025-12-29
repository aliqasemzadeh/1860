<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute\Option;

use App\Models\Shop\Attribute as AttributeModel;
use App\Models\Shop\AttributeOption;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public AttributeModel $attribute;

    public int $attributeId;

    public string $sortBy = 'sort_order';

    public string $sortDirection = 'asc';

    public function mount(int $attributeId): void
    {
        $this->attributeId = $attributeId;
        $this->attribute = AttributeModel::find($attributeId);
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
        $option = AttributeOption::query()->find($id);
        if ($option !== null) {
            $option->delete();
            \Flux\Flux::toast(variant: 'success', text: __('app.attribute_option_deleted'));
            $this->dispatch('panel.shop.setting-management.attribute.option.index.refresh');
        }
    }

    #[Computed]
    public function optionsList(): LengthAwarePaginator
    {
        return AttributeOption::query()
            ->where('attribute_id', $this->attributeId)
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    #[On('panel.shop.setting-management.attribute.option.index.refresh')]
    public function render()
    {
        return view('livewire.panel.shop.setting-management.attribute.option.index');
    }
}

