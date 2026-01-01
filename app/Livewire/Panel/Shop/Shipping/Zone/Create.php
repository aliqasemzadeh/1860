<?php

namespace App\Livewire\Panel\Shop\Shipping\Zone;

use App\Models\Shop\ShippingZone;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $countries = "IR";

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

        ShippingZone::create([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_values($states),
            'cities' => $cities,
            'areas' => array_filter(array_map('trim', explode("\n", (string) ($validated['areas'] ?? '')))),
        ]);

        Flux::modal('panel.shop.shipping.zone.create.modal')->close();
        $this->dispatch('panel.shop.shipping.zone.index.render');
        Flux::toast(variant: 'success', text: __('app.shipping_zone_created'));

        $this->reset(['name', 'countries', 'states', 'cities', 'areas', 'cityOptions']);
        $this->countries = "IR";
    }

    public function render(): View
    {
        $provinces = (array) __('provinces'); // Keep as [id => name] for display

        return view('livewire.panel.shop.shipping.zone.create', compact('provinces'));
    }
}
