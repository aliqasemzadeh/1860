<?php

namespace App\Livewire\Shop\SettingManagement\Category;

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

    public int $main_category_id = 0;

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:categories,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:categories,slug_fa'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'main_category_id' => ['required', 'integer', 'min:0'],
        ]);

        // Enforce one-level hierarchy: if a parent is selected, it must be a root (main_category_id = 0)
        if ((int) $validated['main_category_id'] !== 0) {
            $parent = Category::query()->find($validated['main_category_id']);
            if ($parent === null || (int) $parent->main_category_id !== 0) {
                $this->addError('main_category_id', __('app.parent_must_be_root'));

                return;
            }
        }

        Category::create($validated);

        Flux::modal('shop.category.create.modal')->close();
        $this->dispatch('shop.category.index.render');
        $this->reset(['name', 'slug', 'slug_fa', 'icon', 'sort_order', 'main_category_id']);
    }

    public function render(): View
    {
        $roots = Category::query()->where('main_category_id', 0)->orderBy('name')->get(['id', 'name']);

        return view('livewire.shop.category.create', compact('roots'));
    }
}
