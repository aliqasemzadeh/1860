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
        $today = Jalalian::now();

        // Start date: today
        $this->start_date = $today->format('Y/m/d');

        // Default end date: end of current Jalali year
        $currentYear = $today->getYear();
        $currentYearLastMonthDays = (new Jalalian($currentYear, 12, 1))->getMonthDays();

        $endOfCurrentYear = new Jalalian($currentYear, 12, $currentYearLastMonthDays);

        // If we are in the last month of the year AND
        // the difference between start and end is less than 30 days,
        // extend the range to the end of next year.
        $startCarbon = $today->toCarbon()->startOfDay();
        $endCurrentYearCarbon = $endOfCurrentYear->toCarbon()->endOfDay();

        if ($today->getMonth() === 12 && $endCurrentYearCarbon->diffInDays($startCarbon) < 30) {
            $nextYear = $currentYear + 1;
            $nextYearLastMonthDays = (new Jalalian($nextYear, 12, 1))->getMonthDays();
            $endOfNextYear = new Jalalian($nextYear, 12, $nextYearLastMonthDays);

            $this->end_date = $endOfNextYear->format('Y/m/d');
        } else {
            $this->end_date = $endOfCurrentYear->format('Y/m/d');
        }

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

        if ($mode === 'monthly') {
            return $this->buildMonthlyPeriods($start, $end);
        }

        if ($mode === 'weekly') {
            return $this->buildWeeklyPeriods($start, $end);
        }

        if ($mode === 'quarterly') {
            return $this->buildQuarterlyPeriods($start, $end);
        }

        $currentStart = $start->copy();

        while ($currentStart->lessThanOrEqualTo($end)) {
            $currentEnd = match ($mode) {
                'ten_days' => $currentStart->copy()->addDays(9)->endOfDay(),
                'semi_annual' => $currentStart->copy()->addMonths(6)->subDay()->endOfDay(),
                default => $currentStart->copy()->addMonth()->subDay()->endOfDay(),
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
                'ten_days' => $currentEnd->copy()->addDay()->startOfDay(),
                'semi_annual' => $currentEnd->copy()->addDay()->startOfDay(),
                default => $currentEnd->copy()->addDay()->startOfDay(),
            };
        }

        return $periods;
    }

    /**
     * Build monthly periods: first period from start_date to end of month,
     * then full months, last period from 1st to end_date if partial.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildMonthlyPeriods(Carbon $start, Carbon $end): array
    {
        $periods = [];

        $startJalali = Jalalian::fromCarbon($start);

        // First period: from start_date to end of that Jalali month
        $firstMonthEndJalali = new Jalalian(
            $startJalali->getYear(),
            $startJalali->getMonth(),
            $startJalali->getMonthDays()
        );
        $firstMonthEndCarbon = $firstMonthEndJalali->toCarbon()->endOfDay();

        if ($firstMonthEndCarbon->greaterThan($end)) {
            $firstMonthEndCarbon = $end->copy();
        }

        $total = Cheque::query()
            ->whereBetween('due_at', [$start, $firstMonthEndCarbon])
            ->sum('amount');

        $periods[] = [
            'start' => $start->copy(),
            'end' => $firstMonthEndCarbon->copy(),
            'label' => sprintf(
                '%s - %s',
                $startJalali->format('Y/m/d'),
                Jalalian::fromCarbon($firstMonthEndCarbon)->format('Y/m/d')
            ),
            'total' => $total,
        ];

        // If first period already covers everything, return
        if ($firstMonthEndCarbon->greaterThanOrEqualTo($end)) {
            return $periods;
        }

        // Subsequent full months
        $currentMonthStart = $firstMonthEndCarbon->copy()->addDay()->startOfDay();
        $currentMonthStartJalali = Jalalian::fromCarbon($currentMonthStart);

        while ($currentMonthStart->lessThanOrEqualTo($end)) {
            $currentMonthEndJalali = new Jalalian(
                $currentMonthStartJalali->getYear(),
                $currentMonthStartJalali->getMonth(),
                $currentMonthStartJalali->getMonthDays()
            );
            $currentMonthEnd = $currentMonthEndJalali->toCarbon()->endOfDay();

            if ($currentMonthEnd->greaterThan($end)) {
                // Last partial month: from 1st of month to end_date
                $currentMonthEnd = $end->copy();
            }

            $total = Cheque::query()
                ->whereBetween('due_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('amount');

            $periods[] = [
                'start' => $currentMonthStart->copy(),
                'end' => $currentMonthEnd->copy(),
                'label' => sprintf(
                    '%s - %s',
                    $currentMonthStartJalali->format('Y/m/d'),
                    Jalalian::fromCarbon($currentMonthEnd)->format('Y/m/d')
                ),
                'total' => $total,
            ];

            // Move to next month
            if ($currentMonthEnd->greaterThanOrEqualTo($end)) {
                break;
            }

            $currentMonthStart = $currentMonthEnd->copy()->addDay()->startOfDay();
            $currentMonthStartJalali = Jalalian::fromCarbon($currentMonthStart);
        }

        return $periods;
    }

    /**
     * Build weekly periods: first period from start_date to end of week (Saturday),
     * then full weeks (Sunday to Saturday), last period from Sunday to end_date if partial.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildWeeklyPeriods(Carbon $start, Carbon $end): array
    {
        $periods = [];

        // First period: from start_date to end of week (Saturday)
        $dayOfWeek = $start->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $daysUntilSaturday = 6 - $dayOfWeek;
        $firstWeekEnd = $start->copy()->addDays($daysUntilSaturday)->endOfDay();

        if ($firstWeekEnd->greaterThan($end)) {
            $firstWeekEnd = $end->copy();
        }

        $total = Cheque::query()
            ->whereBetween('due_at', [$start, $firstWeekEnd])
            ->sum('amount');

        $periods[] = [
            'start' => $start->copy(),
            'end' => $firstWeekEnd->copy(),
            'label' => sprintf(
                '%s - %s',
                Jalalian::fromCarbon($start)->format('Y/m/d'),
                Jalalian::fromCarbon($firstWeekEnd)->format('Y/m/d')
            ),
            'total' => $total,
        ];

        // If first period already covers everything, return
        if ($firstWeekEnd->greaterThanOrEqualTo($end)) {
            return $periods;
        }

        // Subsequent full weeks (Sunday to Saturday)
        $currentWeekStart = $firstWeekEnd->copy()->addDay()->startOfDay();

        while ($currentWeekStart->lessThanOrEqualTo($end)) {
            $currentWeekEnd = $currentWeekStart->copy()->addDays(6)->endOfDay();

            if ($currentWeekEnd->greaterThan($end)) {
                // Last partial week: from Sunday to end_date
                $currentWeekEnd = $end->copy();
            }

            $total = Cheque::query()
                ->whereBetween('due_at', [$currentWeekStart, $currentWeekEnd])
                ->sum('amount');

            $periods[] = [
                'start' => $currentWeekStart->copy(),
                'end' => $currentWeekEnd->copy(),
                'label' => sprintf(
                    '%s - %s',
                    Jalalian::fromCarbon($currentWeekStart)->format('Y/m/d'),
                    Jalalian::fromCarbon($currentWeekEnd)->format('Y/m/d')
                ),
                'total' => $total,
            ];

            // Move to next week
            if ($currentWeekEnd->greaterThanOrEqualTo($end)) {
                break;
            }

            $currentWeekStart = $currentWeekEnd->copy()->addDay()->startOfDay();
        }

        return $periods;
    }

    /**
     * Build quarterly periods: first period from start_date to end of quarter,
     * then full quarters, last period from start of quarter to end_date if partial.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildQuarterlyPeriods(Carbon $start, Carbon $end): array
    {
        $periods = [];

        $startJalali = Jalalian::fromCarbon($start);
        $endJalali = Jalalian::fromCarbon($end);

        // Determine which quarter the start date is in
        $startMonth = $startJalali->getMonth();
        $quarter = (int) ceil($startMonth / 3);
        $quarterStartMonth = ($quarter - 1) * 3 + 1;
        $quarterEndMonth = $quarter * 3;

        // First period: from start_date to end of that quarter
        $firstQuarterEndJalali = new Jalalian(
            $startJalali->getYear(),
            $quarterEndMonth,
            (new Jalalian($startJalali->getYear(), $quarterEndMonth, 1))->getMonthDays()
        );
        $firstQuarterEndCarbon = $firstQuarterEndJalali->toCarbon()->endOfDay();

        if ($firstQuarterEndCarbon->greaterThan($end)) {
            $firstQuarterEndCarbon = $end->copy();
        }

        $total = Cheque::query()
            ->whereBetween('due_at', [$start, $firstQuarterEndCarbon])
            ->sum('amount');

        $periods[] = [
            'start' => $start->copy(),
            'end' => $firstQuarterEndCarbon->copy(),
            'label' => sprintf(
                '%s - %s',
                $startJalali->format('Y/m/d'),
                Jalalian::fromCarbon($firstQuarterEndCarbon)->format('Y/m/d')
            ),
            'total' => $total,
        ];

        // If first period already covers everything, return
        if ($firstQuarterEndCarbon->greaterThanOrEqualTo($end)) {
            return $periods;
        }

        // Subsequent full quarters - start from the first day of the next quarter
        $nextQuarterStartJalali = new Jalalian(
            $startJalali->getYear(),
            $quarterEndMonth + 1,
            1
        );

        // Handle year overflow
        if ($nextQuarterStartJalali->getMonth() > 12) {
            $nextQuarterStartJalali = new Jalalian(
                $startJalali->getYear() + 1,
                1,
                1
            );
        }

        $currentQuarterStart = $nextQuarterStartJalali->toCarbon()->startOfDay();
        $currentQuarterStartJalali = $nextQuarterStartJalali;

        while ($currentQuarterStart->lessThanOrEqualTo($end)) {
            $currentMonth = $currentQuarterStartJalali->getMonth();
            $currentQuarter = (int) ceil($currentMonth / 3);
            $currentQuarterEndMonth = $currentQuarter * 3;

            $currentQuarterEndJalali = new Jalalian(
                $currentQuarterStartJalali->getYear(),
                $currentQuarterEndMonth,
                (new Jalalian($currentQuarterStartJalali->getYear(), $currentQuarterEndMonth, 1))->getMonthDays()
            );
            $currentQuarterEnd = $currentQuarterEndJalali->toCarbon()->endOfDay();

            if ($currentQuarterEnd->greaterThan($end)) {
                // Last partial quarter: from start of quarter to end_date
                $currentQuarterEnd = $end->copy();
            }

            $total = Cheque::query()
                ->whereBetween('due_at', [$currentQuarterStart, $currentQuarterEnd])
                ->sum('amount');

            $periods[] = [
                'start' => $currentQuarterStart->copy(),
                'end' => $currentQuarterEnd->copy(),
                'label' => sprintf(
                    '%s - %s',
                    $currentQuarterStartJalali->format('Y/m/d'),
                    Jalalian::fromCarbon($currentQuarterEnd)->format('Y/m/d')
                ),
                'total' => $total,
            ];

            // Move to next quarter
            if ($currentQuarterEnd->greaterThanOrEqualTo($end)) {
                break;
            }

            // Calculate next quarter start
            $nextQuarterStartMonth = $currentQuarterEndMonth + 1;
            $nextQuarterStartYear = $currentQuarterStartJalali->getYear();

            if ($nextQuarterStartMonth > 12) {
                $nextQuarterStartMonth = 1;
                $nextQuarterStartYear++;
            }

            $currentQuarterStartJalali = new Jalalian($nextQuarterStartYear, $nextQuarterStartMonth, 1);
            $currentQuarterStart = $currentQuarterStartJalali->toCarbon()->startOfDay();
        }

        return $periods;
    }

    public function render()
    {
        return view('livewire.panel.accounting.cheque.report');
    }
}
