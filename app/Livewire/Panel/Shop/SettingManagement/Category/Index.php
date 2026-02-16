<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Category;

use App\Models\Shop\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use \Livewire\WithPagination;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

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
        $category = Category::query()->find($id);
        if ($category !== null) {
            $category->delete();
        }
    }

    #[Computed]
    public function categories(): LengthAwarePaginator
    {
        return Category::query()
            ->where('main_category_id', 0)
            ->with(['children' => function ($query) {
                $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->with(['children' => function ($query) {
                    $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
                }]);
            }])
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    #[On('panel.shop.setting-management.category.index.render')]
    public function render()
    {
        return view('livewire.panel.shop.setting-management.category.index');
    }
}
