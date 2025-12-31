<?php

namespace App\Livewire\Main\Header;

use App\Models\Shop\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Search extends Component
{
    public string $query = '';

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
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.main.header.search');
    }
}
