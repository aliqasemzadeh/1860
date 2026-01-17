<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\RPA\BankAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SaveBankAccountDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->data as $item) {
            BankAccount::unguard();
            BankAccount::firstOrCreate($item);
        }
    }
}
