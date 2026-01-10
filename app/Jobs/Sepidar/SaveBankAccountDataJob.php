<?php

namespace App\Jobs\Sepidar;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

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
        Cache::store('file')->put('sepidar_bank_data', $this->data);
    }
}
