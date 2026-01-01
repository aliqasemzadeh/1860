<?php

namespace App\Livewire\Panel\Shop\Shipping\Rate;

use App\Models\Shop\ShippingRate;
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
                $search = '%' . $this->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->whereHas('method', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', $search);
                    })
                    ->orWhereHas('zone', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', $search);
                    })
                    ->orWhere('rate_type', 'like', $search)
                    ->orWhere('amount', 'like', $search)
                    ->orWhere('estimated_days', 'like', $search)
                    ->orWhere('id', 'like', $search)
                    ->orWhere('min_weight', 'like', $search)
                    ->orWhere('max_weight', 'like', $search)
                    ->orWhere('min_price', 'like', $search)
                    ->orWhere('max_price', 'like', $search);
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
