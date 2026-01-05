<?php

namespace App\Livewire\Panel\Shop\Order;

use App\Models\Shop\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $search = '';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->with(['user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', function ($uq) {
                            $uq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
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
        return view('livewire.panel.shop.order.index');
    }
}
