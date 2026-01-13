<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\GNR\Grouping;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Schema;

class SaveGroupingDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Schema::disableForeignKeyConstraints();
        Grouping::truncate();
        foreach ($this->data as $grouping) {
            Grouping::unguard();
            Grouping::firstOrCreate($grouping);
        }
    }
}
