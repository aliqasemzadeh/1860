<?php

namespace App\Livewire\Main\Order;

use App\Models\Shop\Order;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment as GatewayPayment;

#[Layout('layouts.app')]
class Payment extends Component
{
    public $orderId;
    public $paymentHtml;

    public function mount($id)
    {
        $this->orderId = $id;
    }

    public function getOrderProperty()
    {
        if (! auth()->check()) {
            return null;
        }

        return Order::query()
            ->where('id', $this->orderId)
            ->where('user_id', auth()->id())
            ->first();
    }

    public function pay()
    {
        $order = $this->order;

        if (! $order) {
            Flux::toast(variant: 'danger', text: __('app.order_not_found'));
            return $this->redirect(route('order.index'), navigate: true);
        }

        if ($order->paid_at) {
            Flux::toast(variant: 'info', text: __('app.order_already_paid'));
            return $this->redirect(route('order.view', ['id' => $order->id]), navigate: true);
        }

        try {
            // Create invoice
            $invoice = (new Invoice)->amount($order->total_amount)
                ->detail([
                    'order_number' => $order->order_number,
                    'description' => __('app.payment_for_order', ['order_number' => $order->order_number]),
                ]);

            // Store order ID in session for callback
            session(['payment_order_id' => $order->id]);

            // Create payment with callback URL
            $callbackUrl = route('payment.callback');
            $payment = GatewayPayment::callbackUrl($callbackUrl)
                ->purchase($invoice, function ($driver, $transactionId) use ($order) {
                    // Store transaction ID in order meta
                    $meta = $order->meta ?? [];
                    $meta['payment_transaction_id'] = $transactionId;
                    $order->update(['meta' => $meta]);

                    Log::info('meta updated', ['meta' => $meta]);
                });

            Log::info('Payment created', ['payment' => $payment]);

            // Set payment HTML for rendering in view
            $this->paymentHtml = $payment->pay()->render();

        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('app.payment_error').': '.$e->getMessage());
            return $this->redirect(route('order.view', ['id' => $order->id]), navigate: true);
        }
    }

    public function render()
    {
        if (! $this->order) {
            abort(404);
        }

        if ($this->order->paid_at) {
            return $this->redirect(route('order.view', ['id' => $this->order->id]), navigate: true);
        }

        return view('livewire.main.order.payment');
    }
}
