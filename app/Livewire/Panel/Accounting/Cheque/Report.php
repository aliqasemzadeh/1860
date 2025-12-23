<?php

namespace App\Livewire\Panel\Accounting\Cheque;

use App\Models\Accounting\Cheque;
use Carbon\Carbon;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class Report extends Component
{
    /**
     * Mode of aggregation: weekly, ten_days, monthly, quarterly, semi_annual.
     */
    public string $mode = 'monthly';

    /**
     * Start and end date in Jalali (Y/m/d), e.g. 1403/01/01.
     */
    public ?string $start_date = null;

    public ?string $end_date = null;

    /**
     * Computed periods with totals.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $periods = [];

    public function mount(): void
    {
        // Default: monthly for the next 3 months from today.
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->copy()->addMonths(3)->endOfDay();

        $this->start_date = Jalalian::fromCarbon($start)->format('Y/m/d');
        $this->end_date = Jalalian::fromCarbon($end)->format('Y/m/d');

        $this->refreshReport();
    }

    public function updatedMode(): void
    {
        // When mode changes, just recompute based on current dates.
        $this->refreshReport();
    }

    public function updatedStartDate(): void
    {
        $this->refreshReport();
    }

    public function updatedEndDate(): void
    {
        $this->refreshReport();
    }

    public function refreshReport(): void
    {
        // Validate dates (if empty, do nothing)
        if (! $this->start_date || ! $this->end_date) {
            $this->periods = [];

            return;
        }

        // Normalise possible "-" separators to "/"
        $startJ = str_replace('-', '/', trim($this->start_date));
        $endJ = str_replace('-', '/', trim($this->end_date));

        if (! preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $startJ) || ! preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $endJ)) {
            $this->periods = [];

            return;
        }

        try {
            $startCarbon = Jalalian::fromFormat('Y/m/d', $startJ)->toCarbon()->startOfDay();
            $endCarbon = Jalalian::fromFormat('Y/m/d', $endJ)->toCarbon()->endOfDay();
        } catch (\Throwable $e) {
            $this->periods = [];

            return;
        }

        if ($endCarbon->lessThan($startCarbon)) {
            $this->periods = [];

            return;
        }

        $this->periods = $this->buildPeriods($startCarbon, $endCarbon, $this->mode);
    }

    /**
     * Build periods and aggregate cheque amounts for each one.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildPeriods(Carbon $start, Carbon $end, string $mode): array
    {
        $periods = [];

        $currentStart = $start->copy();

        while ($currentStart->lessThanOrEqualTo($end)) {
            $currentEnd = match ($mode) {
                'weekly' => $currentStart->copy()->addDays(6)->endOfDay(),
                'ten_days' => $currentStart->copy()->addDays(9)->endOfDay(),
                'quarterly' => $currentStart->copy()->addMonths(3)->subDay()->endOfDay(),
                'semi_annual' => $currentStart->copy()->addMonths(6)->subDay()->endOfDay(),
                default => $currentStart->copy()->addMonth()->subDay()->endOfDay(), // monthly
            };

            if ($currentEnd->greaterThan($end)) {
                $currentEnd = $end->copy();
            }

            $total = Cheque::query()
                ->whereBetween('due_at', [$currentStart, $currentEnd])
                ->sum('amount');

            $periods[] = [
                'start' => $currentStart->copy(),
                'end' => $currentEnd->copy(),
                'label' => sprintf(
                    '%s - %s',
                    Jalalian::fromCarbon($currentStart)->format('Y/m/d'),
                    Jalalian::fromCarbon($currentEnd)->format('Y/m/d')
                ),
                'total' => $total,
            ];

            // Move to next period
            $currentStart = match ($mode) {
                'weekly' => $currentEnd->copy()->addDay()->startOfDay(),
                'ten_days' => $currentEnd->copy()->addDay()->startOfDay(),
                'quarterly' => $currentEnd->copy()->addDay()->startOfDay(),
                'semi_annual' => $currentEnd->copy()->addDay()->startOfDay(),
                default => $currentEnd->copy()->addDay()->startOfDay(),
            };
        }

        return $periods;
    }

    public function render()
    {
        return view('livewire.panel.accounting.cheque.report');
    }
}
