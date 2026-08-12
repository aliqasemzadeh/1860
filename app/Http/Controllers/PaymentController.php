<?php

namespace App\Http\Controllers;

use App\Models\Shop\Order;
use App\Services\Shop\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Facade\Payment;

class PaymentController extends Controller
{
    public function callback(Request $request, $orderId, OrderStatusService $orderStatusService)
    {
        Log::info('Payment callback received', [
            'order_id' => $orderId,
            'request_data' => $request->all(),
        ]);

        try {
            $order = Order::with('items')->find($orderId);
            if (! $order) {
                return redirect()->route('order.index')->with('error', __('app.order_not_found'));
            }

            if ($order->paid_at !== null) {
                session()->forget('payment_order_id');

                return redirect()->route('order.view', ['id' => $order->id])
                    ->with('success', __('app.payment_successful'));
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

            $orderStatusService->markAsPaid($order);

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
            Log::error('Payment verification failed', [
                'order_id' => $orderId,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('order.view', ['id' => $orderId])
                ->with('error', __('app.payment_failed').': '.$exception->getMessage());
        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('order.index')
                ->with('error', __('app.payment_error').': '.$e->getMessage());
        }
    }
}
