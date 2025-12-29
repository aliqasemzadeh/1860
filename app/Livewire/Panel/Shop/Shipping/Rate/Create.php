<?php

namespace App\Livewire\Panel\Shop\Shipping\Rate;

use App\Models\Shop\ShippingMethod;
use App\Models\Shop\ShippingRate;
use App\Models\Shop\ShippingZone;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public ?int $shipping_method_id = null;

    public ?int $shipping_zone_id = null;

    public string $rate_type = 'flat';

    public float $amount = 0;

    public ?float $min_weight = null;

    public ?float $max_weight = null;

    public ?float $min_price = null;

    public ?float $max_price = null;

    public ?string $estimated_days = null;

    public bool $is_active = true;

    public function create(): void
    {
        $validated = $this->validate([
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'shipping_zone_id' => ['required', 'integer', 'exists:shipping_zones,id'],
            'rate_type' => ['required', 'in:flat,weight,price'],
            'amount' => ['required', 'numeric', 'min:0'],
            'min_weight' => ['nullable', 'numeric', 'min:0'],
            'max_weight' => ['nullable', 'numeric', 'min:0'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        ShippingRate::create($validated);

        Flux::modal('panel.shop.shipping.rate.create.modal')->close();
        $this->dispatch('panel.shop.shipping.rate.index.render');
        Flux::toast(variant: 'success', text: __('app.shipping_rate_created'));

        $this->reset([
            'shipping_method_id',
            'shipping_zone_id',
            'rate_type',
            'amount',
            'min_weight',
            'max_weight',
            'min_price',
            'max_price',
            'estimated_days',
            'is_active',
        ]);
        $this->rate_type = 'flat';
        $this->is_active = true;
    }

    public function render(): View
    {
        $methods = ShippingMethod::query()->orderBy('name')->get(['id', 'name']);
        $zones = ShippingZone::query()->orderBy('name')->get(['id', 'name']);

        return view('livewire.panel.shop.shipping.rate.create', compact('methods', 'zones'));
    }
}
