<?php

namespace App\Livewire\Administrator\UserManagement\User;

use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public int $id;

    public string $mobile = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    #[On('administrator.user-management.user.edit.assign-data')]
    public function assignData($id): void
    {
        $this->user = User::findOrFail($id);
        $this->id = $this->user->id;
        $this->first_name = (string) ($this->user->first_name ?? '');
        $this->last_name = (string) ($this->user->last_name ?? '');
        $this->mobile = (string) $this->user->mobile;
        $this->email = (string) ($this->user->email ?? '');
        $this->password = '';
        $this->password_confirmation = '';
        Flux::modal('administrator.user-management.user.edit.modal')->show();
    }

    public function edit(): void
    {
        $this->authorize('administrator_user_management_edit');
        
        if (! isset($this->user)) {
            return;
        }

        $validated = $this->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'ir_mobile', 'max:255', Rule::unique('users', 'mobile')->ignore($this->user)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['nullable', 'string'],
        ]);

        $this->user->first_name = $validated['first_name'] ?? null;
        $this->user->last_name = $validated['last_name'] ?? null;
        $this->user->mobile = $validated['mobile'];
        $this->user->email = $validated['email'] ?? null;

        if (! empty($validated['password'] ?? '')) {
            // Will be hashed automatically via the model cast
            $this->user->password = $validated['password'];
        }

        $this->user->save();

        $this->dispatch('pg:eventRefresh-administrator.user-management.user.table');
        Flux::modal('administrator.user-management.user.edit.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.administrator.user-management.user.edit');
    }
}
