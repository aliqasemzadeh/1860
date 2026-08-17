<?php

namespace App\Livewire\Main\Header;

use App\Models\Shop\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Search extends Component
{
    public string $query = '';

    /** @var array<int, string> */
    public array $recentSearches = [];

    #[Computed]
    public function products()
    {
        $searchTerm = trim($this->query);

        if (empty($searchTerm)) {
            return collect([]);
        }

        return Product::query()
            ->with(['category', 'prices' => function ($query) {
                $query->orderByDesc('is_default')
                    ->orderByDesc('created_at');
            }])
            ->search($searchTerm)
            ->orderByAvailability()
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function goToSearchPage(): void
    {
        $term = trim($this->query);

        if (strlen($term) < 2) {
            return;
        }

        $this->redirect(route('search.index', ['q' => $term]), navigate: true);
    }

    public function syncSearchHistory(array $history): void
    {
        $this->recentSearches = collect($history)
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->take(10)
            ->values()
            ->all();
    }

    public function goToProduct(int $productId): void
    {
        $this->query = '';
        unset($this->products);

        $product = Product::query()->findOrFail($productId);

        $this->redirect($product->url, navigate: true);
    }

    public function selectHistory(string $term): void
    {
        $this->query = $term;
        unset($this->products);
    }

    public function clearSearchHistory(): void
    {
        $this->recentSearches = [];
    }

    public function handleSearchClose(): void
    {
        $this->query = '';
        unset($this->products);
    }

    public function render()
    {
        return view('livewire.main.header.search');
    }
}
