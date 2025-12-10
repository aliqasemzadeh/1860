<?php

namespace App\Livewire\Panel\Accounting\Bank;

use App\Models\Accounting\Bank;
use Flux\Flux;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public ?string $description = null;

    public int $sort_order = 0;

    public function create()
    {
        $this->authorize('accounting_bank_create');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        Bank::create($validated);

        $this->dispatch('accounting.bank.index.render');
        Flux::modal('accounting.bank.create.modal')->close();

        $this->reset(['name', 'description', 'sort_order']);
    }

    public function render()
    {
        return view('livewire.panel.accounting.bank.create');
    }
}
