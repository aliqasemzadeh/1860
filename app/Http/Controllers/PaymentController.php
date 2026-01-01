<?php

namespace App\Http\Controllers;

use App\Models\Shop\Order;
use Illuminate\Http\Request;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Facade\Payment;
use Flux\Flux;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        try {
            // Get order ID from session
            $orderId = session('payment_order_id');
            if (! $orderId) {
                return redirect()->route('order.index')->with('error', __('app.payment_error'));
            }

            $order = Order::find($orderId);
            if (! $order) {
                return redirect()->route('order.index')->with('error', __('app.order_not_found'));
            }

            // Get transaction ID from request (different gateways use different parameters)
            $transactionId = $request->get('transactionId') 
                ?? $request->get('Authority') 
                ?? $request->get('RefNum')
                ?? $request->get('token')
                ?? $request->get('id');

            // If not in request, get from order meta
            if (! $transactionId) {
                $meta = $order->meta ?? [];
                $transactionId = $meta['payment_transaction_id'] ?? null;
            }

            if (! $transactionId) {
                return redirect()->route('order.view', ['id' => $order->id])
                    ->with('error', __('app.payment_error'));
            }

            // Verify payment
            $receipt = Payment::amount($order->total_amount)
                ->transactionId($transactionId)
                ->verify();

            // Update order
            $order->update([
                'paid_at' => now(),
                'status' => 'processing',
            ]);

            // Update meta with receipt
            $meta = $order->meta ?? [];
            $meta['payment_receipt'] = $receipt;
            $meta['payment_verified_at'] = now()->toDateTimeString();
            $order->update(['meta' => $meta]);

            // Clear session
            session()->forget('payment_order_id');

            return redirect()->route('order.view', ['id' => $order->id])
                ->with('success', __('app.payment_successful'));

        } catch (InvalidPaymentException $exception) {
            // Payment failed
            $orderId = session('payment_order_id');
            session()->forget('payment_order_id');
            return redirect()->route('order.view', ['id' => $orderId ?? 0])
                ->with('error', __('app.payment_failed').': '.$exception->getMessage());
        } catch (\Exception $e) {
            session()->forget('payment_order_id');
            return redirect()->route('order.index')
                ->with('error', __('app.payment_error').': '.$e->getMessage());
        }
    }
}

