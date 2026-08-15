<?php

namespace App\Livewire\Panel\Shop\Dashboard;

use App\Enums\OrderStatusEnum;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('shop_dashboard_index');
    }

    #[Computed]
    public function stats(): array
    {
        return Cache::remember('panel.shop.dashboard.stats', 60, function () {
            $startOfDay = now()->startOfDay();

            $orders = Order::query()
                ->selectRaw('count(*) as orders_count')
                ->selectRaw("sum(case when status = 'pending' then 1 else 0 end) as pending_count")
                ->selectRaw("sum(case when status = 'processing' then 1 else 0 end) as processing_count")
                ->selectRaw("sum(case when status = 'shipped' then 1 else 0 end) as shipped_count")
                ->selectRaw("sum(case when status = 'delivered' then 1 else 0 end) as delivered_count")
                ->selectRaw("sum(case when status = 'cancelled' then 1 else 0 end) as cancelled_count")
                ->selectRaw('coalesce(sum(case when paid_at is not null then total_amount else 0 end), 0) as paid_total')
                ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as today_count', [$startOfDay])
                ->selectRaw('coalesce(sum(case when paid_at >= ? then total_amount else 0 end), 0) as today_paid_total', [$startOfDay])
                ->first();

            $statusCounts = [];
            foreach (OrderStatusEnum::cases() as $status) {
                $statusCounts[$status->value] = (int) ($orders->{$status->value.'_count'} ?? 0);
            }

            return [
                'products_count' => Product::query()->count(),
                'out_of_stock_count' => Product::query()->whereAvailability(false)->count(),
                'orders_count' => (int) ($orders->orders_count ?? 0),
                'paid_total' => (float) ($orders->paid_total ?? 0),
                'today_count' => (int) ($orders->today_count ?? 0),
                'today_paid_total' => (float) ($orders->today_paid_total ?? 0),
                'categories_count' => Category::query()->count(),
                'brands_count' => Brand::query()->count(),
                'status_counts' => $statusCounts,
            ];
        });
    }

    #[Computed]
    public function recentOrders(): Collection
    {
        return Order::query()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();
    }

    #[On('panel.shop.dashboard.index.render')]
    public function refresh(): void
    {
        Cache::forget('panel.shop.dashboard.stats');
        unset($this->stats, $this->recentOrders);

        Flux::toast(variant: 'success', text: __('general.updated_successfully'));
    }

    #[Layout('layouts.panels.shop')]
    public function render()
    {
        $this->authorize('shop_dashboard_index');

        return view('livewire.panel.shop.dashboard.index');
    }
}
