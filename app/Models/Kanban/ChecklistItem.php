<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ChecklistItem extends Model
{
    protected $table = 'kanban_checklist_items';

    protected $fillable = [
        'checklist_id',
        'content',
        'is_done',
        'position',
        'done_at',
        'done_by',
    ];

    protected $casts = [
        'is_done'  => 'boolean',
        'position' => 'integer',
        'done_at'  => 'datetime',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function doneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}
