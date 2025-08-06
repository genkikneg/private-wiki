<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Note; // ← ここで正しくモデルをインポート
use App\Models\Tag;

class NoteSeeder extends Seeder
{
    public function run()
{
    // タグを10個作成
    $tags = Tag::factory()->count(30)->create();

    // ノートを10件作成し、タグをランダムに付与
    Note::factory()->count(30)->create()->each(function ($note) use ($tags) {
        $note->tags()->attach($tags->random(2)); // ランダムに2つ付ける
    });
}
}