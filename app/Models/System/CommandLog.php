<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class CommandLog extends Model
{
    protected $fillable = [
        'command',
        'parameters',
        'output',
        'status_code',
        'execution_time_ms',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => AsArrayObject::class,
        ];
    }
}
