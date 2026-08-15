<?php

namespace App\Jobs\System;

use App\Models\System\CommandLog;
use App\Support\SystemCommandGuard;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class UpdateProjectJob implements ShouldQueue
{
    use Queueable;

    protected ?CommandLog $currentLog = null;

    public function __construct(
        public bool $runComposer = false,
        public bool $runNpmBuild = false,
        public ?int $logId = null,
    ) {}

    public function handle(): void
    {
        $start = microtime(true);
        $this->currentLog = $this->resolveLog();

        Log::channel('commands')->info('Starting project update job...', ['log_id' => $this->currentLog->id]);

        try {
            if (! $this->gitPull()) {
                $this->failJob('Git pull failed', $start);

                return;
            }

            if ($this->runComposer) {
                $this->runComposerUpdate();
            }

            $this->runMigrations();
            $this->clearCache();
            $this->clearRoute();
            $this->clearView();

            if ($this->runNpmBuild) {
                $this->addTheme();
            }

            $this->restartQueue();

            $duration = (int) ((microtime(true) - $start) * 1000);
            $this->currentLog->update([
                'status' => 'success',
                'execution_time_ms' => $duration,
                'status_code' => 0,
            ]);
            Log::channel('commands')->info('Project update job completed successfully.', ['duration_ms' => $duration]);
        } catch (Throwable $e) {
            $this->failJob($e->getMessage(), $start);
        }
    }

    protected function resolveLog(): CommandLog
    {
        if ($this->logId) {
            $log = CommandLog::find($this->logId);

            if ($log) {
                $log->update([
                    'status' => 'running',
                    'parameters' => [
                        'runComposer' => $this->runComposer,
                        'runNpmBuild' => $this->runNpmBuild,
                    ],
                ]);

                return $log;
            }
        }

        return CommandLog::create([
            'command' => 'UpdateProjectJob',
            'parameters' => [
                'runComposer' => $this->runComposer,
                'runNpmBuild' => $this->runNpmBuild,
            ],
            'status' => 'running',
        ]);
    }

    protected function failJob(string $message, ?float $startTime = null): void
    {
        $duration = $startTime ? (int) ((microtime(true) - $startTime) * 1000) : null;
        $this->currentLog?->update([
            'status' => 'failed',
            'output' => SystemCommandGuard::stripAnsi(($this->currentLog->output ?? '')."\nFATAL ERROR: ".$message),
            'status_code' => 1,
            'execution_time_ms' => $duration,
        ]);
        Log::channel('commands')->error('Project update job failed: '.$message);
    }

    protected function gitPull(): bool
    {
        Log::channel('commands')->info('Executing git pull...');

        $process = Process::forever()
            ->path(base_path())
            ->run('git pull');

        $this->appendToOutput('Git Pull:'."\n".($process->successful() ? $process->output() : $process->errorOutput()));

        return $process->successful();
    }

    protected function runMigrations(): void
    {
        $this->runArtisan('migrate', ['--force' => true]);
    }

    protected function runComposerUpdate(): void
    {
        Log::channel('commands')->info('Running composer install...');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->composerCommand().' install --no-dev --optimize-autoloader --no-interaction');

            $this->appendToOutput('Composer Install:'."\n".($process->successful() ? $process->output() : $process->errorOutput()));
        } catch (Throwable $exception) {
            $this->appendToOutput('Composer Install Exception: '.$exception->getMessage());
        }
    }

    protected function clearCache(): void
    {
        $this->runArtisan('cache:clear');
    }

    protected function clearRoute(): void
    {
        $this->runArtisan('route:clear');
    }

    protected function clearView(): void
    {
        $this->runArtisan('view:clear');
    }

    protected function addTheme(): void
    {
        Log::channel('commands')->info('Building theme assets...');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->npmCommand().' run build');

            $this->appendToOutput('NPM Build:'."\n".($process->successful() ? $process->output() : $process->errorOutput()));
        } catch (Throwable $exception) {
            $this->appendToOutput('NPM Build Exception: '.$exception->getMessage());
        }
    }

    protected function restartQueue(): void
    {
        $this->runArtisan('queue:restart');
    }

    protected function runArtisan(string $command, array $parameters = []): void
    {
        Log::channel('commands')->info("Running artisan {$command}...");

        Artisan::call($command, $parameters);
        $output = SystemCommandGuard::stripAnsi(Artisan::output());

        $this->appendToOutput("Artisan {$command}:"."\n".$output);
    }

    protected function appendToOutput(string $text): void
    {
        $this->currentLog?->update([
            'output' => SystemCommandGuard::stripAnsi(($this->currentLog->output ?? '')."\n".$text),
        ]);
        $this->currentLog?->refresh();
    }

    protected function npmCommand(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
    }

    protected function composerCommand(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'composer.bat' : 'composer';
    }
}
