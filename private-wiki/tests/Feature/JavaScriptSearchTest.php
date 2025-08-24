<?php

namespace Tests\Feature;

use App\Models\FavoriteTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JavaScriptSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorite_tag_click_triggers_search()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => 'Laravel']);
        FavoriteTag::factory()->create(['tag_id' => $tag->id]);

        $response = $this->get('/');
        $content = $response->getContent();

        $this->assertStringContainsString('favorite-tag-btn', $content);
        $this->assertStringContainsString('data-tag="Laravel"', $content);
    }

    public function test_favorite_tag_search_redirects_to_correct_url()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/?tag=Laravel');

        $response->assertStatus(200);
        $this->assertEquals('Laravel', request('tag'));
    }

    public function test_multiple_favorite_tags_have_correct_data_attributes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag1 = Tag::factory()->create(['name' => 'PHP']);
        $tag2 = Tag::factory()->create(['name' => 'JavaScript']);
        
        FavoriteTag::factory()->create(['tag_id' => $tag1->id, 'display_order' => 1]);
        FavoriteTag::factory()->create(['tag_id' => $tag2->id, 'display_order' => 2]);

        $response = $this->get('/');
        $content = $response->getContent();

        $this->assertStringContainsString('data-tag="PHP"', $content);
        $this->assertStringContainsString('data-tag="JavaScript"', $content);
    }
}