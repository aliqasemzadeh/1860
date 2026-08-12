<?php

namespace App\Console\Commands\Shop;

use App\Models\Shop\Order;
use App\Services\Shop\OrderStatusService;
use Illuminate\Console\Command;

class AutoDeliverOrdersCommand extends Command
{
    protected $signature = 'shop:auto-deliver-orders';

    protected $description = 'Mark shipped orders as delivered after the configured number of days';

    public function handle(OrderStatusService $orderStatusService): int
    {
        $days = config('shop.auto_deliver_days', 10);

        $orders = Order::query()
            ->where('status', 'shipped')
            ->whereNotNull('shipped_at')
            ->whereNull('delivered_at')
            ->where('shipped_at', '<=', now()->subDays($days))
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $orderStatusService->markAsDelivered($order);
            $count++;
        }

        $this->info("Marked {$count} order(s) as delivered.");

        return self::SUCCESS;
    }
}
