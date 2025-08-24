<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body'];

    protected static function booted()
    {
        static::created(function ($note) {
            $note->createHistory('created');
        });

        static::updated(function ($note) {
            $note->createHistory('updated');
        });

        static::deleting(function ($note) {
            $note->createHistory('deleted');
        });
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(NoteHistory::class)->orderBy('version', 'desc');
    }

    public function createHistory(string $changeType): void
    {
        $lastVersion = $this->histories()->max('version') ?? 0;
        
        NoteHistory::create([
            'note_id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'tags_snapshot' => $this->tags->pluck('name')->toArray(),
            'change_type' => $changeType,
            'version' => $lastVersion + 1,
        ]);
    }

    public function restoreVersion(int $version): bool
    {
        $history = $this->histories()->where('version', $version)->first();
        
        if (!$history) {
            return false;
        }

        $this->update([
            'title' => $history->title,
            'body' => $history->body,
        ]);

        if ($history->tags_snapshot) {
            $tags = Tag::whereIn('name', $history->tags_snapshot)->get();
            $this->tags()->sync($tags->pluck('id'));
        }

        return true;
    }
}
