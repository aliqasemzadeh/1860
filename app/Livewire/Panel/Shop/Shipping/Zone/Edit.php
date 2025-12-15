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

    public string $states = '';

    public string $cities = '';

    #[On('shop.shipping.zone.edit.assign-data')]
    public function assignData($id): void
    {
        $this->zone = ShippingZone::findOrFail($id);

        $this->id = $this->zone->id;
        $this->name = (string) $this->zone->name;
        $this->countries = implode("\n", (array) $this->zone->countries);
        $this->states = implode("\n", (array) $this->zone->states);
        $this->cities = implode("\n", (array) $this->zone->cities);

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
            'states' => ['nullable', 'string'],
            'cities' => ['nullable', 'string'],
        ]);

        $this->zone->update([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_filter(array_map('trim', explode("\n", (string) $validated['states']))),
            'cities' => array_filter(array_map('trim', explode("\n", (string) $validated['cities']))),
        ]);

        $this->dispatch('shop.shipping.zone.index.render');
        Flux::modal('shop.shipping.zone.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.shipping_zone_updated'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.shipping.zone.edit');
    }
}
