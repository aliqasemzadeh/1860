<?php

namespace App\Livewire\Main\Order;

use App\Models\Shop\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    #[Computed]
    public function orders()
    {
        if (! auth()->check()) {
            return collect();
        }

        return Order::query()
            ->where('user_id', auth()->id())
            ->with(['items', 'shippingMethod'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.main.order.index');
    }
}
