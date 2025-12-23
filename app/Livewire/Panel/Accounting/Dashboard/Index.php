<?php

namespace App\Livewire\Panel\Accounting\Dashboard;

use App\Models\Accounting\Bank;
use App\Models\Accounting\Cheque;
use App\Models\Accounting\Remittance;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Computed]
    public function stats(): array
    {
        $totalCheques = Cheque::sum('amount');
        $totalBankBalance = Bank::sum('balance');
        $totalRemittanceBalance = Remittance::sum('account_balance');
        $totalRemittancePayment = Remittance::sum('payment');

        return [
            [
                'title' => __('app.cheques_total'),
                'value' => number_format($totalCheques) . ' ' . __('app.toman'),
                'trend' => '—',
                'trendUp' => true,
            ],
            [
                'title' => __('app.banks_balance_total'),
                'value' => number_format($totalBankBalance) . ' ' . __('app.toman'),
                'trend' => '—',
                'trendUp' => true,
            ],
            [
                'title' => __('app.remittance_balance_total'),
                'value' => number_format($totalRemittanceBalance) . ' ' . __('app.toman'),
                'trend' => '—',
                'trendUp' => true,
            ],
            [
                'title' => __('app.remittance_payment_total'),
                'value' => number_format($totalRemittancePayment) . ' ' . __('app.toman'),
                'trend' => '—',
                'trendUp' => true,
            ],
        ];
    }

    #[Computed]
    public function upcomingCheques()
    {
        $start = Carbon::today();
        $end = Carbon::today()->addWeek();

        return Cheque::whereBetween('due_at', [$start, $end])
            ->orderBy('due_at')
            ->limit(20)
            ->get();
    }

    #[Layout('layouts.panels.accounting')]
    public function render()
    {
        return view('livewire.panel.accounting.dashboard.index');
    }
}
