<?php

namespace App\Livewire\Panel\Shop\Shipping\Zone;

use App\Models\Shop\ShippingZone;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ShippingZone $zone;

    public int $id;

    public string $name = '';

    public string $countries = '';

    /**
     * Selected provinces (استان‌ها) as an array of IDs.
     *
     * @var array<int>
     */
    public array $states = [];

    /**
     * Selected cities (شهرها) as an array of city keys (e.g., '100001', '100002').
     *
     * @var array<string>
     */
    public array $cities = [];

    /**
     * Optional areas / postal code prefixes for finer granularity.
     * Stored as array in DB, edited as textarea (line by line).
     */
    public string $areas = '';

    /**
     * City options based on selected provinces.
     * Structure: [city_key => city_name] (flattened from all selected provinces)
     *
     * @var array<string,string>
     */
    public array $cityOptions = [];

    public function updatedStates(): void
    {
        $citiesByProvince = (array) __('cities');

        // جمع‌آوری شهرها بر اساس کد استان‌های انتخاب شده
        $cityOptions = [];
        foreach ($this->states as $provinceId) {
            $provinceId = (int) $provinceId;
            if (isset($citiesByProvince[$provinceId]) && is_array($citiesByProvince[$provinceId])) {
                // Merge cities from all selected provinces
                foreach ($citiesByProvince[$provinceId] as $cityKey => $cityData) {
                    $cityOptions[$cityKey] = is_array($cityData) ? ($cityData['name'] ?? $cityKey) : $cityData;
                }
            }
        }

        // Sort by city name for better UX
        asort($cityOptions);
        $this->cityOptions = $cityOptions;

        // Keep only selected cities that are still valid for the selected provinces
        $this->cities = array_values(array_intersect($this->cities, array_keys($cityOptions)));
    }

    #[On('panel.shop.shipping.zone.edit.assign-data')]
    public function assignData($id): void
    {
        $this->zone = ShippingZone::findOrFail($id);

        $this->id = $this->zone->id;
        $this->name = (string) $this->zone->name;
        $this->countries = implode("\n", (array) $this->zone->countries);

        // Convert states to integers (handle both old string names and new integer IDs)
        $states = (array) $this->zone->states;
        $provinces = (array) __('provinces');
        $convertedStates = [];
        foreach ($states as $state) {
            if (is_numeric($state)) {
                $convertedStates[] = (int) $state;
            } else {
                // Legacy: convert name to ID
                foreach ($provinces as $provinceId => $provinceName) {
                    if ($provinceName === $state) {
                        $convertedStates[] = (int) $provinceId;
                        break;
                    }
                }
            }
        }
        $this->states = array_values($convertedStates);

        // Convert cities: handle both old format (arrays with province_id and city_index) and new format (city keys)
        $cities = (array) $this->zone->cities;
        $convertedCities = [];
        $citiesByProvince = (array) __('cities');

        foreach ($cities as $city) {
            if (is_array($city) && isset($city['province_id']) && isset($city['city_index'])) {
                // Old format: [province_id, city_index] - convert to city key
                $provinceId = (int) $city['province_id'];
                $cityIndex = (int) $city['city_index'];
                if (isset($citiesByProvince[$provinceId]) && is_array($citiesByProvince[$provinceId])) {
                    $provinceCities = $citiesByProvince[$provinceId];
                    $cityKeys = array_keys($provinceCities);
                    if (isset($cityKeys[$cityIndex])) {
                        $convertedCities[] = $cityKeys[$cityIndex];
                    }
                }
            } elseif (is_string($city) && preg_match('/^\d{6}$/', $city)) {
                // New format: city key (e.g., '100001')
                $convertedCities[] = $city;
            }
        }
        $this->cities = array_values($convertedCities);
        $this->areas = implode("\n", (array) $this->zone->areas);
        $this->updatedStates();

        Flux::modal('panel.shop.shipping.zone.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->zone)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'countries' => ['required', 'string'],
            'states' => ['nullable', 'array'],
            'cities' => ['nullable', 'array'],
            'areas' => ['nullable', 'string'],
        ]);

        // Convert states to integers (province IDs)
        $states = array_map('intval', $validated['states'] ?? []);

        // Cities are already city keys (e.g., '100001', '100002')
        $cities = array_values(array_filter($validated['cities'] ?? []));

        $this->zone->update([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_values($states),
            'cities' => $cities,
            'areas' => array_filter(array_map('trim', explode("\n", (string) ($validated['areas'] ?? '')))),
        ]);

        $this->dispatch('panel.shop.shipping.zone.index.render');
        Flux::modal('panel.shop.shipping.zone.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('general.shipping_zone_updated'));
    }

    public function render(): View
    {
        $provinces = (array) __('provinces'); // Keep as [id => name] for display
        $cityOptions = $this->cityOptions;

        return view('livewire.panel.shop.shipping.zone.edit', compact('provinces', 'cityOptions'));
    }
}
