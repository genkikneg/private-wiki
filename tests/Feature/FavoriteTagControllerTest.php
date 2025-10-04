<?php

namespace Tests\Feature;

use App\Models\FavoriteTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_favorite_tags_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag1 = Tag::factory()->create(['name' => 'Laravel']);
        $tag2 = Tag::factory()->create(['name' => 'Vue.js']);
        
        FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 2]);
        FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 1]);

        $response = $this->get('/api/favorite-tags');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'id' => 2,
                        'tag' => ['name' => 'Vue.js'],
                        'display_order' => 1
                    ],
                    [
                        'id' => 1,
                        'tag' => ['name' => 'Laravel'],
                        'display_order' => 2
                    ]
                ]
            ]);
    }

    public function test_can_add_favorite_tag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => 'PHP']);

        $response = $this->post('/api/favorite-tags', [
            'tag_id' => $tag->id
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'tag_id' => $tag->id,
                    'display_order' => 1
                ]
            ]);

        $this->assertDatabaseHas('favorite_tags', [
            'tag_id' => $tag->id,
            'display_order' => 1
        ]);
    }

    public function test_cannot_add_duplicate_favorite_tag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();
        FavoriteTag::factory()->create(['tag_id' => $tag->id]);

        $response = $this->postJson('/api/favorite-tags', [
            'tag_id' => $tag->id
        ]);

        $response->assertStatus(422);
    }

    public function test_can_delete_favorite_tag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();
        $favoriteTag = FavoriteTag::factory()->create(['tag_id' => $tag->id]);

        $response = $this->delete("/api/favorite-tags/{$favoriteTag->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'お気に入りタグを削除しました'
            ]);

        $this->assertDatabaseMissing('favorite_tags', [
            'id' => $favoriteTag->id
        ]);
    }

    public function test_can_reorder_favorite_tags()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $favorite1 = FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 1]);
        $favorite2 = FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 2]);
        $favorite3 = FavoriteTag::factory()->create(['tag_id' => $tag3->id, 'display_order' => 3]);

        $response = $this->put('/api/favorite-tags/reorder', [
            'favorite_tag_ids' => [$favorite3->id, $favorite1->id, $favorite2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'お気に入りタグの順序を変更しました'
            ]);

        $this->assertDatabaseHas('favorite_tags', ['id' => $favorite3->id, 'display_order' => 1]);
        $this->assertDatabaseHas('favorite_tags', ['id' => $favorite1->id, 'display_order' => 2]);
        $this->assertDatabaseHas('favorite_tags', ['id' => $favorite2->id, 'display_order' => 3]);
    }

    public function test_add_favorite_tag_requires_tag_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/favorite-tags', []);

        $response->assertStatus(422);
    }

    public function test_add_favorite_tag_requires_valid_tag_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/favorite-tags', [
            'tag_id' => 999999
        ]);

        $response->assertStatus(422);
    }

    public function test_reorder_requires_favorite_tag_ids()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->putJson('/api/favorite-tags/reorder', []);

        $response->assertStatus(422);
    }

    public function test_delete_nonexistent_favorite_tag_returns_404()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete('/api/favorite-tags/999999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'お気に入りタグが見つかりません'
            ]);
    }
}