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
            try {
                $model::truncate();
            } catch (\Exception $exception) {
                Log::channel('sepidar')->error('Truncate Error:', [
                    'table' => $this->table,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        foreach ($this->data as $item) {
            $model::unguard();
            try {
                $model::firstOrCreate($item);
            } catch (\Exception $exception) {
                Log::channel('sepidar')->error('Create Item Error:', [
                    'table' => $this->table,
                    'exception' => $exception->getMessage(),
                ]);
            }

        }

        Log::channel('sepidar')->info('Queue Done.', ['table' => $this->table]);
    }
}
