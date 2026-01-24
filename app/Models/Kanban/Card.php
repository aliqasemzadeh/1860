<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Card extends Model
{
    protected $table = 'kanban_cards';

    protected $fillable = [
        'board_id',
        'column_id',
        'swimlane_id',
        'project_id',
        'title',
        'description',
        'position',
        'priority',
        'due_at',
        'started_at',
        'completed_at',
        'created_by',
        'archived_at',
        'meta',
    ];

    protected $casts = [
        'position'     => 'integer',
        'priority'     => 'integer',
        'due_at'       => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'archived_at'  => 'datetime',
        'meta'         => 'array',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function swimlane(): BelongsTo
    {
        return $this->belongsTo(Swimlane::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Pivot rows (useful for assigned_at / admin queries) */
    public function assigneeLinks(): HasMany
    {
        return $this->hasMany(CardAssignee::class);
    }

    /** Users assigned to this card */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'card_assignees')
            ->withTimestamps()
            ->withPivot(['assigned_at']);
    }

    /** Pivot rows (useful for admin queries) */
    public function cardLabelLinks(): HasMany
    {
        return $this->hasMany(CardLabel::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'card_labels')
            ->withTimestamps();
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        // SoftDeletes on Comment; this hides deleted comments by default
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->orderBy('created_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CardEvent::class)->orderBy('occurred_at');
    }
}
