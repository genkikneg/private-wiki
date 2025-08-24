<?php

namespace Database\Seeders;

use App\Models\FavoriteTag;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class FavoriteTagSeeder extends Seeder
{
    public function run(): void
    {
        // テスト用のタグを作成
        $tags = [
            ['name' => 'Laravel', 'order' => 1],
            ['name' => 'PHP', 'order' => 2], 
            ['name' => 'JavaScript', 'order' => 3],
        ];

        foreach ($tags as $tagData) {
            $tag = Tag::firstOrCreate(['name' => $tagData['name']]);
            
            // お気に入りタグが既に存在しない場合のみ作成
            if (!FavoriteTag::where('tag_id', $tag->id)->exists()) {
                FavoriteTag::create([
                    'tag_id' => $tag->id,
                    'display_order' => $tagData['order']
                ]);
            }
        }
    }
}