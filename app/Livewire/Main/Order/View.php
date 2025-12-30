<?php

namespace App\Livewire\Main\Order;

use App\Models\Shop\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class View extends Component
{
    public $orderId;

    public function mount($id)
    {
        $this->orderId = $id;
    }

    #[Computed]
    public function order()
    {
        if (! auth()->check()) {
            return null;
        }

        return Order::query()
            ->where('id', $this->orderId)
            ->where('user_id', auth()->id())
            ->with(['items.color', 'items.warranty', 'shippingMethod', 'shippingZone', 'user'])
            ->first();
    }

    public function render()
    {
        if (! $this->order) {
            abort(404);
        }

        return view('livewire.main.order.view');
    }
}
