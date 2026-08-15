<?php

namespace App\Livewire\Panel\Content\Box;

use App\Livewire\Forms\Content\BoxForm;
use App\Models\Shop\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public BoxForm $form;
    public string $search = '';

    #[Computed]
    public function products()
    {
        return Product::query()
            ->select('products.id', 'products.name')
            ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->form->product_ids, fn($query) => $query->orWhereIn('products.id', $this->form->product_ids))
            ->limit(20)
            ->get();
    }

    public function save()
    {
        $this->form->store();
        $this->dispatch('panels.administrator.content.box.index.table');
        \Flux::modals()->close();
        \Flux::toast(__('general.created_successfully'));
    }

    public function render()
    {
        return view('livewire.panel.content.box.create');
    }
}
