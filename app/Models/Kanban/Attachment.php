<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Attachment extends Model
{
    protected $table = 'kanban_attachments';

    protected $fillable = [
        'card_id',
        'uploaded_by',
        'disk',
        'path',
        'filename',
        'original_name',
        'mime',
        'size_bytes',
        'checksum',
        'url',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
