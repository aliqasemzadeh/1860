<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Workspace extends Model
{
    protected $table = 'kanban_workspaces';

    protected $fillable = [
        'name',
        'description',
        'owner_user_id',
        'is_active',
        'archived_at',
        'settings',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'archived_at' => 'datetime',
        'settings'    => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
