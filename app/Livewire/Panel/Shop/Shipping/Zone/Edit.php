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
     * Selected provinces (استان‌ها) as an array of names.
     *
     * @var array<int,string>
     */
    public array $states = [];

    /**
     * Selected cities (شهرها) as an array of names.
     *
     * @var array<int,string>
     */
    public array $cities = [];

    /**
     * Optional areas / postal code prefixes for finer granularity.
     * Stored as array in DB, edited as textarea (line by line).
     */
    public string $areas = '';

    /**
     * City options based on selected provinces.
     *
     * @var array<int,string>
     */
    public array $cityOptions = [];

    public function updatedStates(): void
    {
        $provinces = (array) __('provinces');
        $citiesByProvince = (array) __('cities');

        $selectedProvinceIds = [];
        foreach ($provinces as $id => $name) {
            if (in_array($name, $this->states, true)) {
                $selectedProvinceIds[] = (int) $id;
            }
        }

        $cities = [];
        foreach ($selectedProvinceIds as $provinceId) {
            if (isset($citiesByProvince[$provinceId]) && is_array($citiesByProvince[$provinceId])) {
                $cities = array_merge($cities, $citiesByProvince[$provinceId]);
            }
        }

        $cities = array_values(array_unique($cities));
        sort($cities, SORT_NATURAL);

        $this->cityOptions = $cities;
    }

    #[On('shop.shipping.zone.edit.assign-data')]
    public function assignData($id): void
    {
        $this->zone = ShippingZone::findOrFail($id);

        $this->id = $this->zone->id;
        $this->name = (string) $this->zone->name;
        $this->countries = implode("\n", (array) $this->zone->countries);
        $this->states = array_values((array) $this->zone->states);
        $this->cities = array_values((array) $this->zone->cities);
        $this->areas = implode("\n", (array) $this->zone->areas);
        $this->updatedStates();

        Flux::modal('shop.shipping.zone.edit.modal')->show();
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

        $this->zone->update([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_values($validated['states'] ?? []),
            'cities' => array_values($validated['cities'] ?? []),
            'areas' => array_filter(array_map('trim', explode("\n", (string) ($validated['areas'] ?? '')))),
        ]);

        $this->dispatch('shop.shipping.zone.index.render');
        Flux::modal('shop.shipping.zone.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.shipping_zone_updated'));
    }

    public function render(): View
    {
        $provinces = array_values((array) __('provinces'));
        $cityOptions = $this->cityOptions;

        return view('livewire.panel.shop.shipping.zone.edit', compact('provinces', 'cityOptions'));
    }
}
