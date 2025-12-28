<?php

namespace App\Livewire\Panel\Shop\SettingManagement\AttributeGroup;

use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeOption;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Options extends Component
{
    use \Livewire\WithPagination;

    public Attribute $attribute;

    public int $attributeId;

    public string $sortBy = 'sort_order';

    public string $sortDirection = 'asc';

    public function mount(int $attributeId): void
    {
        $this->attributeId = $attributeId;
        $this->loadAttribute();
    }

    #[On('panel.shop.setting-management.attribute-group.attribute.options.refresh')]
    public function loadAttribute(): void
    {
        $this->attribute = Attribute::with('attributeGroup')->findOrFail($this->attributeId);
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
            Flux::toast(variant: 'success', text: __('app.attribute_option_deleted'));
            $this->loadAttribute();
        }
    }

    #[Computed]
    public function options(): LengthAwarePaginator
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
    public function render()
    {
        return view('livewire.panel.shop.setting-management.attribute-group.options');
    }
}

