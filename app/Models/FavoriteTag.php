<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_id',
        'display_order',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public static function getNextDisplayOrder(): int
    {
        return static::max('display_order') + 1 ?? 1;
    }

    public static function reorderTags(array $favoriteTagIds): void
    {
        foreach ($favoriteTagIds as $index => $favoriteTagId) {
            static::where('id', $favoriteTagId)->update(['display_order' => $index + 1]);
        }
    }
}