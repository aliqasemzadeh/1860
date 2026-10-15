<?php

namespace App\Livewire\Main\Dashboard;

use App\Models\Content\Post;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    #[Computed(cache: true, key: 'home.categories')]
    public function categories(): Collection
    {
        return Category::query()
            ->where('main_category_id', 0)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function discountedProducts(): Collection
    {
        return Product::query()
            ->active()
            ->whereAvailability(true)
            ->whereDiscounted()
            ->withEffectivePrice()
            ->orderByDesc('created_at')
            ->take(12)
            ->get();
    }

    #[Computed]
    public function newestProducts(): Collection
    {
        return Product::query()
            ->active()
            ->whereAvailability(true)
            ->withEffectivePrice()
            ->orderByDesc('created_at')
            ->take(12)
            ->get();
    }

    /**
     * Exactly two editorial articles for the magazine-style section above the footer.
     */
    #[Computed]
    public function latestPosts(): Collection
    {
        return Post::query()
            ->published()
            ->with('tags')
            ->orderByDesc('published_at')
            ->take(2)
            ->get();
    }

    /**
     * Hero promo visuals: the freshest discounted product and the most recent article.
     */
    #[Computed]
    public function heroOfferProduct(): ?Product
    {
        return $this->discountedProducts->first();
    }

    #[Computed]
    public function heroPost(): ?Post
    {
        return $this->latestPosts->first();
    }

    public function render()
    {
        return view('livewire.main.dashboard.index');
    }
}
