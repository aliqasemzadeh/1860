<?php

namespace App\Livewire\Panel\Shop\Shipping\Rate;

use App\Models\ShippingRate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

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
        $rate = ShippingRate::query()->find($id);
        if ($rate !== null) {
            $rate->delete();
        }
    }

    #[Computed]
    public function rates(): LengthAwarePaginator
    {
        return ShippingRate::query()
            ->with(['method', 'zone'])
            ->when($this->search, function ($query) {
                $query->whereHas('method', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhereHas('zone', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
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
        return view('livewire.panel.shop.shipping.rate.index');
    }
}
