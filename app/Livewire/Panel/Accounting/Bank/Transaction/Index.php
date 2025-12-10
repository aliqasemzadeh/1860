<?php

namespace App\Livewire\Panel\Accounting\Bank\Transaction;

use App\Enums\TransactionTypeEnum;
use App\Models\Accounting\Bank;
use App\Models\Accounting\BankTransaction;
use Flux\Flux;
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

    public string $typeFilter = '';

    public string $bankFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'bankFilter' => ['except' => ''],
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
    public function transactions()
    {
        return BankTransaction::query()
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
            ->when($this->typeFilter, function ($query) {
                $query->where('linker', $this->typeFilter);
            })
            ->when($this->bankFilter, function ($query) {
                $query->where('bank_id', $this->bankFilter);
            })
            ->when(!$this->sortBy, fn ($query) => $query->orderBy('created_at', 'desc'))
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBankFilter(): void
    {
        $this->resetPage();
    }

    public function delete($id): void
    {
        $this->authorize('accounting_bank_transaction_delete');

        $transaction = BankTransaction::findOrFail($id);
        $transaction->delete();

        Flux::toast(variant: 'success', text: __('app.transaction_deleted'));
        $this->dispatch('accounting.bank.transaction.index.render');
    }

    #[Layout('layouts.panels.accounting')]
    #[On('accounting.bank.transaction.index.render')]
    #[\Livewire\Attributes\Computed]
    public function banks()
    {
        return Bank::orderBy('sort_order')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function transactionTypes()
    {
        return config('accounting.transaction_type', []);
    }

    public function render()
    {
        $this->authorize('accounting_bank_transaction_index');

        return view('livewire.panel.accounting.bank.transaction.index');
    }
}
