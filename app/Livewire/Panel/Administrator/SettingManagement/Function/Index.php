<?php

namespace App\Livewire\Panel\Administrator\SettingManagement\Function;

use App\Jobs\System\UpdateProjectJob;
use App\Services\Shop\SitemapService;
use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('administrator_setting_function_index');
    }

    public function updatePermissions(): void
    {
        $this->authorize('administrator_setting_function_index');

        Artisan::call('system:administrator:create-permissions-command');
        Flux::toast(__('app.permissions_updated'));
    }

    public function clearCache(): void
    {
        $this->authorize('administrator_setting_function_index');

        Artisan::call('cache:clear');
        Flux::toast(__('app.cache_cleared'));
    }

    public function rebuildSitemap(): void
    {
        $this->authorize('administrator_setting_function_index');

        $urls = app(SitemapService::class)->refresh();
        Flux::toast(__('app.sitemap_rebuilt', ['count' => count($urls)]));
    }

    public function updateQuick(): void
    {
        $this->authorize('administrator_setting_function_update');

        UpdateProjectJob::dispatch(runComposer: false, runNpmBuild: false);
        Flux::toast(__('app.update_dispatched'));
    }

    public function updateFull(): void
    {
        $this->authorize('administrator_setting_function_update');

        UpdateProjectJob::dispatch(runComposer: true, runNpmBuild: true);
        Flux::toast(__('app.update_dispatched'));
    }

    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.panel.administrator.setting-management.function.index');
    }
}
