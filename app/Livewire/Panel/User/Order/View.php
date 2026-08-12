<?php

namespace App\Livewire\Panel\User\Order;

use App\Models\Shop\Order;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class View extends Component
{
    public ?Order $order = null;

    #[On('panel.user.order.view.assign-data')]
    public function assignData(int $id): void
    {
        $this->authorize('user_order_view');

        $this->order = Order::query()
            ->with(['items.color', 'items.warranty', 'shippingMethod'])
            ->where('user_id', auth()->id())
            ->find($id);

        abort_if($this->order === null, 404);

        Flux::modal('panel.user.order.view.modal')->show();
    }

    public function render()
    {
        return view('livewire.panel.user.order.view');
    }
}
