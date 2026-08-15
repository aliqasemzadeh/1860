<?php

namespace App\Livewire\Main\Content\Box;

use App\Models\Content\Box;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class View extends Component
{
    use WithPagination;

    public $id;
    public $slug;

    public function mount($id, $slug = null)
    {
        $this->id = $id;
        $this->slug = $slug;
    }

    #[Computed]
    public function box()
    {
        return Box::query()->findOrFail($this->id);
    }

    #[Computed]
    public function products()
    {
        return $this->box->products()->select('products.*')->withEffectivePrice()->paginate(12, pageName: 'products-page');
    }

    #[Computed]
    public function articles()
    {
        return $this->box->posts()->paginate(10, pageName: 'articles-page');
    }

    public function render()
    {
        return view('livewire.main.content.box.view');
    }
}
