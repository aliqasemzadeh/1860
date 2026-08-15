<?php

namespace App\Jobs\System;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\System\CommandLog;

class RunArtisanCommandJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $command,
        public array $parameters = [],
    ) {}

    public function handle(): void
    {
        $start = microtime(true);
        $log = CommandLog::create([
            'command' => $this->command,
            'parameters' => $this->parameters,
            'status' => 'running',
        ]);

        Log::channel('commands')->info("Running Artisan command via Job: {$this->command}", [
            'parameters' => $this->parameters,
            'log_id' => $log->id,
        ]);

        try {
            $statusCode = Artisan::call($this->command, $this->parameters);
            $output = Artisan::output();
            $duration = (int) ((microtime(true) - $start) * 1000);

            $log->update([
                'output' => $output,
                'status_code' => $statusCode,
                'execution_time_ms' => $duration,
                'status' => $statusCode === 0 ? 'success' : 'failed',
            ]);

            Log::channel('commands')->info("Artisan command completed: {$this->command}", [
                'status_code' => $statusCode,
                'duration_ms' => $duration,
                'output_length' => strlen($output),
            ]);
        } catch (\Exception $e) {
            $duration = (int) ((microtime(true) - $start) * 1000);

            $log->update([
                'output' => $e->getMessage(),
                'status_code' => 1,
                'execution_time_ms' => $duration,
                'status' => 'failed',
            ]);

            Log::channel('commands')->error("Artisan command failed: {$this->command}", [
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
            ]);
        }
    }
}
