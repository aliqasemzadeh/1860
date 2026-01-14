<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\INV\InventoryReceipt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Schema;

class SaveInventoryReceiptDataJob implements ShouldQueue
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
        InventoryReceipt::truncate();
        foreach ($this->data as $grouping) {
            InventoryReceipt::unguard();
            InventoryReceipt::firstOrCreate($grouping);
        }
    }
}
