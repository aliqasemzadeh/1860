<?php

namespace App\Console\Commands\Shop;

use App\Models\Shop\Order;
use App\Services\Shop\OrderStatusService;
use Illuminate\Console\Command;

class CancelUnpaidOrdersCommand extends Command
{
    protected $signature = 'shop:cancel-unpaid-orders';

    protected $description = 'Cancel unpaid orders that have exceeded the payment time limit';

    public function handle(OrderStatusService $orderStatusService): int
    {
        $minutes = config('shop.unpaid_cancel_minutes', 10);

        $orders = Order::query()
            ->where('status', 'pending')
            ->whereNull('paid_at')
            ->whereNull('cancelled_at')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $orderStatusService->markAsCancelled($order);
            $count++;
        }

        $this->info("Cancelled {$count} unpaid order(s).");

        return self::SUCCESS;
    }
}
