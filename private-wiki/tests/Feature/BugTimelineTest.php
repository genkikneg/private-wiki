<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\BugReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BugTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_display_bug_timeline_page()
    {
        $response = $this->get('/bug-timeline');
        
        $response->assertStatus(200);
        $response->assertViewIs('bug-timeline.index');
    }

    /** @test */
    public function it_can_create_bug_report_via_post_request()
    {
        $response = $this->post('/bug-timeline', [
            'title' => 'Test Bug',
            'description' => 'This is a test bug description',
        ]);

        $response->assertRedirect('/bug-timeline');
        $response->assertSessionHas('success', 'バグレポートを投稿しました。');
        
        $this->assertDatabaseHas('bug_reports', [
            'title' => 'Test Bug',
            'description' => 'This is a test bug description',
            'status' => 'open',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post('/bug-timeline', []);

        $response->assertSessionHasErrors(['title', 'description']);
    }

    /** @test */
    public function it_displays_bug_reports_in_timeline_order()
    {
        // より古いバグレポートを作成
        BugReport::create([
            'title' => 'Older Bug',
            'description' => 'Older description',
        ]);
        
        // 1秒待機
        sleep(1);
        
        // より新しいバグレポートを作成
        BugReport::create([
            'title' => 'Newer Bug', 
            'description' => 'Newer description',
        ]);

        $response = $this->get('/bug-timeline');
        
        $response->assertSeeInOrder(['Newer Bug', 'Older Bug']);
    }

    /** @test */
    public function it_can_update_bug_status()
    {
        $bugReport = BugReport::create([
            'title' => 'Test Bug',
            'description' => 'Test description',
            'status' => 'open',
        ]);

        $response = $this->patch("/bug-timeline/{$bugReport->id}/status", [
            'status' => 'closed',
        ]);

        $response->assertRedirect('/bug-timeline');
        $this->assertDatabaseHas('bug_reports', [
            'id' => $bugReport->id,
            'status' => 'closed',
        ]);
    }

    /** @test */
    public function it_can_delete_bug_report()
    {
        $bugReport = BugReport::create([
            'title' => 'Test Bug to Delete',
            'description' => 'This bug will be deleted',
            'status' => 'open',
        ]);

        $response = $this->delete("/bug-timeline/{$bugReport->id}");

        $response->assertRedirect('/bug-timeline');
        $response->assertSessionHas('success', 'バグレポートを削除しました。');
        
        $this->assertDatabaseMissing('bug_reports', [
            'id' => $bugReport->id,
        ]);
    }
}