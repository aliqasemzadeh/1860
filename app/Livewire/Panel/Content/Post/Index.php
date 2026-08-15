<?php

namespace App\Livewire\Panel\Content\Post;

use App\Models\Content\Post;
use App\Services\Shop\SitemapService;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected const SORTABLE = ['title', 'status', 'published_at', 'created_at'];

    public string $search = '';

    public string $statusFilter = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(): void
    {
        $this->authorize('content_post_index');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        $sortBy = in_array($this->sortBy, self::SORTABLE, true) ? $this->sortBy : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Post::query()
            ->with('tags')
            ->withCount('products')
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('slug', 'like', $search);
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderBy($sortBy, $sortDirection)
            ->paginate(10);
    }

    public function delete(int $id): void
    {
        $this->authorize('content_post_delete');

        $post = Post::query()->with('products:id')->find($id);

        if ($post === null) {
            return;
        }

        $productIds = $post->products->pluck('id')->map(fn ($id) => (int) $id)->all();

        $post->delete();

        foreach ($productIds as $productId) {
            Cache::forget("product.{$productId}.related_posts");
        }

        app(SitemapService::class)->forget();

        $this->dispatch('panel.content.post.index.render');
        Flux::toast(variant: 'success', text: __('general.post_deleted'));
    }

    #[Layout('layouts.panels.content')]
    #[On('panel.content.post.index.render')]
    public function render()
    {
        $this->authorize('content_post_index');

        return view('livewire.panel.content.post.index');
    }
}
