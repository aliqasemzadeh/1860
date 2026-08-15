<?php

namespace App\Livewire\Panel\Shop\SettingManagement\Category;

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

    public int $main_category_id = 0;

    public function updatedName(string $value): void
    {
        $this->slug = \Illuminate\Support\Str::slug($value);
        $this->slug_fa = slug_fa($value);
    }

    #[On('panel.shop.setting-management.category.edit.assign-data')]
    public function assignData($id): void
    {
        $this->category = Category::findOrFail($id);
        $this->id = $this->category->id;
        $this->name = (string) $this->category->name;
        $this->slug = (string) $this->category->slug;
        $this->slug_fa = (string) $this->category->slug_fa;
        $this->icon = $this->category->icon;
        $this->sort_order = (int) $this->category->sort_order;
        $this->main_category_id = (int) $this->category->main_category_id;
        Flux::modal('panel.shop.setting-management.category.edit.modal')->show();
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
            'main_category_id' => ['required', 'integer', 'min:0'],
        ]);

        // Prevent assigning itself as parent
        if ((int) $validated['main_category_id'] === (int) $this->category->id) {
            $this->addError('main_category_id', __('general.parent_cannot_be_self'));

            return;
        }

        // Enforce one-level hierarchy: chosen parent must be a root (main_category_id = 0)
        if ((int) $validated['main_category_id'] !== 0) {
            $parent = Category::query()->find($validated['main_category_id']);
            if ($parent === null || (int) $parent->main_category_id !== 0) {
                $this->addError('main_category_id', __('general.parent_must_be_root'));

                return;
            }
        }

        $this->category->fill($validated)->save();

        $this->dispatch('panel.shop.setting-management.category.index.render');
        Flux::modal('panel.shop.setting-management.category.edit.modal')->close();
    }

    public function render(): View
    {
        $roots = Category::query()
            ->where('main_category_id', 0)
            ->where('id', '!=', $this->id ?? 0)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.panel.shop.setting-management.category.edit', compact('roots'));
    }
}
