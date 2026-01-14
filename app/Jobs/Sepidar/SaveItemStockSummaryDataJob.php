<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\INV\ItemStockSummary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Schema;

class SaveItemStockSummaryDataJob implements ShouldQueue
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
        ItemStockSummary::truncate();
        foreach ($this->data as $item) {
            ItemStockSummary::unguard();
            ItemStockSummary::firstOrCreate($item);
        }
    }
}
