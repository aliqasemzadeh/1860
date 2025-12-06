<?php

namespace App\Livewire\Shop\SettingManagement\Warranty;

use App\Models\Shop\Warranty;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public Warranty $warranty;

    public int $id;

    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    #[On('shop.warranty.edit.assign-data')]
    public function assignData($id): void
    {
        $this->warranty = Warranty::findOrFail($id);
        $this->id = $this->warranty->id;
        $this->name = (string) $this->warranty->name;
        $this->slug = (string) $this->warranty->slug;
        $this->slug_fa = (string) $this->warranty->slug_fa;
        Flux::modal('shop.warranty.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->warranty)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('warranties', 'slug')->ignore($this->warranty)],
            'slug_fa' => ['required', 'string', 'max:255', Rule::unique('warranties', 'slug_fa')->ignore($this->warranty)],
        ]);

        $this->warranty->fill($validated)->save();

        $this->dispatch('shop.warranty.index.render');
        Flux::modal('shop.warranty.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.shop.warranty.edit');
    }
}
