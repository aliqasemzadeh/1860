<?php

namespace App\Livewire\Panel\Administrator\SettingManagement\Function;

use App\Jobs\System\UpdateProjectJob;
use App\Jobs\System\RunArtisanCommandJob;
use App\Models\System\CommandLog;
use App\Services\Shop\SitemapService;
use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\On;

class Index extends Component
{
    public string $lastCommand = '';
    public string $lastOutput = '';
    public int $executionDuration = 0;
    public int $lastStatus = 0;
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('administrator_setting_function_index');
    }

    #[Computed]
    public function commands(): array
    {
        $allCommands = [
            'cache_clear' => [
                'name' => __('general.cmd_cache_clear_title'),
                'description' => __('general.cmd_cache_clear_desc'),
                'signature' => 'cache:clear',
                'category' => __('general.category_cache'),
                'icon' => 'trash',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('cache:clear');
                    return __('general.update_dispatched');
                },
            ],
            'optimize' => [
                'name' => __('general.cmd_optimize_title'),
                'description' => __('general.cmd_optimize_desc'),
                'signature' => 'optimize',
                'category' => __('general.category_cache'),
                'icon' => 'zap',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('optimize');
                    return __('general.update_dispatched');
                },
            ],
            'optimize_clear' => [
                'name' => __('general.cmd_optimize_clear_title'),
                'description' => __('general.cmd_optimize_clear_desc'),
                'signature' => 'optimize:clear',
                'category' => __('general.category_cache'),
                'icon' => 'rotate-cw',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('optimize:clear');
                    return __('general.update_dispatched');
                },
            ],
            'config_cache' => [
                'name' => __('general.cmd_config_cache_title'),
                'description' => __('general.cmd_config_cache_desc'),
                'signature' => 'config:cache',
                'category' => __('general.category_cache'),
                'icon' => 'settings',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('config:cache');
                    return __('general.update_dispatched');
                },
            ],
            'route_cache' => [
                'name' => __('general.cmd_route_cache_title'),
                'description' => __('general.cmd_route_cache_desc'),
                'signature' => 'route:cache',
                'category' => __('general.category_cache'),
                'icon' => 'link',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('route:cache');
                    return __('general.update_dispatched');
                },
            ],
            'view_cache' => [
                'name' => __('general.cmd_view_cache_title'),
                'description' => __('general.cmd_view_cache_desc'),
                'signature' => 'view:cache',
                'category' => __('general.category_cache'),
                'icon' => 'eye',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('view:cache');
                    return __('general.update_dispatched');
                },
            ],
            'create_permissions' => [
                'name' => __('general.update_permissions'),
                'description' => __('general.cmd_permissions_desc'),
                'signature' => 'system:administrator:create-permissions-command',
                'category' => __('general.category_system'),
                'icon' => 'shield-check',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('system:administrator:create-permissions-command');
                    return __('general.update_dispatched');
                },
            ],
            'create_roles' => [
                'name' => __('general.cmd_roles_title'),
                'description' => __('general.cmd_roles_desc'),
                'signature' => 'system:administrator:create-roles-command',
                'category' => __('general.category_system'),
                'icon' => 'shield-check',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('system:administrator:create-roles-command');
                    return __('general.update_dispatched');
                },
            ],
            'rebuild_sitemap' => [
                'name' => __('general.rebuild_sitemap'),
                'description' => __('general.cmd_sitemap_desc'),
                'signature' => 'sitemap:refresh',
                'category' => __('general.category_system'),
                'icon' => 'network',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('sitemap:refresh');
                    return __('general.update_dispatched');
                },
            ],
            'add_watermarks' => [
                'name' => __('general.add_watermarks'),
                'description' => __('general.cmd_watermarks_desc'),
                'signature' => 'app:add-water-mark-to-images-command',
                'category' => __('general.category_media'),
                'icon' => 'image',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('app:add-water-mark-to-images-command');
                    return __('general.update_dispatched');
                },
            ],
            'optimize_images' => [
                'name' => __('general.optimize_images'),
                'description' => __('general.cmd_optimize_images_desc'),
                'signature' => 'app:optimize-images-command',
                'category' => __('general.category_media'),
                'icon' => 'sparkles',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('app:optimize-images-command');
                    return __('general.update_dispatched');
                },
            ],
            'about' => [
                'name' => __('general.cmd_about_title'),
                'description' => __('general.cmd_about_desc'),
                'signature' => 'about',
                'category' => __('general.category_info'),
                'icon' => 'circle-gauge',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('about');
                    return __('general.update_dispatched');
                },
            ],
            'schedule_list' => [
                'name' => __('general.cmd_schedule_list_title'),
                'description' => __('general.cmd_schedule_list_desc'),
                'signature' => 'schedule:list',
                'category' => __('general.category_info'),
                'icon' => 'chart-bar-stacked',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('schedule:list');
                    return __('general.update_dispatched');
                },
            ],
            'queue_failed' => [
                'name' => __('general.cmd_queue_failed_title'),
                'description' => __('general.cmd_queue_failed_desc'),
                'signature' => 'queue:failed',
                'category' => __('general.category_maintenance'),
                'icon' => 'triangle-alert',
                'action' => function () {
                    RunArtisanCommandJob::dispatch('queue:failed');
                    return __('general.update_dispatched');
                },
            ],
            'update_quick' => [
                'name' => __('general.quick_update'),
                'description' => __('general.are_you_sure'),
                'signature' => 'Job: UpdateProject(Quick)',
                'category' => __('general.category_maintenance'),
                'icon' => 'play',
                'action' => function () {
                    UpdateProjectJob::dispatch(runComposer: false, runNpmBuild: false);
                    return __('general.update_dispatched');
                },
            ],
            'update_full' => [
                'name' => __('general.full_update'),
                'description' => __('general.are_you_sure'),
                'signature' => 'Job: UpdateProject(Full)',
                'category' => __('general.category_maintenance'),
                'icon' => 'play',
                'action' => function () {
                    UpdateProjectJob::dispatch(runComposer: true, runNpmBuild: true);
                    return __('general.update_dispatched');
                },
            ],
        ];

        if (empty($this->search)) {
            return [];
        }

        return array_filter($allCommands, function ($command) {
            return str_contains(strtolower($command['name']), strtolower($this->search)) ||
                   str_contains(strtolower($command['signature']), strtolower($this->search)) ||
                   str_contains(strtolower($command['category']), strtolower($this->search));
        });
    }

    public function runCommand(string $key): void
    {
        $this->authorize('administrator_setting_function_index');

        if (! array_key_exists($key, $this->commands)) {
            Flux::toast(__('general.error'), variant: 'danger');
            return;
        }

        $command = $this->commands[$key];
        $start = microtime(true);

        $log = CommandLog::create([
            'command' => 'php artisan ' . $command['signature'],
            'status' => 'running',
        ]);

        try {
            $result = $command['action']();

            $this->lastStatus = is_int($result) ? $result : 0;
            $this->lastOutput = is_string($result) ? $result : Artisan::output();
            $this->lastCommand = 'php artisan ' . $command['signature'];
            $this->executionDuration = (int) ((microtime(true) - $start) * 1000);

            $log->update([
                'output' => $this->lastOutput,
                'status_code' => $this->lastStatus,
                'execution_time_ms' => $this->executionDuration,
                'status' => $this->lastStatus === 0 ? 'success' : 'failed',
            ]);

            Flux::toast(__('general.success'));
        } catch (\Exception $e) {
            $this->lastStatus = 1;
            $this->lastOutput = $e->getMessage();
            $this->lastCommand = 'php artisan ' . $command['signature'];
            $this->executionDuration = (int) ((microtime(true) - $start) * 1000);

            $log->update([
                'output' => $this->lastOutput,
                'status_code' => $this->lastStatus,
                'execution_time_ms' => $this->executionDuration,
                'status' => 'failed',
            ]);

            Flux::toast(__('general.error'), variant: 'danger');
        }
    }

    public function clearConsole(): void
    {
        $this->lastCommand = '';
        $this->lastOutput = '';
        $this->executionDuration = 0;
        $this->lastStatus = 0;
    }

    #[Computed]
    public function recentLogs()
    {
        return CommandLog::latest()->take(10)->get();
    }

    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.panel.administrator.setting-management.function.index');
    }
}
