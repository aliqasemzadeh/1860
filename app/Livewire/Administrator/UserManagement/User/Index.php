<?php

namespace App\Livewire\Administrator\UserManagement\User;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use \Livewire\WithPagination;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
    #[\Livewire\Attributes\Computed]
    public function users()
    {
        return \App\Models\User::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(5);
    }

    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.administrator.user-management.user.index');
    }
}
