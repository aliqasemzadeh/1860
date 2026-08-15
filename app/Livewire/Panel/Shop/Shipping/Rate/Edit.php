<?php

namespace App\Livewire\Panel\Shop\Shipping\Rate;

use App\Models\Shop\ShippingMethod;
use App\Models\Shop\ShippingRate;
use App\Models\Shop\ShippingZone;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ShippingRate $rate;

    public int $id;

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

    #[On('panel.shop.shipping.rate.edit.assign-data')]
    public function assignData($id): void
    {
        $this->rate = ShippingRate::with(['method', 'zone'])->findOrFail($id);

        $this->id = $this->rate->id;
        $this->shipping_method_id = $this->rate->shipping_method_id;
        $this->shipping_zone_id = $this->rate->shipping_zone_id;
        $this->rate_type = (string) $this->rate->rate_type;
        $this->amount = (float) $this->rate->amount;
        $this->min_weight = $this->rate->min_weight !== null ? (float) $this->rate->min_weight : null;
        $this->max_weight = $this->rate->max_weight !== null ? (float) $this->rate->max_weight : null;
        $this->min_price = $this->rate->min_price !== null ? (float) $this->rate->min_price : null;
        $this->max_price = $this->rate->max_price !== null ? (float) $this->rate->max_price : null;
        $this->estimated_days = $this->rate->estimated_days;
        $this->is_active = (bool) $this->rate->is_active;

        Flux::modal('panel.shop.shipping.rate.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->rate)) {
            return;
        }

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

        $this->rate->fill($validated)->save();

        $this->dispatch('panel.shop.shipping.rate.index.render');
        Flux::modal('panel.shop.shipping.rate.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('general.shipping_rate_updated'));
    }

    public function render(): View
    {
        $methods = ShippingMethod::query()->orderBy('name')->get(['id', 'name']);
        $zones = ShippingZone::query()->orderBy('name')->get(['id', 'name']);

        return view('livewire.panel.shop.shipping.rate.edit', compact('methods', 'zones'));
    }
}
