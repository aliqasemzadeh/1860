<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Board extends Model
{
    protected $table = 'kanban_boards';

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'visibility',
        'created_by',
        'settings',
        'archived_at',
    ];

    protected $casts = [
        'settings'    => 'array',
        'archived_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    public function swimlanes(): HasMany
    {
        return $this->hasMany(Swimlane::class)->orderBy('position');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
