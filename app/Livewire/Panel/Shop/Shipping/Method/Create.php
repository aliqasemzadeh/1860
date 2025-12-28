<?php

namespace App\Livewire\Panel\Shop\Shipping\Method;

use App\Models\Shop\ShippingMethod;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $handle = '';

    public ?string $description = null;

    public bool $is_active = true;

    #[On('panel.shop.shipping.method.create.open')]
    public function open(): void
    {
        Flux::modal('shop.shipping.method.create.modal')->show();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:shipping_methods,handle'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        ShippingMethod::create($validated);

        Flux::modal('shop.shipping.method.create.modal')->close();
        $this->dispatch('shop.shipping.method.index.render');
        Flux::toast(variant: 'success', text: __('app.shipping_method_created'));

        $this->reset(['name', 'handle', 'description', 'is_active']);
        $this->is_active = true;
    }

    public function render(): View
    {
        return view('livewire.panel.shop.shipping.method.create');
    }
}
