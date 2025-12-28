<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute;

use App\Models\Shop\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'sort_order';

    public string $sortDirection = 'asc';

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
            \Flux\Flux::toast(variant: 'success', text: __('app.attribute_deleted'));
            $this->dispatch('panel.shop.setting-management.attribute.index.render');
        }
    }

    #[Computed]
    public function attributes(): LengthAwarePaginator
    {
        return Attribute::query()
            ->with(['attributeGroup'])
            ->withCount('options')
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    #[On('panel.shop.setting-management.attribute.index.render')]
    public function render()
    {
        return view('livewire.panel.shop.setting-management.attribute.index');
    }
}