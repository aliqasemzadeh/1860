<?php

namespace App\Livewire\Shop\SettingManagement\Brand;

use App\Models\Shop\Brand as BrandModel;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public BrandModel $brand;

    public int $id;

    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    #[On('shop.brand.edit.assign-data')]
    public function assignData($id): void
    {
        $this->brand = BrandModel::findOrFail($id);
        $this->id = $this->brand->id;
        $this->name = (string) $this->brand->name;
        $this->slug = (string) $this->brand->slug;
        $this->slug_fa = (string) $this->brand->slug_fa;
        Flux::modal('shop.brand.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->brand)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('brands', 'slug')->ignore($this->brand)],
            'slug_fa' => ['required', 'string', 'max:255', Rule::unique('brands', 'slug_fa')->ignore($this->brand)],
        ]);

        $this->brand->fill($validated)->save();

        $this->dispatch('shop.brand.index.render');
        Flux::modal('shop.brand.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.shop.brand.edit');
    }
}
