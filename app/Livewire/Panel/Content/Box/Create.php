<?php

namespace App\Livewire\Panel\Content\Box;

use App\Livewire\Forms\Content\BoxForm;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public BoxForm $form;

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
