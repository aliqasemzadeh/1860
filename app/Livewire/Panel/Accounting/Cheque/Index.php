<?php

namespace App\Livewire\Panel\Accounting\Cheque;

use App\Models\Accounting\Cheque;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'due_at';

    public string $sortDirection = 'asc';

    public string $search = '';

    public ?string $date = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'date' => ['except' => ''],
    ];

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Validate and normalize the Jalali date filter whenever it changes.
     */
    public function updatedDate($value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $value)) {
            $this->date = null;
            Flux::toast(variant: 'danger', text: __('app.invalid_date'));

            return;
        }

        try {
            $jDate = Jalalian::fromFormat('Y/m/d', $value);
            $this->date = $jDate->format('Y/m/d');
        } catch (\Throwable $e) {
            $this->date = null;
            Flux::toast(variant: 'danger', text: __('app.invalid_date'));
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function cheques()
    {
        return Cheque::query()
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when($this->date, function ($query) {
                try {
                    $carbonDate = Jalalian::fromFormat('Y/m/d', $this->date)->toCarbon()->toDateString();

                    $query->whereDate('due_at', $carbonDate);
                } catch (\Throwable $e) {
                    // Ignore invalid filter
                }
            })
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    #[Computed]
    public function totalAmount()
    {
        return Cheque::query()->sum('amount');
    }

    public function delete($id): void
    {
        $this->authorize('accounting_cheque_delete');

        $cheque = Cheque::findOrFail($id);
        $cheque->delete();

        Flux::toast(variant: 'success', text: __('app.cheque_deleted'));
        $this->dispatch('accounting.cheque.index.render');
    }

    #[Layout('layouts.panels.accounting')]
    #[On('accounting.cheque.index.render')]
    public function render()
    {
        $this->authorize('accounting_cheque_index');

        return view('livewire.panel.accounting.cheque.index');
    }
}
