<?php

namespace App\Livewire\Panel\Shop\Order;

use App\Enums\OrderStatusEnum;
use App\Models\Shop\Order;
use App\Services\Shop\OrderStatusService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Ship extends Component
{
    public ?Order $order = null;

    public string $trackingCode = '';

    #[On('panel.shop.order.ship.assign-data')]
    public function assignData(int $id): void
    {
        $this->authorize('shop_order_ship');

        $this->order = Order::query()->find($id);

        if (! $this->order) {
            return;
        }

        if ($this->order->status !== OrderStatusEnum::Processing->value || $this->order->paid_at === null) {
            Flux::toast(variant: 'danger', text: __('general.order_cannot_be_shipped'));

            return;
        }

        $this->trackingCode = (string) ($this->order->tracking_code ?? '');
        $this->resetValidation();

        Flux::modal('panel.shop.order.ship.modal')->show();
    }

    public function ship(OrderStatusService $orderStatusService): void
    {
        $this->authorize('shop_order_ship');

        if (! $this->order) {
            return;
        }

        $this->validate([
            'trackingCode' => ['required', 'string', 'max:255'],
        ]);

        try {
            $orderStatusService->markAsShipped($this->order, $this->trackingCode);
        } catch (\InvalidArgumentException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());

            return;
        }

        $this->dispatch('panel.shop.order.index.render');
        $this->dispatch('panel.shop.dashboard.index.refresh-data');
        Flux::modal('panel.shop.order.ship.modal')->close();
        Flux::toast(variant: 'success', text: __('general.order_shipped'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.order.ship');
    }
}
