<?php

namespace App\Jobs\Sepidar;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SaveTableDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data, public string $table, public bool $clean = false)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('sepidar')->info('Queue Start...', ['table' => $this->table]);
        $model = config('sepidar_invoices.tables.' . $this->table . '.model');
        if($this->clean) {
            Log::channel('sepidar')->info('Queue Clean.', ['table' => $this->table]);
            Schema::disableForeignKeyConstraints();
            $model::truncate();
        }

        foreach ($this->data as $item) {
            $model::unguard();
            $model::firstOrCreate($item);
        }

        Log::channel('sepidar')->info('Queue Done.', ['table' => $this->table]);
    }
}
