<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteMarkdownImportUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_markdown_file_selection_is_limited_to_button_area(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notes.create'));

        $response->assertOk();

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $fileInputNodes = $xpath->query('//*[@id="markdown-file"]');
        $this->assertSame(1, $fileInputNodes->length, 'markdown-file input should be rendered');
        $fileInputClass = $fileInputNodes->item(0)->getAttribute('class');
        $this->assertMatchesRegularExpression('/(^|\\s)(hidden|sr-only)(\\s|$)/', $fileInputClass, 'markdown-file input should be visually hidden');

        $triggerNodes = $xpath->query('//*[@data-testid="markdown-file-trigger"]');
        $this->assertSame(1, $triggerNodes->length, 'markdown file trigger button should be rendered');
        $this->assertSame('button', strtolower($triggerNodes->item(0)->nodeName), 'trigger should be a button element');

        $buttonLabelNodes = $xpath->query('//button[normalize-space(text())="ファイルを選択"]');
        $this->assertSame(1, $buttonLabelNodes->length, 'only one visible ファイルを選択 button should exist');
    }
}
