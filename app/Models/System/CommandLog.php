<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class CommandLog extends Model
{
    protected $fillable = ['command', 'parameters', 'output', 'status_code', 'execution_time_ms', 'status'];

    protected function casts(): array
    {
        return [
            'parameters' => AsArrayObject::class,
        ];
    }
}
