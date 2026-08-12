<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;

trait HasProvinceCityOptions
{
    #[Computed]
    public function provinces(): array
    {
        return Cache::rememberForever('geo.provinces', function () {
            return require lang_path('fa/provinces.php');
        });
    }

    #[Computed]
    public function cities(): array
    {
        $provinceId = $this->form->province_id ?? null;

        if (! $provinceId) {
            return [];
        }

        return Cache::rememberForever('geo.cities.'.$provinceId, function () use ($provinceId) {
            $allCities = require lang_path('fa/cities.php');
            $provinceCities = $allCities[$provinceId] ?? [];

            $result = [];
            foreach (array_keys($provinceCities) as $originalKey) {
                $result[(string) $originalKey] = $provinceCities[$originalKey]['name'] ?? $provinceCities[$originalKey];
            }

            return $result;
        });
    }
}
