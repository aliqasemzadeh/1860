<?php

namespace App\Livewire\Main\Content\Tag;

use App\Models\Content\Post;
use App\Support\Seo\Seo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Tags\Tag;

class View extends Component
{
    use WithPagination;

    public ?int $id = null;

    public ?string $slug = null;

    public function mount($id = null, $slug = null)
    {
        $this->id = $id !== null ? (int) $id : null;
        $this->slug = $slug;

        $tag = $this->tag;

        if (! $tag) {
            abort(404);
        }

        $canonicalSlug = (string) $tag->slug;

        if ($this->slug === null || $this->slug !== $canonicalSlug) {
            return redirect()->to($this->tagUrl($tag), 301);
        }
    }

    #[Computed]
    public function tag(): ?Tag
    {
        if (! $this->id) {
            return null;
        }

        return Tag::query()->find($this->id);
    }

    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        return Post::query()
            ->withAnyTags([$this->tag])
            ->published()
            ->with('tags')
            ->orderByDesc('published_at')
            ->paginate(12);
    }

    #[Computed]
    public function seo(): Seo
    {
        $tag = $this->tag;
        $title = $tag ? __('general.posts_with_tag', ['tag' => $tag->name]) : Seo::siteName();

        return new Seo(
            title: $title,
            description: $title,
            canonical: $tag ? $this->tagUrl($tag) : null,
        );
    }

    protected function tagUrl(Tag $tag): string
    {
        return route('tag.view', [
            'id' => $tag->id,
            'slug' => (string) $tag->slug,
        ]);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        if (! $this->tag) {
            abort(404);
        }

        return view('livewire.main.content.tag.view');
    }
}
