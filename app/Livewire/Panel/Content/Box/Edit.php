<?php

namespace App\Livewire\Panel\Content\Box;

use App\Livewire\Forms\Content\BoxForm;
use App\Models\Content\Box;
use App\Models\Shop\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
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

    #[On('panels.administrator.content.box.edit.assign-data')]
    public function assignData(int $id)
    {
        $box = Box::query()->findOrFail($id);
        $this->form->setModel($box);
        \Flux::modal('content.box.edit')->show();
    }

    public function save()
    {
        $this->form->update();
        $this->dispatch('panels.administrator.content.box.index.table');
        \Flux::modals()->close();
        \Flux::toast(__('general.updated_successfully'));
    }

    public function render()
    {
        return view('livewire.panel.content.box.edit');
    }
}
