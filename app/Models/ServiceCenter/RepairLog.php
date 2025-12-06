<?php

namespace App\Models\ServiceCenter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairLog extends Model
{
    use SoftDeletes;

    protected $fillable = ['repair_id', 'technician_user_id', 'description', 'status'];
}
