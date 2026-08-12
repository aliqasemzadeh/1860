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

    public function addWatermarks(): void
    {
        $this->authorize('administrator_setting_function_index');

        Artisan::call('app:add-water-mark-to-images-command');
        $output = Artisan::output();

        if (preg_match(
            '/RESULT products_marked=(\d+) products_skipped=(\d+) product_images_marked=(\d+) product_images_skipped=(\d+)/',
            $output,
            $matches
        )) {
            $marked = (int) $matches[1] + (int) $matches[3];
            $skipped = (int) $matches[2] + (int) $matches[4];

            Flux::toast(__('app.watermarks_added', [
                'marked' => $marked,
                'skipped' => $skipped,
            ]));

            return;
        }

        Flux::toast(__('app.watermarks_added_generic'));
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
