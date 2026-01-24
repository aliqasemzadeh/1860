<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CardEvent extends Model
{
    protected $table = 'kanban_card_events';

    protected $fillable = [
        'card_id',
        'actor_user_id',
        'type',
        'from_column_id',
        'to_column_id',
        'from_swimlane_id',
        'to_swimlane_id',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'occurred_at' => 'datetime',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function fromColumn(): BelongsTo
    {
        return $this->belongsTo(Column::class, 'from_column_id');
    }

    public function toColumn(): BelongsTo
    {
        return $this->belongsTo(Column::class, 'to_column_id');
    }

    public function fromSwimlane(): BelongsTo
    {
        return $this->belongsTo(Swimlane::class, 'from_swimlane_id');
    }

    public function toSwimlane(): BelongsTo
    {
        return $this->belongsTo(Swimlane::class, 'to_swimlane_id');
    }
}
