<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\INV\Item;
use App\Models\Sepidar\SLS\InvoiceItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Schema;

class SaveInvoiceItemDataJob implements ShouldQueue
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
        foreach ($this->data as $item) {
            InvoiceItem::unguard();
            InvoiceItem::firstOrCreate($item);
        }
    }
}
