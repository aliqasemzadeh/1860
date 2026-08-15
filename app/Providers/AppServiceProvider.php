<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('jalali', fn ($expression) => "<?php echo jalali({$expression}); ?>");

        Gate::before(function ($user, $ability) {
            if ($user === null) {
                return null;
            }

            foreach (config('permission.unrestricted_prefixes', []) as $prefix) {
                if (str_starts_with($ability, $prefix.'_')) {
                    return true;
                }
            }

            return null;
        });

        Gate::define('viewLogViewer', function ($user = null) {
            if ($user === null) {
                return app()->environment('local');
            }

            return $user->can('administrator_access')
                || $user->hasRole('administrator')
                || app()->environment('local');
        });
    }
}
