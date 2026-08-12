<?php

namespace App\Livewire\Forms\Panel\User;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProfileForm extends Form
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $national_code = '';

    public function setUser(User $user): void
    {
        $this->first_name = (string) ($user->first_name ?? '');
        $this->last_name = (string) ($user->last_name ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->national_code = (string) ($user->national_code ?? '');
    }

    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(auth()->id()),
            ],
            'national_code' => [
                'nullable',
                'string',
                'ir_national_id',
                Rule::unique('users', 'national_code')->ignore(auth()->id()),
            ],
        ];
    }

    public function update(User $user): void
    {
        $validated = $this->validate();

        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));
        $email = trim((string) ($validated['email'] ?? ''));
        $nationalCode = trim((string) ($validated['national_code'] ?? ''));

        $user->first_name = $firstName === '' ? null : $firstName;
        $user->last_name = $lastName === '' ? null : $lastName;
        $user->email = $email === '' ? null : $email;
        $user->national_code = $nationalCode === '' ? null : $nationalCode;
        $user->save();
    }
}
