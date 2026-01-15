<?php

namespace App\Jobs\Sepidar;

use App\Models\Accounting\Bank;
use App\Models\Sepidar\RPA\BankBranch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SaveBankDataJob implements ShouldQueue
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
            Bank::unguard();
            Bank::firstOrCreate($item);
        }
    }
}
