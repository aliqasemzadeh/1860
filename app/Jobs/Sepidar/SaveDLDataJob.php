<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\ACC\DL;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Schema;

class SaveDLDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
        //
    }

    public function handle(): void
    {
        foreach ($this->data as $item) {
            DL::unguard();
            DL::firstOrCreate($item);
        }
    }
}
