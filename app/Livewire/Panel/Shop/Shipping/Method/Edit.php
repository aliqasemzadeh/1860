<?php

namespace App\Livewire\Panel\Shop\Shipping\Method;

use App\Models\Shop\ShippingMethod;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ShippingMethod $method;

    public int $id;

    public string $name = '';

    public string $handle = '';

    public ?string $description = null;

    public bool $is_active = true;

    #[On('panel.shop.shipping.method.edit.assign-data')]
    public function assignData($id): void
    {
        $this->method = ShippingMethod::findOrFail($id);

        $this->id = $this->method->id;
        $this->name = (string) $this->method->name;
        $this->handle = (string) $this->method->handle;
        $this->description = $this->method->description;
        $this->is_active = (bool) $this->method->is_active;

        Flux::modal('panel.shop.shipping.method.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->method)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('shipping_methods', 'handle')->ignore($this->method)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $this->method->fill($validated)->save();

        $this->dispatch('panel.shop.shipping.method.index.render');
        Flux::modal('panel.shop.shipping.method.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.shipping_method_updated'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.shipping.method.edit');
    }
}
