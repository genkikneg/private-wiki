<?php

namespace Tests\Unit;

use App\Models\FavoriteTag;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorite_tag_can_be_created()
    {
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        
        $favoriteTag = FavoriteTag::create([
            'tag_id' => $tag->id,
            'display_order' => 1
        ]);

        $this->assertInstanceOf(FavoriteTag::class, $favoriteTag);
        $this->assertEquals($tag->id, $favoriteTag->tag_id);
        $this->assertEquals(1, $favoriteTag->display_order);
    }

    public function test_favorite_tag_belongs_to_tag()
    {
        $tag = Tag::factory()->create(['name' => 'Vue.js']);
        $favoriteTag = FavoriteTag::factory()->create([
            'tag_id' => $tag->id
        ]);

        $this->assertInstanceOf(Tag::class, $favoriteTag->tag);
        $this->assertEquals('Vue.js', $favoriteTag->tag->name);
    }

    public function test_favorite_tags_are_ordered_by_display_order()
    {
        $tag1 = Tag::factory()->create(['name' => 'PHP']);
        $tag2 = Tag::factory()->create(['name' => 'JavaScript']);
        $tag3 = Tag::factory()->create(['name' => 'Python']);

        FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 3]);
        FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 1]);
        FavoriteTag::factory()->create(['tag_id' => $tag3->id, 'display_order' => 2]);

        $favoriteTags = FavoriteTag::ordered()->get();

        $this->assertEquals('JavaScript', $favoriteTags->first()->tag->name);
        $this->assertEquals('Python', $favoriteTags->get(1)->tag->name);
        $this->assertEquals('PHP', $favoriteTags->last()->tag->name);
    }

    public function test_can_get_next_display_order()
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        
        FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 1]);
        FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 3]);

        $nextOrder = FavoriteTag::getNextDisplayOrder();
        
        $this->assertEquals(4, $nextOrder);
    }

    public function test_get_next_display_order_returns_1_when_no_favorites_exist()
    {
        $nextOrder = FavoriteTag::getNextDisplayOrder();
        
        $this->assertEquals(1, $nextOrder);
    }

    public function test_can_reorder_favorite_tags()
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $favorite1 = FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 1]);
        $favorite2 = FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 2]);
        $favorite3 = FavoriteTag::factory()->create(['tag_id' => $tag3->id, 'display_order' => 3]);

        FavoriteTag::reorderTags([$favorite3->id, $favorite1->id, $favorite2->id]);

        $this->assertEquals(1, $favorite3->fresh()->display_order);
        $this->assertEquals(2, $favorite1->fresh()->display_order);
        $this->assertEquals(3, $favorite2->fresh()->display_order);
    }

    public function test_tag_id_must_be_unique()
    {
        $tag = Tag::factory()->create();
        
        FavoriteTag::factory()->create(['tag_id' => $tag->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        FavoriteTag::factory()->create(['tag_id' => $tag->id]);
    }
}