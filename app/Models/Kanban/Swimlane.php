<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Swimlane extends Model
{
    protected $table = 'kanban_swimlanes';

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'archived_at',
        'settings',
    ];

    protected $casts = [
        'position'    => 'integer',
        'archived_at' => 'datetime',
        'settings'    => 'array',
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
