<?php

namespace App\Livewire\Panel\User\Order;

use App\Models\Shop\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $search = '';

    public string $status = '';

    public string $paymentStatus = '';

    public function mount(): void
    {
        $this->authorize('user_order_index');
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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', auth()->id())
            ->with(['items:id,order_id,name,quantity,total_amount', 'shippingMethod:id,name'])
            ->when($this->search, fn ($q) => $q->where('order_number', 'like', '%'.$this->search.'%'))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->paymentStatus === 'unpaid', fn ($q) => $q->whereNull('paid_at'))
            ->when($this->paymentStatus === 'paid', fn ($q) => $q->whereNotNull('paid_at'))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Layout('layouts.panels.user')]
    #[On('panel.user.order.index.render')]
    public function render()
    {
        $this->authorize('user_order_index');

        return view('livewire.panel.user.order.index');
    }
}
