<?php

namespace App\Livewire\Shop\Category;

use App\Models\Shop\Category;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    public ?string $icon = null;

    public int $sort_order = 1;

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:categories,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:categories,slug_fa'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        Category::create($validated);

        Flux::modal('shop.category.create.modal')->close();
        $this->reset(['name', 'slug', 'slug_fa', 'icon', 'sort_order']);
    }

    public function render(): View
    {
        return view('livewire.shop.category.create');
    }
}
