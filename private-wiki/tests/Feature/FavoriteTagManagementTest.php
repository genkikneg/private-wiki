<?php

namespace Tests\Feature;

use App\Models\FavoriteTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_favorite_tags_management_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/favorite-tags/manage');

        $response->assertStatus(200)
            ->assertSee('お気に入りタグの管理')
            ->assertSee('タグを追加');
    }

    public function test_management_page_displays_existing_favorite_tags()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag1 = Tag::factory()->create(['name' => 'Laravel']);
        $tag2 = Tag::factory()->create(['name' => 'Vue.js']);
        
        FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 1]);
        FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 2]);

        $response = $this->get('/favorite-tags/manage');

        $response->assertStatus(200)
            ->assertSee('Laravel')
            ->assertSee('Vue.js')
            ->assertSee('削除', false);
    }

    public function test_management_page_shows_tag_input_for_addition()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/favorite-tags/manage');

        $response->assertStatus(200)
            ->assertSee('タグ名を入力')
            ->assertSee('既存のタグ名を入力するか、新しいタグ名を作成できます');
    }

    public function test_can_add_favorite_tag_from_management_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/favorite-tags/manage/add', [
            'tag_name' => 'React'
        ]);

        $response->assertRedirect('/favorite-tags/manage')
            ->assertSessionHas('success', 'お気に入りタグに追加しました');

        $this->assertDatabaseHas('tags', [
            'name' => 'React'
        ]);

        $tag = Tag::where('name', 'React')->first();
        $this->assertDatabaseHas('favorite_tags', [
            'tag_id' => $tag->id
        ]);
    }

    public function test_can_remove_favorite_tag_from_management_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();
        $favoriteTag = FavoriteTag::factory()->create(['tag_id' => $tag->id]);

        $response = $this->delete("/favorite-tags/manage/{$favoriteTag->id}");

        $response->assertRedirect('/favorite-tags/manage')
            ->assertSessionHas('success', 'お気に入りタグから削除しました');

        $this->assertDatabaseMissing('favorite_tags', [
            'id' => $favoriteTag->id
        ]);
    }

    public function test_management_page_requires_authentication()
    {
        $response = $this->get('/favorite-tags/manage');

        $response->assertRedirect('/login');
    }

    public function test_management_page_shows_empty_state_when_no_favorites()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/favorite-tags/manage');

        $response->assertStatus(200)
            ->assertSee('お気に入りのタグがありません');
    }

    public function test_cannot_add_more_than_5_favorite_tags()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 既に5個のお気に入りタグを作成
        for ($i = 1; $i <= 5; $i++) {
            $tag = Tag::factory()->create(['name' => "Tag{$i}"]);
            FavoriteTag::factory()->create(['tag_id' => $tag->id]);
        }

        $response = $this->post('/favorite-tags/manage/add', [
            'tag_name' => 'ExtraTag'
        ]);

        $response->assertRedirect('/favorite-tags/manage')
            ->assertSessionHas('error', 'お気に入りタグは最大5個までしか登録できません');
    }

    public function test_management_page_shows_limit_warning_when_at_max()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 5個のお気に入りタグを作成
        for ($i = 1; $i <= 5; $i++) {
            $tag = Tag::factory()->create(['name' => "Tag{$i}"]);
            FavoriteTag::factory()->create(['tag_id' => $tag->id]);
        }

        $response = $this->get('/favorite-tags/manage');

        $response->assertStatus(200)
            ->assertSee('5/5個')
            ->assertSee('お気に入りタグは最大5個までです');
    }

    public function test_can_add_existing_tag_by_name()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $existingTag = Tag::factory()->create(['name' => 'ExistingTag']);

        $response = $this->post('/favorite-tags/manage/add', [
            'tag_name' => 'ExistingTag'
        ]);

        $response->assertRedirect('/favorite-tags/manage')
            ->assertSessionHas('success', 'お気に入りタグに追加しました');

        $this->assertDatabaseHas('favorite_tags', [
            'tag_id' => $existingTag->id
        ]);
    }

    public function test_cannot_add_duplicate_favorite_tag_by_name()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => 'DuplicateTag']);
        FavoriteTag::factory()->create(['tag_id' => $tag->id]);

        $response = $this->post('/favorite-tags/manage/add', [
            'tag_name' => 'DuplicateTag'
        ]);

        $response->assertRedirect('/favorite-tags/manage')
            ->assertSessionHas('error', 'このタグは既にお気に入りに登録されています');
    }
}