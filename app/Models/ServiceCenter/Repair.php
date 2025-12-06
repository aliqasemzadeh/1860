<?php

namespace App\Models\ServiceCenter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repair extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admission_user_id',
        'admission_description',
        'status',
        'status_description',
        'status_date',
        'estimate_date',
        'owner_name',
        'owner_mobile',
        'owner_email',
        'owner_national_code',
        'owner_address',
        'warranty_type',
        'warranty_date',
        'device_serial_number',
        'device_brand',
        'device_type',
        'device_model',
        'device_color',
        'device_image',
        'device_problem',
        'device_accessories',
        'device_description',
        'device_problem_file',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_date' => 'datetime',
        'estimate_date' => 'datetime',
        'warranty_date' => 'datetime',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(RepairService::class);
    }
}
