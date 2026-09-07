<?php

namespace App\Services\Shop;

use App\Enums\OrderStatusEnum;
use App\Jobs\Notification\SendBaleMessageJob;
use App\Jobs\Notification\SendSmsMessageJob;
use App\Models\Shop\Order;
use App\Models\Shop\ProductPrice;
use App\Settings\BaleSettings;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    public function markAsPaid(Order $order): bool
    {
        if ($order->paid_at !== null) {
            return false;
        }

        $marked = false;

        DB::transaction(function () use ($order, &$marked) {
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

            $marked = true;
        });

        if ($marked) {
            $this->notifyBalePaidOrder($order);
        }

        return $marked;
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

    private function notifyBalePaidOrder(Order $order): void
    {
        $bale = app(BaleSettings::class);

        if (trim($bale->bot_token) === '' || trim($bale->chat_id) === '') {
            return;
        }

        $order->loadMissing(['user', 'items']);

        $customer = trim(($order->user?->first_name ?? '').' '.($order->user?->last_name ?? ''));
        if ($customer === '') {
            $customer = $order->user?->mobile
                ?? data_get($order->shipping_address, 'name')
                ?? '-';
        }

        $itemLines = $order->items
            ->take(5)
            ->map(fn ($item) => sprintf(
                '• %s × %s',
                $item->name,
                number_format((int) $item->quantity)
            ));

        $remaining = $order->items->count() - $itemLines->count();
        if ($remaining > 0) {
            $itemLines->push(__('general.and_more_items', ['count' => $remaining]));
        }

        $siteTitle = app(GeneralSettings::class)->title;

        $message = __('general.order_paid_bale_message', [
            'site' => $siteTitle,
            'order_number' => $order->order_number,
            'customer' => $customer,
            'amount' => number_format((float) $order->total_amount),
            'items' => $itemLines->implode("\n") ?: '-',
            'url' => route('order.view', ['id' => $order->id]),
        ]);

        dispatch(new SendBaleMessageJob($bale->chat_id, $message));
    }

    private function deductInventory(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $priceId = data_get($item->meta, 'price_id');

            $productPrice = $priceId
                ? ProductPrice::find($priceId)
                : ProductPrice::query()
                    ->where('product_id', $item->sku)
                    ->where('color_id', $item->color_id)
                    ->where('warranty_id', $item->warranty_id)
                    ->first();

            if ($productPrice) {
                $productPrice->decrement('quantity', $item->quantity);
            }
        }
    }
}
