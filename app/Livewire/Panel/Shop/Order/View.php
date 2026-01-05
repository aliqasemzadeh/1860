<?php

namespace App\Livewire\Panel\Shop\Order;

use App\Models\Shop\Order;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class View extends Component
{
    public ?Order $order = null;

    #[On('panel.shop.order.view.assign-data')]
    public function assignData(int $id): void
    {
        $this->order = Order::query()
            ->with(['items', 'user', 'shippingMethod', 'shippingZone'])
            ->find($id);

        if ($this->order) {
            Flux::modal('panel.shop.order.view.modal')->show();
        }
    }

    public function render()
    {
        return view('livewire.panel.shop.order.view');
    }
}
