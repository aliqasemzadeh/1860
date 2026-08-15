<?php

namespace App\Livewire\Panel\Content\Box;

use App\Models\Content\Box;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    #[Computed]
    #[On('panels.administrator.content.box.index.table')]
    public function boxes()
    {
        return Box::query()
            ->when($this->search, fn($q) => $q->where('title_fa', 'like', "%{$this->search}%")->orWhere('title_en', 'like', "%{$this->search}%"))
            ->ordered()
            ->paginate(config('general.per_page'));
    }

    public function delete(int $id)
    {
        Box::query()->findOrFail($id)->delete();
        \Flux::toast(__('general.deleted_successfully'));
        $this->dispatch('panels.administrator.content.box.index.table');
    }

    public function render()
    {
        return view('livewire.panel.content.box.index');
    }
}
