<?php

namespace App\Providers;

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
    }
}
