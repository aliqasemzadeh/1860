<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\GNR\Grouping;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        foreach ($this->data as $grouping) {
            Grouping::firstOrCreate(['GroupingID' => $grouping['GroupingID']], $grouping);
        }
    }
}
