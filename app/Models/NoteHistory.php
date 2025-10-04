<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteHistory extends Model
{
    protected $fillable = [
        'note_id',
        'title',
        'body',
        'tags_snapshot',
        'change_type',
        'version',
    ];

    protected $casts = [
        'tags_snapshot' => 'array',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
