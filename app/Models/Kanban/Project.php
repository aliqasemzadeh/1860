<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = 'kanban_projects';

    protected $fillable = [
        'workspace_id',
        'name',
        'key',
        'description',
        'color',
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

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'project_id');
    }
}
