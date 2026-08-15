<?php

namespace App\Livewire\Main\Content\Post;

use App\Models\Content\Post;
use App\Support\Seo\Seo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->with('tags')
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('summary', 'like', $search);
                });
            })
            ->orderByDesc('published_at')
            ->paginate(12);
    }

    #[Computed]
    public function seo(): Seo
    {
        return new Seo(
            title: __('app.blog'),
            description: __('app.blog_description'),
            canonical: route('post.index'),
        );
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.main.content.post.index');
    }
}
