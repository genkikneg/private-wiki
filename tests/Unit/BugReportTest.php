<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BugReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BugReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_bug_report()
    {
        $bugReport = BugReport::create([
            'title' => 'Sample Bug',
            'description' => 'This is a sample bug description',
            'status' => 'open',
        ]);

        $this->assertInstanceOf(BugReport::class, $bugReport);
        $this->assertEquals('Sample Bug', $bugReport->title);
        $this->assertEquals('This is a sample bug description', $bugReport->description);
        $this->assertEquals('open', $bugReport->status);
    }

    /** @test */
    public function it_has_required_fields()
    {
        $requiredFields = ['title', 'description'];
        
        foreach ($requiredFields as $field) {
            $data = [
                'title' => 'Sample Bug',
                'description' => 'This is a sample bug description',
                'status' => 'open',
            ];
            unset($data[$field]);
            
            $this->expectException(\Illuminate\Database\QueryException::class);
            BugReport::create($data);
        }
    }

    /** @test */
    public function it_has_default_status_open()
    {
        $bugReport = BugReport::create([
            'title' => 'Sample Bug',
            'description' => 'This is a sample bug description',
        ]);

        $this->assertEquals('open', $bugReport->status);
    }

    /** @test */
    public function it_can_be_ordered_by_created_at_desc()
    {
        $first = BugReport::create([
            'title' => 'First Bug',
            'description' => 'First description',
        ]);
        sleep(1);

        $second = BugReport::create([
            'title' => 'Second Bug',
            'description' => 'Second description',
        ]);

        $ordered = BugReport::latest()->get();
        
        $this->assertEquals('Second Bug', $ordered->first()->title);
        $this->assertEquals('First Bug', $ordered->last()->title);
    }
}