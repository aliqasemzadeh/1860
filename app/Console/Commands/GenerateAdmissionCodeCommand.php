<?php

namespace App\Console\Commands;

use App\Models\ServiceCenter\Repair;
use Illuminate\Console\Command;
use Morilog\Jalali\Jalalian;

class GenerateAdmissionCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-admission-code-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild all admission codes based on Jalali dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to rebuild admission codes...');

        // Get all repairs ordered by creation date
        $repairs = Repair::query()
            ->whereNotNull('created_at')
            ->orderBy('created_at')
            ->get();

        if ($repairs->isEmpty()) {
            $this->info('No repairs found.');

            return self::SUCCESS;
        }

        // Group repairs by Jalali year and month
        $groupedRepairs = [];
        foreach ($repairs as $repair) {
            $jalaliDate = Jalalian::fromCarbon($repair->created_at);
            $year = $jalaliDate->getYear();
            $month = $jalaliDate->getMonth();
            $key = sprintf('%d-%02d', $year, $month);

            if (!isset($groupedRepairs[$key])) {
                $groupedRepairs[$key] = [];
            }

            $groupedRepairs[$key][] = $repair;
        }

        $this->info(sprintf('Found %d repairs in %d Jalali months.', $repairs->count(), count($groupedRepairs)));

        $bar = $this->output->createProgressBar($repairs->count());
        $bar->start();

        // Process each group and assign admission codes
        foreach ($groupedRepairs as $key => $monthRepairs) {
            [$year, $month] = explode('-', $key);
            $counter = 1;

            foreach ($monthRepairs as $repair) {
                $repair->admission_counter = $counter;
                $repair->admission_code = sprintf('%d%02d%03d', (int) $year, (int) $month, $counter);
                $repair->saveQuietly();

                $counter++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Admission codes rebuilt successfully!');

        return self::SUCCESS;
    }
}
