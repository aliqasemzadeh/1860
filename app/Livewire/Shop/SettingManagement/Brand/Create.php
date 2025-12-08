<?php

namespace App\Livewire\Shop\SettingManagement\Brand;

use App\Models\Shop\Brand;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:brands,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:brands,slug_fa'],
        ]);

        $brand = Brand::create($validated);

        Flux::modal('shop.setting-management.brand.create.modal')->close();
        $this->dispatch('shop.setting-management.brand.index.render');
        \Illuminate\Support\Facades\Log::info("Brand Created:",[$brand->id]);
        $this->dispatch('shop.product.brand.refresh', ['id' => $brand->id]);
        Flux::toast(variant: 'success', text: __('app.brand_created', ['name' => $validated['name']]));
        $this->reset(['name', 'slug', 'slug_fa']);
    }

    public function render(): View
    {
        return view('livewire.shop.setting-management.brand.create');
    }
}
