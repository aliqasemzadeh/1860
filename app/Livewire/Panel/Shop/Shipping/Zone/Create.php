<?php

namespace App\Livewire\Panel\Shop\Shipping\Zone;

use App\Models\Shop\ShippingZone;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $countries = 'IR';

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

    public function create(): void
    {
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

        ShippingZone::create([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_values($states),
            'cities' => $cities,
            'areas' => array_filter(array_map('trim', explode("\n", (string) ($validated['areas'] ?? '')))),
        ]);

        Flux::modal('panel.shop.shipping.zone.create.modal')->close();
        $this->dispatch('panel.shop.shipping.zone.index.render');
        Flux::toast(variant: 'success', text: __('general.shipping_zone_created'));

        $this->reset(['name', 'countries', 'states', 'cities', 'areas', 'cityOptions']);
        $this->countries = 'IR';
    }

    public function render(): View
    {
        $provinces = (array) __('provinces'); // Keep as [id => name] for display

        return view('livewire.panel.shop.shipping.zone.create', compact('provinces'));
    }
}
