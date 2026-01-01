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
     * Selected cities (شهرها) as an array of indices.
     *
     * @var array<int>
     */
    public array $cities = [];

    /**
     * Optional areas / postal code prefixes for finer granularity.
     * Stored as array in DB, edited as textarea (line by line).
     */
    public string $areas = '';

    /**
     * City options based on selected provinces.
     * Structure: [province_id => [city_index => city_name]]
     *
     * @var array<int,array<int,string>>
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
                $cityOptions[$provinceId] = $citiesByProvince[$provinceId];
            }
        }

        $this->cityOptions = $cityOptions;
        
        // Reset cities when provinces change
        $this->cities = [];
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
        
        // Convert cities: handle both old format (integers) and new format (arrays with province_id and city_index)
        $cities = (array) $this->zone->cities;
        $convertedCities = [];
        foreach ($cities as $city) {
            if (is_array($city) && isset($city['province_id']) && isset($city['city_index'])) {
                // New format: [province_id, city_index]
                $convertedCities[] = $city['province_id'] . ':' . $city['city_index'];
            } elseif (is_numeric($city)) {
                // Old format: just integer (legacy, will need province context - skip for now)
                // This is problematic, but we'll skip legacy integer cities
            }
        }
        $this->cities = $convertedCities;
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
        
        // Convert cities: format is "province_id:city_index", store as array of [province_id, city_index] pairs
        $cities = [];
        foreach ($validated['cities'] ?? [] as $cityValue) {
            if (str_contains($cityValue, ':')) {
                [$provinceId, $cityIndex] = explode(':', $cityValue, 2);
                $cities[] = [
                    'province_id' => (int) $provinceId,
                    'city_index' => (int) $cityIndex,
                ];
            }
        }

        $this->zone->update([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_values($states),
            'cities' => $cities,
            'areas' => array_filter(array_map('trim', explode("\n", (string) ($validated['areas'] ?? '')))),
        ]);

        $this->dispatch('panel.shop.shipping.zone.index.render');
        Flux::modal('panel.shop.shipping.zone.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.shipping_zone_updated'));
    }

    public function render(): View
    {
        $provinces = (array) __('provinces'); // Keep as [id => name] for display
        $cityOptions = $this->cityOptions;

        return view('livewire.panel.shop.shipping.zone.edit', compact('provinces', 'cityOptions'));
    }
}
