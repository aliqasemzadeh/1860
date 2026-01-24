<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    protected $table = 'kanban_columns';

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'wip_limit',
        'is_done',
        'is_blocked',
        'settings',
    ];

    protected $casts = [
        'position'   => 'integer',
        'wip_limit'  => 'integer',
        'is_done'    => 'boolean',
        'is_blocked' => 'boolean',
        'settings'   => 'array',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)->orderBy('position');
    }
}
