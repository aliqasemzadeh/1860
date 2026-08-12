<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasOneTimePasswords, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mobile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be appended to the model's array / JSON form.
     *
     * @var list<string>
     */
    protected $appends = [
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor: Concatenated first and last name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $first = (string) ($this->first_name ?? '');
                $last = (string) ($this->last_name ?? '');

                return trim($first.' '.$last);
            },
        );
    }

    /**
     * Get the shipping addresses for the user.
     */
    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(\App\Models\Customer\ShippingAddress::class);
    }

    /**
     * Get the default shipping address for the user.
     */
    public function defaultShippingAddress()
    {
        return $this->hasOne(\App\Models\Customer\ShippingAddress::class)
            ->where('is_default', true);
    }
}
