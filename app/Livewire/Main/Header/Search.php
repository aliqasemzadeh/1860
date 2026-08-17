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

    public function mount(): void
    {
        $this->loadSearchHistory();
    }

    protected function loadSearchHistory(): void
    {
        $this->recentSearches = session('search_history', []);
    }

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
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            })
            ->orderByAvailability()
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function rememberSearch(): void
    {
        $term = trim($this->query);

        if (mb_strlen($term) < 2) {
            return;
        }

        $history = collect(session('search_history', []))
            ->reject(fn ($item) => $item === $term)
            ->prepend($term)
            ->take(10)
            ->values()
            ->all();

        session(['search_history' => $history]);
        $this->recentSearches = $history;
    }

    public function goToProduct(int $productId): void
    {
        $this->rememberSearch();
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
        session()->forget('search_history');
        $this->recentSearches = [];
    }

    public function handleSearchClose(): void
    {
        $this->query = '';
        $this->loadSearchHistory();
        unset($this->products);
    }

    public function render()
    {
        return view('livewire.main.header.search');
    }
}
