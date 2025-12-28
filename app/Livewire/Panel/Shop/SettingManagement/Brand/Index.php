<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Brand;

use App\Models\Shop\Brand;
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
        $brand = Brand::query()->find($id);
        if ($brand !== null) {
            $brand->delete();
        }
    }

    #[Computed]
    public function brands(): LengthAwarePaginator
    {
        return Brand::query()
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    #[On('shop.setting-management.brand.index.render')]
    public function render()
    {
        return view('livewire.panel.shop.setting-management.brand.index');
    }
}
