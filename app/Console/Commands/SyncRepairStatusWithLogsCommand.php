<?php

namespace App\Console\Commands;

use App\Models\ServiceCenter\Repair;
use App\Models\ServiceCenter\RepairLog;
use Illuminate\Console\Command;

class SyncRepairStatusWithLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:sync-status-with-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync repair statuses based on their latest log entry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to sync repair statuses with logs...');

        $repairs = Repair::query()
            ->with(['logs' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->get();

        $synced = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($repairs->count());
        $bar->start();

        foreach ($repairs as $repair) {
            $latestLog = $repair->logs->first();

            if (!$latestLog) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if ($repair->status !== $latestLog->status) {
                $repair->status = $latestLog->status;
                $repair->saveQuietly();
                $synced++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Sync completed! Synced: {$synced}, Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
