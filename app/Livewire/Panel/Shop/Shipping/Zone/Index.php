<?php

namespace App\Livewire\Panel\Shop\Shipping\Zone;

use App\Models\Shop\ShippingZone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $zone = ShippingZone::query()->find($id);
        if ($zone !== null) {
            $zone->delete();
        }
    }

    #[Computed]
    public function zones(): LengthAwarePaginator
    {
        return ShippingZone::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
            ->paginate(10);
    }

    /**
     * Get province name by ID.
     */
    public function getProvinceName($provinceId): string
    {
        $provinces = (array) __('provinces');
        return $provinces[$provinceId] ?? (string) $provinceId;
    }

    /**
     * Get city name by province ID and city index.
     */
    public function getCityName($provinceId, $cityIndex): string
    {
        $citiesByProvince = (array) __('cities');
        if (isset($citiesByProvince[$provinceId]) && is_array($citiesByProvince[$provinceId])) {
            $cities = $citiesByProvince[$provinceId];
            return $cities[$cityIndex] ?? (string) $cityIndex;
        }
        return (string) $cityIndex;
    }

    /**
     * Format states for display.
     */
    public function formatStates($zone): string
    {
        $states = (array) $zone->states;
        if (empty($states)) {
            return __('app.all');
        }

        $names = [];
        foreach ($states as $state) {
            if (is_numeric($state)) {
                $names[] = $this->getProvinceName((int) $state);
            } else {
                $names[] = $state; // Legacy: already a name
            }
        }

        return implode(', ', $names);
    }

    /**
     * Format cities for display.
     */
    public function formatCities($zone): string
    {
        $cities = (array) $zone->cities;
        if (empty($cities)) {
            return __('app.all');
        }

        $names = [];
        foreach ($cities as $city) {
            if (is_array($city) && isset($city['province_id']) && isset($city['city_index'])) {
                // New format
                $names[] = $this->getCityName((int) $city['province_id'], (int) $city['city_index']);
            } elseif (is_numeric($city)) {
                // Old format: just index (can't determine province, show as-is)
                $names[] = (string) $city;
            } else {
                $names[] = (string) $city; // Legacy: already a name
            }
        }

        return implode(', ', $names);
    }

    #[Layout('layouts.panels.shop')]
    public function render()
    {
        return view('livewire.panel.shop.shipping.zone.index');
    }
}
