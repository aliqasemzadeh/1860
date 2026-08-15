<?php

namespace App\Livewire\Panel\Administrator\SettingManagement\Function;

use App\Jobs\System\RunArtisanCommandJob;
use App\Jobs\System\UpdateProjectJob;
use App\Models\System\CommandLog;
use App\Services\Shop\SitemapService;
use App\Support\SystemCommandGuard;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $lastCommand = '';

    public string $lastOutput = '';

    public int $executionDuration = 0;

    public int $lastStatus = 0;

    public string $search = '';

    public ?int $selectedLogId = null;

    public ?int $activeLogId = null;

    public function mount(): void
    {
        $this->authorize('administrator_setting_function_index');
    }

    /**
     * @return array<string, array{name: string, description: string, signature: string, category: string, icon: string, mode: string, job?: string}>
     */
    protected function commandCatalog(): array
    {
        return [
            'cache_clear' => [
                'name' => __('general.cmd_cache_clear_title'),
                'description' => __('general.cmd_cache_clear_desc'),
                'signature' => 'cache:clear',
                'category' => __('general.category_cache'),
                'icon' => 'trash',
                'mode' => 'sync',
            ],
            'optimize' => [
                'name' => __('general.cmd_optimize_title'),
                'description' => __('general.cmd_optimize_desc'),
                'signature' => 'optimize',
                'category' => __('general.category_cache'),
                'icon' => 'zap',
                'mode' => 'sync',
            ],
            'optimize_clear' => [
                'name' => __('general.cmd_optimize_clear_title'),
                'description' => __('general.cmd_optimize_clear_desc'),
                'signature' => 'optimize:clear',
                'category' => __('general.category_cache'),
                'icon' => 'rotate-cw',
                'mode' => 'sync',
            ],
            'config_cache' => [
                'name' => __('general.cmd_config_cache_title'),
                'description' => __('general.cmd_config_cache_desc'),
                'signature' => 'config:cache',
                'category' => __('general.category_cache'),
                'icon' => 'settings',
                'mode' => 'sync',
            ],
            'route_cache' => [
                'name' => __('general.cmd_route_cache_title'),
                'description' => __('general.cmd_route_cache_desc'),
                'signature' => 'route:cache',
                'category' => __('general.category_cache'),
                'icon' => 'link',
                'mode' => 'sync',
            ],
            'view_cache' => [
                'name' => __('general.cmd_view_cache_title'),
                'description' => __('general.cmd_view_cache_desc'),
                'signature' => 'view:cache',
                'category' => __('general.category_cache'),
                'icon' => 'eye',
                'mode' => 'sync',
            ],
            'create_permissions' => [
                'name' => __('general.update_permissions'),
                'description' => __('general.cmd_permissions_desc'),
                'signature' => 'system:administrator:create-permissions-command',
                'category' => __('general.category_system'),
                'icon' => 'shield-check',
                'mode' => 'sync',
            ],
            'create_roles' => [
                'name' => __('general.cmd_roles_title'),
                'description' => __('general.cmd_roles_desc'),
                'signature' => 'system:administrator:create-roles-command',
                'category' => __('general.category_system'),
                'icon' => 'shield-check',
                'mode' => 'sync',
            ],
            'rebuild_sitemap' => [
                'name' => __('general.rebuild_sitemap'),
                'description' => __('general.cmd_sitemap_desc'),
                'signature' => 'SitemapService::refresh',
                'category' => __('general.category_system'),
                'icon' => 'network',
                'mode' => 'service',
                'handler' => 'rebuildSitemap',
            ],
            'add_watermarks' => [
                'name' => __('general.add_watermarks'),
                'description' => __('general.cmd_watermarks_desc'),
                'signature' => 'app:add-water-mark-to-images-command',
                'category' => __('general.category_media'),
                'icon' => 'image',
                'mode' => 'queue',
            ],
            'optimize_images' => [
                'name' => __('general.optimize_images'),
                'description' => __('general.cmd_optimize_images_desc'),
                'signature' => 'app:optimize-images-command',
                'category' => __('general.category_media'),
                'icon' => 'sparkles',
                'mode' => 'queue',
            ],
            'about' => [
                'name' => __('general.cmd_about_title'),
                'description' => __('general.cmd_about_desc'),
                'signature' => 'about',
                'category' => __('general.category_info'),
                'icon' => 'circle-gauge',
                'mode' => 'sync',
            ],
            'schedule_list' => [
                'name' => __('general.cmd_schedule_list_title'),
                'description' => __('general.cmd_schedule_list_desc'),
                'signature' => 'schedule:list',
                'category' => __('general.category_info'),
                'icon' => 'chart-bar-stacked',
                'mode' => 'sync',
            ],
            'queue_failed' => [
                'name' => __('general.cmd_queue_failed_title'),
                'description' => __('general.cmd_queue_failed_desc'),
                'signature' => 'queue:failed',
                'category' => __('general.category_maintenance'),
                'icon' => 'triangle-alert',
                'mode' => 'sync',
            ],
            'update_quick' => [
                'name' => __('general.quick_update'),
                'description' => __('general.are_you_sure'),
                'signature' => 'Job: UpdateProject(Quick)',
                'category' => __('general.category_maintenance'),
                'icon' => 'play',
                'mode' => 'queue',
                'job' => 'update_quick',
            ],
            'update_full' => [
                'name' => __('general.full_update'),
                'description' => __('general.are_you_sure'),
                'signature' => 'Job: UpdateProject(Full)',
                'category' => __('general.category_maintenance'),
                'icon' => 'play',
                'mode' => 'queue',
                'job' => 'update_full',
            ],
        ];
    }

    #[Computed]
    public function commands(): array
    {
        $catalog = $this->commandCatalog();

        if ($this->search === '') {
            return $catalog;
        }

        return array_filter($catalog, function (array $command): bool {
            $needle = mb_strtolower($this->search);

            return str_contains(mb_strtolower($command['name']), $needle)
                || str_contains(mb_strtolower($command['signature']), $needle)
                || str_contains(mb_strtolower($command['category']), $needle);
        });
    }

    public function runCommand(string $key): void
    {
        $this->authorize('administrator_setting_function_index');

        $catalog = $this->commandCatalog();

        if (! array_key_exists($key, $catalog)) {
            Flux::toast(__('general.error'), variant: 'danger');

            return;
        }

        $command = $catalog[$key];
        $displayCommand = match ($command['mode'] ?? 'sync') {
            'queue' => ($command['job'] ?? null)
                ? $command['signature']
                : 'php artisan '.$command['signature'],
            'service' => $command['signature'],
            default => 'php artisan '.$command['signature'],
        };

        $mode = $command['mode'] ?? 'sync';

        if ($mode === 'sync' && ! SystemCommandGuard::isAllowed($command['signature'])) {
            Flux::toast(__('general.command_not_allowed'), variant: 'danger');

            return;
        }

        $start = microtime(true);
        $log = null;

        try {
            $log = CommandLog::create([
                'command' => $displayCommand,
                'status' => 'running',
                'output' => __('general.command_queued'),
            ]);

            $this->activeLogId = $log->id;
            $this->lastCommand = $displayCommand;

            if ($mode === 'queue') {
                $this->dispatchQueuedCommand($command, $log->id);

                $this->lastStatus = 0;
                $this->lastOutput = __('general.command_queued');
                $this->executionDuration = (int) ((microtime(true) - $start) * 1000);

                $log->update([
                    'output' => $this->lastOutput,
                    'status_code' => 0,
                    'execution_time_ms' => $this->executionDuration,
                    'status' => 'running',
                ]);

                unset($this->logs, $this->hasRunningLogs, $this->selectedLog);
                Flux::toast(__('general.update_dispatched'));

                return;
            }

            if ($mode === 'service') {
                $handler = $command['handler'] ?? null;

                if (! is_string($handler) || ! method_exists($this, $handler)) {
                    throw new \RuntimeException(__('general.command_not_allowed'));
                }

                $output = (string) $this->{$handler}();
                $statusCode = 0;
            } else {
                $statusCode = Artisan::call($command['signature']);
                $output = SystemCommandGuard::stripAnsi(Artisan::output());
            }

            $duration = (int) ((microtime(true) - $start) * 1000);

            $this->lastStatus = $statusCode;
            $this->lastOutput = $output !== '' ? $output : __('general.command_completed_no_output');
            $this->executionDuration = $duration;

            $log->update([
                'output' => $this->lastOutput,
                'status_code' => $statusCode,
                'execution_time_ms' => $duration,
                'status' => $statusCode === 0 ? 'success' : 'failed',
            ]);

            $this->activeLogId = null;
            unset($this->logs, $this->hasRunningLogs, $this->selectedLog);

            Flux::toast(
                variant: $statusCode === 0 ? 'success' : 'danger',
                text: $statusCode === 0 ? __('general.success') : __('general.error'),
            );
        } catch (\Throwable $e) {
            $this->lastStatus = 1;
            $this->lastOutput = $e->getMessage();
            $this->lastCommand = $displayCommand;
            $this->executionDuration = (int) ((microtime(true) - $start) * 1000);
            $this->activeLogId = null;

            $log?->update([
                'output' => $this->lastOutput,
                'status_code' => 1,
                'execution_time_ms' => $this->executionDuration,
                'status' => 'failed',
            ]);

            unset($this->logs, $this->hasRunningLogs, $this->selectedLog);
            Flux::toast(__('general.error'), variant: 'danger');
        }
    }

    protected function rebuildSitemap(): string
    {
        $urls = app(SitemapService::class)->refresh();

        return __('general.sitemap_rebuilt', ['count' => count($urls)]);
    }

    /**
     * @param  array{signature: string, job?: string}  $command
     */
    protected function dispatchQueuedCommand(array $command, int $logId): void
    {
        if (($command['job'] ?? null) === 'update_quick') {
            UpdateProjectJob::dispatch(runComposer: false, runNpmBuild: false, logId: $logId);

            return;
        }

        if (($command['job'] ?? null) === 'update_full') {
            UpdateProjectJob::dispatch(runComposer: true, runNpmBuild: true, logId: $logId);

            return;
        }

        if (! SystemCommandGuard::isAllowed($command['signature'])) {
            throw new \RuntimeException(__('general.command_not_allowed'));
        }

        RunArtisanCommandJob::dispatch($command['signature'], [], $logId);
    }

    public function refreshActiveLog(): void
    {
        if ($this->activeLogId) {
            $log = CommandLog::find($this->activeLogId);

            if (! $log) {
                $this->activeLogId = null;
            } else {
                $this->lastCommand = $log->command;
                $this->lastOutput = SystemCommandGuard::stripAnsi($log->output);
                $this->lastStatus = (int) $log->status_code;
                $this->executionDuration = (int) ($log->execution_time_ms ?? 0);

                if (in_array($log->status, ['success', 'failed'], true)) {
                    $this->activeLogId = null;
                }
            }
        }

        unset($this->logs, $this->hasRunningLogs, $this->selectedLog);
    }

    public function viewLog(int $id): void
    {
        $this->authorize('administrator_setting_function_index');

        $log = CommandLog::findOrFail($id);

        $this->selectedLogId = $log->id;
        $this->lastCommand = $log->command;
        $this->lastOutput = SystemCommandGuard::stripAnsi($log->output);
        $this->lastStatus = (int) $log->status_code;
        $this->executionDuration = (int) ($log->execution_time_ms ?? 0);

        unset($this->selectedLog);
        Flux::modal('panels.administrator.setting-management.function.command-log.detail')->show();
    }

    public function clearConsole(): void
    {
        $this->lastCommand = '';
        $this->lastOutput = '';
        $this->executionDuration = 0;
        $this->lastStatus = 0;
        $this->activeLogId = null;
    }

    #[Computed]
    public function logs(): LengthAwarePaginator
    {
        return CommandLog::query()
            ->latest()
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function hasRunningLogs(): bool
    {
        return CommandLog::query()->where('status', 'running')->exists();
    }

    #[Computed]
    public function selectedLog(): ?CommandLog
    {
        if (! $this->selectedLogId) {
            return null;
        }

        return CommandLog::find($this->selectedLogId);
    }

    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.panel.administrator.setting-management.function.index');
    }
}
