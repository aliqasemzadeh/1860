<?php

namespace App\Console\Commands;

use App\Models\Accounting\Remittance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveOldRemittancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-old-remittances-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove previous day remittances every midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // We consider "old" remittances as those created before today (previous days).
        $cutoff = Carbon::today();

        $count = Remittance::where('created_at', '<', $cutoff)->delete();

        $this->info("Removed {$count} old remittances created before {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
