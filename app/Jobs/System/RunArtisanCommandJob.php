<?php

namespace App\Jobs\System;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunArtisanCommandJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $command,
        public array $parameters = [],
    ) {}

    public function handle(): void
    {
        Log::info("Running Artisan command via Job: {$this->command}");

        try {
            Artisan::call($this->command, $this->parameters);
            Log::info("Artisan command completed: {$this->command}\n" . Artisan::output());
        } catch (\Exception $e) {
            Log::error("Artisan command failed: {$this->command}\n" . $e->getMessage());
        }
    }
}
