<?php

namespace App\Livewire\Forms\Panel\User;

use App\Models\Customer\ShippingAddress;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

class AddressForm extends Form
{
    public string $name = '';

    public ?int $province_id = null;

    public ?string $city_id = null;

    public string $address = '';

    public string $postal_code = '';

    public string $emergency_contact = '';

    public bool $is_default = false;

    public function setAddress(ShippingAddress $address): void
    {
        $this->name = (string) ($address->name ?? '');
        $this->province_id = $address->province_id !== null ? (int) $address->province_id : null;
        $this->city_id = $address->city_id !== null ? (string) $address->city_id : null;
        $this->address = (string) ($address->address ?? '');
        $this->postal_code = (string) ($address->postal_code ?? '');
        $this->emergency_contact = (string) ($address->emergency_contact ?? '');
        $this->is_default = (bool) $address->is_default;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'province_id' => ['required', 'integer'],
            'city_id' => ['required', 'string', 'regex:/^\d{6}$/'],
            'address' => ['required', 'string'],
            'postal_code' => ['required', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ];
    }

    public function store(): ShippingAddress
    {
        $validated = $this->validate();

        return DB::transaction(function () use ($validated) {
            if (! empty($validated['is_default'])) {
                auth()->user()->shippingAddresses()->update(['is_default' => false]);
            }

            return auth()->user()->shippingAddresses()->create([
                'name' => $validated['name'] ?: null,
                'province_id' => $validated['province_id'],
                'city_id' => $validated['city_id'],
                'address' => $validated['address'],
                'postal_code' => $validated['postal_code'],
                'emergency_contact' => $validated['emergency_contact'] ?: null,
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]);
        });
    }

    public function update(ShippingAddress $address): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($address, $validated) {
            if (! empty($validated['is_default'])) {
                auth()->user()->shippingAddresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'name' => $validated['name'] ?: null,
                'province_id' => $validated['province_id'],
                'city_id' => $validated['city_id'],
                'address' => $validated['address'],
                'postal_code' => $validated['postal_code'],
                'emergency_contact' => $validated['emergency_contact'] ?: null,
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]);
        });
    }
}
