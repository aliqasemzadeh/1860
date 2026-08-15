<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

#[Fillable(['command', 'parameters', 'output', 'status_code', 'execution_time_ms', 'status'])]
class CommandLog extends Model
{
    protected function casts(): array
    {
        return [
            'parameters' => AsArrayObject::class,
        ];
    }
}
