<?php

namespace App\Livewire\Panel\Accounting\Remittance;

use App\Models\Accounting\Remittance;
use Flux\Flux;
use Morilog\Jalali\Jalalian;
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

    public ?string $date = null;

    /**
     * Inline payment values keyed by remittance id.
     *
     * @var array<int, string>
     */
    public array $payments = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'date' => ['except' => ''],
    ];

    public function sort($column): void
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

        // Basic format validation: 1403/01/01
        if (! preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $value)) {
            $this->date = null;
            Flux::toast(variant: 'danger', text: __('app.invalid_date'));

            return;
        }

        try {
            // Ensure the date is actually valid (e.g. 13th month not allowed)
            $jDate = Jalalian::fromFormat('Y/m/d', $value);

            // Normalise back to same format
            $this->date = $jDate->format('Y/m/d');
        } catch (\Throwable $e) {
            $this->date = null;
            Flux::toast(variant: 'danger', text: __('app.invalid_date'));
        }
    }

    #[\Livewire\Attributes\Computed]
    public function remittances()
    {
        return Remittance::query()
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

                    $query->whereDate('created_at', $carbonDate);
                } catch (\Throwable $e) {
                    // If something goes wrong, just ignore the filter.
                }
            })
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    #[\Livewire\Attributes\Computed]
    public function totalPayment()
    {
        return Remittance::query()->sum('payment');
    }

    #[\Livewire\Attributes\Computed]
    public function totalAccountBalance()
    {
        return Remittance::query()->sum('account_balance');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete($id): void
    {
        $this->authorize('accounting_remittance_delete');

        $remittance = Remittance::findOrFail($id);
        $remittance->delete();

        Flux::toast(variant: 'success', text: __('app.remittance_deleted'));
        $this->dispatch('accounting.remittance.index.render');
    }

    public function savePayment($id): void
    {
        $this->authorize('accounting_remittance_edit');

        $remittance = Remittance::findOrFail($id);

        $raw = $this->payments[$id] ?? null;

        if ($raw === null || $raw === '') {
            return;
        }

        // Clean money string (remove thousands separators) before casting
        $clean = (float) str_replace(',', '', $raw);

        $remittance->update([
            'payment' => $clean,
        ]);

        Flux::toast(variant: 'success', text: __('app.remittance_updated'));
        $this->dispatch('accounting.remittance.index.render');
    }

    #[Layout('layouts.panels.accounting')]
    #[On('accounting.remittance.index.render')]
    public function render()
    {
        $this->authorize('accounting_remittance_index');

        return view('livewire.panel.accounting.remittance.index');
    }
}
