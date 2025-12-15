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

    public string $states = '';

    public string $cities = '';

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'countries' => ['required', 'string'],
            'states' => ['nullable', 'string'],
            'cities' => ['nullable', 'string'],
        ]);

        ShippingZone::create([
            'name' => $validated['name'],
            'countries' => array_filter(array_map('trim', explode("\n", $validated['countries']))),
            'states' => array_filter(array_map('trim', explode("\n", (string) $validated['states']))),
            'cities' => array_filter(array_map('trim', explode("\n", (string) $validated['cities']))),
        ]);

        Flux::modal('shop.shipping.zone.create.modal')->close();
        $this->dispatch('shop.shipping.zone.index.render');
        Flux::toast(variant: 'success', text: __('app.shipping_zone_created'));

        $this->reset(['name', 'countries', 'states', 'cities']);
        $this->countries = "IR";
    }

    public function render(): View
    {
        return view('livewire.panel.shop.shipping.zone.create');
    }
}
