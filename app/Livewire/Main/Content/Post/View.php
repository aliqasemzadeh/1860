<?php

namespace App\Livewire\Main\Content\Post;

use App\Models\Content\Post;
use App\Support\Seo\Seo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class View extends Component
{
    public ?string $slug = null;

    public function mount(?string $slug = null): void
    {
        $this->slug = $slug;

        if (! $this->post) {
            abort(404);
        }
    }

    #[Computed]
    public function post(): ?Post
    {
        if (! $this->slug) {
            return null;
        }

        return Post::query()
            ->published()
            ->with(['tags', 'products' => fn ($q) => $q->select('products.*')->active()->withEffectivePrice()])
            ->where('slug', $this->slug)
            ->first();
    }

    #[Computed]
    public function seo(): Seo
    {
        $post = $this->post;

        return new Seo(
            title: $post?->title ?: Seo::siteName(),
            description: $post?->summary ?: Seo::siteName(),
            canonical: $post?->url,
            image: $post?->featured_image_url,
            type: 'article',
        );
    }

    #[Layout('layouts.app')]
    public function render()
    {
        if (! $this->post) {
            abort(404);
        }

        return view('livewire.main.content.post.view');
    }
}
