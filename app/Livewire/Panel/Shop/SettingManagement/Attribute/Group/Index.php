<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Attribute\Group;

use App\Models\Shop\AttributeGroup;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use \Livewire\WithPagination;

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
        $group = AttributeGroup::query()->find($id);
        if ($group !== null) {
            $group->delete();
            Flux::toast(variant: 'success', text: __('app.attribute_group_deleted'));
        }
    }

    #[Computed]
    public function groups(): LengthAwarePaginator
    {
        return AttributeGroup::query()
            ->withCount('attributes')
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    #[On('panel.shop.setting-management.attribute.group.index.render')]
    public function render()
    {
        return view('livewire.panel.shop.setting-management.attribute.group.index');
    }
}
