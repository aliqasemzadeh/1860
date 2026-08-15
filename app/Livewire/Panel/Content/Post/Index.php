<?php

namespace App\Livewire\Panel\Content\Post;

use App\Models\Content\Post;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

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
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function delete(int $id): void
    {
        $this->authorize('content_post_delete');

        $post = Post::query()->find($id);

        if ($post === null) {
            return;
        }

        $post->delete();

        $this->dispatch('panel.content.post.index.render');
        Flux::toast(variant: 'success', text: __('app.post_deleted'));
    }

    #[Layout('layouts.panels.content')]
    #[On('panel.content.post.index.render')]
    public function render()
    {
        $this->authorize('content_post_index');

        return view('livewire.panel.content.post.index');
    }
}
