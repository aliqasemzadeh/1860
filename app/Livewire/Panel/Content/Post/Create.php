<?php

namespace App\Livewire\Panel\Content\Post;

use App\Livewire\Forms\Panel\Content\PostForm;
use App\Models\Shop\Product;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public PostForm $form;

    #[Validate('nullable|image|max:5120')]
    public $featured_file = null;

    public string $product_search = '';

    public function updatedFormTitle(string $value): void
    {
        $this->form->slug = Str::slug($value);
    }

    #[Computed]
    public function products(): Collection
    {
        $selectedIds = collect($this->form->product_ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        $query = Product::query()
            ->select(['id', 'name'])
            ->when($this->product_search, function ($q) {
                $search = '%'.$this->product_search.'%';
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('en_name', 'like', $search)
                        ->orWhere('slug', 'like', $search);
                });
            })
            ->orderBy('name')
            ->limit(20);

        $products = $query->get();

        if ($selectedIds !== []) {
            $missing = Product::query()
                ->select(['id', 'name'])
                ->whereIn('id', $selectedIds)
                ->whereNotIn('id', $products->pluck('id'))
                ->get();

            $products = $products->concat($missing)->unique('id')->values();
        }

        return $products;
    }

    public function removeFeaturedFile(): void
    {
        if ($this->featured_file) {
            $this->featured_file->delete();
        }

        $this->featured_file = null;
        Flux::toast(variant: 'success', text: __('app.file_removed'));
    }

    public function create(): void
    {
        $this->authorize('content_post_create');
        $this->validateOnly('featured_file');

        if ($this->featured_file) {
            $this->form->featured_image = $this->featured_file->store('posts', 'public');
        }

        $this->form->store();

        Flux::modal('panel.content.post.create.modal')->close();
        $this->dispatch('panel.content.post.index.render');
        Flux::toast(variant: 'success', text: __('app.post_created'));

        $this->form->reset();
        $this->reset(['featured_file', 'product_search']);
        unset($this->products);
    }

    public function render(): View
    {
        return view('livewire.panel.content.post.create');
    }
}
