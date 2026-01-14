<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\INV\InventoryReceiptItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Schema;

class SaveInventoryReceiptItemDataJob implements ShouldQueue
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
        InventoryReceiptItem::truncate();
        foreach ($this->data as $grouping) {
            InventoryReceiptItem::unguard();
            InventoryReceiptItem::firstOrCreate($grouping);
        }
    }
}
