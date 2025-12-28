<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Color;

use App\Models\Shop\Color;
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
        $color = Color::query()->find($id);
        if ($color !== null) {
            $color->delete();
        }
    }

    #[Computed]
    public function colors(): LengthAwarePaginator
    {
        return Color::query()
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    #[On('shop.setting-management.color.index.render')]
    public function render()
    {
        return view('livewire.panel.shop.setting-management.color.index');
    }
}
