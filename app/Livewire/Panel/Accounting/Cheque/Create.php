<?php

namespace App\Livewire\Panel\Accounting\Cheque;

use App\Models\Accounting\Cheque;
use Flux\Flux;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class Create extends Component
{
    public string $description = '';

    public string $amount = '0';

    /**
     * Jalali due date (Y/m/d), e.g. 1403/01/01.
     */
    public string $due_at = '';

    public function create(): void
    {
        $this->authorize('accounting_cheque_create');

        $validated = $this->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'min:0'],
            'due_at' => ['required', 'string'],
        ]);

        // Normalise Jalali date
        try {
            $jDate = Jalalian::fromFormat('Y/m/d', str_replace('-', '/', $validated['due_at']));
            $dueAt = $jDate->toCarbon();
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', text: __('app.invalid_date'));

            return;
        }

        // Clean amount string (remove thousands separators)
        $amount = (float) str_replace(',', '', $validated['amount']);

        Cheque::create([
            'description' => $validated['description'],
            'amount' => $amount,
            'due_at' => $dueAt,
        ]);

        Flux::toast(variant: 'success', text: __('app.cheque_created'));

        $this->reset(['description', 'amount', 'due_at']);
        $this->dispatch('panel.accounting.cheque.index.render');
        Flux::modal('panel.accounting.cheque.create.modal')->close();
    }

    public function render()
    {
        return view('livewire.panel.accounting.cheque.create');
    }
}
