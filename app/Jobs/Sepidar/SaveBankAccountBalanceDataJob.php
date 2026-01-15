<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\ACC\DL;
use App\Models\Sepidar\RPA\BankAccountBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SaveBankAccountBalanceDataJob implements ShouldQueue
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
            BankAccountBalance::unguard();
            BankAccountBalance::firstOrCreate($item);
        }
    }
}
