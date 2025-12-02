<?php

namespace App\Livewire\Administrator\Shop\Category;

use App\Models\Shop\Category;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public Category $category;

    public int $id;

    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    public ?string $icon = null;

    public int $sort_order = 1;

    #[On('administrator.shop.category.edit.assign-data')]
    public function assignData($id): void
    {
        $this->category = Category::findOrFail($id);
        $this->id = $this->category->id;
        $this->name = (string) $this->category->name;
        $this->slug = (string) $this->category->slug;
        $this->slug_fa = (string) $this->category->slug_fa;
        $this->icon = $this->category->icon;
        $this->sort_order = (int) $this->category->sort_order;
        Flux::modal('administrator.shop.category.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->category)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($this->category)],
            'slug_fa' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug_fa')->ignore($this->category)],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $this->category->fill($validated)->save();

        $this->dispatch('pg:eventRefresh-administrator.shop.category.table');
        Flux::modal('administrator.shop.category.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.administrator.shop.category.edit');
    }
}
