<?php

namespace App\Services\Shop;

use App\Enums\OrderStatusEnum;
use App\Jobs\Notification\SendSmsMessageJob;
use App\Models\Shop\Order;
use App\Models\Shop\ProductPrice;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    public function markAsPaid(Order $order): bool
    {
        if ($order->paid_at !== null) {
            return false;
        }

        DB::transaction(function () use ($order) {
            $order->refresh();

            if ($order->paid_at !== null) {
                return;
            }

            $order->update([
                'paid_at' => now(),
                'status' => OrderStatusEnum::Processing->value,
            ]);

            $this->deductInventory($order);

            $adminMobile = config('shop.admin_mobile');

            if ($adminMobile) {
                dispatch(new SendSmsMessageJob(
                    $adminMobile,
                    __('general.order_paid_admin_sms', [
                        'order_number' => $order->order_number,
                        'amount' => number_format((float) $order->total_amount),
                    ])
                ));
            }
        });

        return true;
    }

    public function markAsShipped(Order $order, string $trackingCode): void
    {
        $currentStatus = OrderStatusEnum::tryFromSafe($order->status);

        if (! $currentStatus->canTransitionTo(OrderStatusEnum::Shipped)) {
            throw new \InvalidArgumentException(__('general.order_cannot_be_shipped'));
        }

        if ($order->paid_at === null) {
            throw new \InvalidArgumentException(__('general.order_must_be_paid_to_ship'));
        }

        DB::transaction(function () use ($order, $trackingCode) {
            $order->update([
                'status' => OrderStatusEnum::Shipped->value,
                'tracking_code' => $trackingCode,
                'shipped_at' => now(),
            ]);

            $order->load('user');

            if ($order->user?->mobile) {
                dispatch(new SendSmsMessageJob(
                    $order->user->mobile,
                    __('general.order_shipped_sms', [
                        'order_number' => $order->order_number,
                        'tracking_code' => $trackingCode,
                    ])
                ));
            }
        });
    }

    public function markAsDelivered(Order $order): void
    {
        $currentStatus = OrderStatusEnum::tryFromSafe($order->status);

        if (! $currentStatus->canTransitionTo(OrderStatusEnum::Delivered)) {
            return;
        }

        $order->update([
            'status' => OrderStatusEnum::Delivered->value,
            'delivered_at' => now(),
        ]);
    }

    public function markAsCancelled(Order $order, ?string $reason = null): void
    {
        $currentStatus = OrderStatusEnum::tryFromSafe($order->status);

        if (! $currentStatus->canTransitionTo(OrderStatusEnum::Cancelled)) {
            return;
        }

        $order->update([
            'status' => OrderStatusEnum::Cancelled->value,
            'cancelled_at' => now(),
        ]);
    }

    private function deductInventory(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $productPrice = ProductPrice::where('product_id', function ($query) use ($item) {
                $query->select('id')
                    ->from('products')
                    ->where('sku', $item->sku)
                    ->limit(1);
            })
                ->where('color_id', $item->color_id)
                ->where('warranty_id', $item->warranty_id)
                ->first();

            if ($productPrice) {
                $productPrice->decrement('quantity', $item->quantity);
            }
        }
    }
}
