<?php

namespace App\Livewire\Panel\Accounting\Bank\Remittance;

use App\Models\Accounting\BankRemittance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

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
    public function remittances()
    {
        return BankRemittance::query()
            ->with(['bank', 'user'])
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('bank', function ($bankQuery) use ($search) {
                            $bankQuery->where('name', 'like', $search);
                        });
                });
            })
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete($id): void
    {
        $this->authorize('accounting_bank_remittance_delete');

        $remittance = BankRemittance::findOrFail($id);
        $remittance->delete();

        $this->dispatch('accounting.bank.remittance.index.render');
    }

    #[Layout('layouts.panels.accounting')]
    #[On('accounting.bank.remittance.index.render')]
    public function render()
    {
        $this->authorize('accounting_bank_remittance_index');

        return view('livewire.panel.accounting.bank.remittance.index');
    }
}
