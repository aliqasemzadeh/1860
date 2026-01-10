<?php

namespace App\Livewire\Panel\Accounting\Cheque;

use App\Models\Accounting\Cheque;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class Edit extends Component
{
    public ?Cheque $cheque = null;

    public int $id;

    public string $description = '';

    public string $amount = '0';

    /**
     * Jalali due date (Y/m/d), e.g. 1403/01/01.
     */
    public string $due_at = '';

    #[On('panel.accounting.cheque.edit.assign-data')]
    public function assignData($id): void
    {
        $this->cheque = Cheque::findOrFail($id);

        $this->id = $this->cheque->id;
        $this->description = $this->cheque->description;
        $this->amount = (string) $this->cheque->amount;
        $this->due_at = Jalalian::fromCarbon($this->cheque->due_at)->format('Y/m/d');

        Flux::modal('panel.accounting.cheque.edit.modal')->show();
    }

    public function edit(): void
    {
        $this->authorize('accounting_cheque_edit');

        if (! isset($this->cheque)) {
            return;
        }

        $validated = $this->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'min:0'],
            'due_at' => ['required', 'string'],
        ]);

        try {
            $jDate = Jalalian::fromFormat('Y/m/d', str_replace('-', '/', $validated['due_at']));
            $dueAt = $jDate->toCarbon();
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', text: __('app.invalid_date'));

            return;
        }

        $amount = (float) str_replace(',', '', $validated['amount']);

        $this->cheque->update([
            'description' => $validated['description'],
            'amount' => $amount,
            'due_at' => $dueAt,
        ]);

        Flux::toast(variant: 'success', text: __('app.cheque_updated'));
        $this->dispatch('panel.accounting.cheque.index.render');
        Flux::modal('panel.accounting.cheque.edit.modal')->close();
    }

    public function render()
    {
        return view('livewire.panel.accounting.cheque.edit');
    }
}
