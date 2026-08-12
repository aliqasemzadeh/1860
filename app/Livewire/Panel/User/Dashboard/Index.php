<?php

namespace App\Livewire\Panel\User\Dashboard;

use App\Models\Shop\Order;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('user_dashboard');
    }

    #[Computed]
    public function user()
    {
        return auth()->user();
    }

    #[Computed]
    public function stats(): array
    {
        $userId = auth()->id();

        return Cache::remember('panel.user.dashboard.stats.'.$userId, 60, function () use ($userId) {
            $orderStats = Order::query()
                ->where('user_id', $userId)
                ->selectRaw('count(*) as orders_count')
                ->selectRaw('sum(case when paid_at is null and cancelled_at is null then 1 else 0 end) as unpaid_count')
                ->selectRaw('coalesce(sum(case when paid_at is not null then total_amount else 0 end), 0) as paid_total')
                ->first();

            return [
                'orders_count' => (int) ($orderStats->orders_count ?? 0),
                'unpaid_count' => (int) ($orderStats->unpaid_count ?? 0),
                'paid_total' => (float) ($orderStats->paid_total ?? 0),
                'addresses_count' => (int) auth()->user()->shippingAddresses()->count(),
            ];
        });
    }

    #[Layout('layouts.panels.user')]
    #[On('panel.user.dashboard.index.render')]
    public function render()
    {
        $this->authorize('user_dashboard');

        return view('livewire.panel.user.dashboard.index');
    }
}
