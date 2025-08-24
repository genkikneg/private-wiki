<?php

namespace Tests\Feature;

use App\Models\FavoriteTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_displays_favorite_tags()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag1 = Tag::factory()->create(['name' => 'Laravel']);
        $tag2 = Tag::factory()->create(['name' => 'Vue.js']);
        
        FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 2]);
        FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 1]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('お気に入りタグ')
            ->assertSee('Vue.js')
            ->assertSee('Laravel');
    }

    public function test_sidebar_shows_no_favorites_message_when_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('お気に入りのタグがありません');
    }

    public function test_sidebar_favorite_tags_are_clickable()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => 'Laravel']);
        FavoriteTag::factory()->create(['tag_id' => $tag->id]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('data-tag="Laravel"', false);
    }

    public function test_favorite_tags_are_ordered_by_display_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag1 = Tag::factory()->create(['name' => 'PHP']);
        $tag2 = Tag::factory()->create(['name' => 'JavaScript']);
        $tag3 = Tag::factory()->create(['name' => 'Python']);

        FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 3]);
        FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 1]);
        FavoriteTag::factory()->create(['tag_id' => $tag3->id, 'display_order' => 2]);

        $response = $this->get('/');
        $content = $response->getContent();

        $jsPosition = strpos($content, 'JavaScript');
        $pythonPosition = strpos($content, 'Python');
        $phpPosition = strpos($content, 'PHP');

        $this->assertTrue($jsPosition < $pythonPosition);
        $this->assertTrue($pythonPosition < $phpPosition);
    }
}