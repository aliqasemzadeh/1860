<?php

namespace App\Livewire\Forms\Main\Order;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Sadegh19b\LaravelPersianValidation\Rules\IranianNationalId;

class CustomerProfileForm extends Form
{
    public string $first_name = '';

    public string $last_name = '';

    public string $national_code = '';

    public function setUser(User $user): void
    {
        $this->first_name = (string) ($user->first_name ?? '');
        $this->last_name = (string) ($user->last_name ?? '');
        $this->national_code = (string) ($user->national_code ?? '');
    }

    public function rules(User $user): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'national_code' => [
                'required',
                'string',
                new IranianNationalId(convertPersianNumbers: true),
                Rule::unique('users', 'national_code')->ignore($user->id),
            ],
        ];
    }

    public function update(User $user): void
    {
        $this->national_code = $this->normalizeDigits(trim($this->national_code));

        $validated = $this->validate($this->rules($user));

        $user->first_name = trim($validated['first_name']);
        $user->last_name = trim($validated['last_name']);
        $user->national_code = $this->normalizeDigits(trim($validated['national_code']));
        $user->save();
    }

    protected function normalizeDigits(string $value): string
    {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $value
        );
    }
}
