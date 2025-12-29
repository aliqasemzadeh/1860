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

        // پیدا کردن کد استان‌ها بر اساس نام انتخاب‌شده
        $selectedProvinceIds = [];
        foreach ($provinces as $id => $name) {
            if (in_array($name, $this->states, true)) {
                $selectedProvinceIds[] = (int) $id;
            }
        }

        // جمع‌آوری شهرها بر اساس کد استان‌ها
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

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'countries' => ['required', 'string'],
            'states' => ['nullable', 'array'],
            'cities' => ['nullable', 'array'],
            'areas' => ['nullable', 'string'],
        ]);

        ShippingZone::create([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_values($validated['states'] ?? []),
            'cities' => array_values($validated['cities'] ?? []),
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
        $provinces = array_values((array) __('provinces'));

        return view('livewire.panel.shop.shipping.zone.create', compact('provinces'));
    }
}
