<?php

namespace App\Livewire\Main\Product;

use App\Models\Shop\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;

class View extends Component
{
    public $id = null;
    public $slug = null;

    public function mount($id = null, $slug = null)
    {
        // Handle route parameters - if we have both, prioritize slug
        if ($slug) {
            $this->slug = $slug;
        } elseif ($id) {
            $this->id = $id;
        }
    }

    #[Computed]
    public function product()
    {
        $query = Product::query()
            ->with(['category', 'brand', 'unit', 'colors', 'warranties', 'prices.color', 'prices.warranty']);

        if ($this->slug) {
            $query->where(function ($q) {
                $q->where('slug', $this->slug)->orWhere('slug_fa', $this->slug);
            });
        } elseif ($this->id) {
            $query->where('id', $this->id);
        } else {
            return null;
        }

        return $query->first();
    }

    public function render()
    {
        if (!$this->product) {
            abort(404);
        }

        return view('livewire.main.product.view');
    }
}
