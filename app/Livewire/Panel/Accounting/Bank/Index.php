<?php

namespace App\Livewire\Panel\Accounting\Bank;

use App\Models\Accounting\Bank;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'sort_order';

    public $sortDirection = 'asc';

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[\Livewire\Attributes\Computed]
    public function banks()
    {
        return Bank::query()
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', $search)
                        ->orWhere('name', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(100);
    }

    #[\Livewire\Attributes\Computed]
    public function totalBalance()
    {
        return Bank::query()
            ->get()
            ->sum(fn ($bank) => $bank->calculateBalance());
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete($id): void
    {
        $this->authorize('accounting_bank_delete');

        $bank = Bank::findOrFail($id);
        $bank->delete();

        Flux::toast(variant: 'success', text: __('app.bank_deleted'));
        $this->dispatch('accounting.bank.index.render');
    }

    #[Layout('layouts.panels.accounting')]
    #[On('accounting.bank.index.render')]
    public function render()
    {
        $this->authorize('accounting_bank_index');

        return view('livewire.panel.accounting.bank.index');
    }
}
