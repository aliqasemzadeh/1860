<?php

namespace App\Jobs\Sepidar;

use App\Models\Sepidar\GNR\Party;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

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
        $model =
        if($this->clean) {
            Schema::disableForeignKeyConstraints();
            Role::truncate();
        }
        foreach ($this->data as $item) {
            Party::unguard();
            Party::firstOrCreate($item);
        }
    }
}
