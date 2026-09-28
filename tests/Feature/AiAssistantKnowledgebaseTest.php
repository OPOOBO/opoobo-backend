<?php

namespace Tests\Feature;

use App\Models\AiProvider;
use App\Models\Developer;
use App\Models\KnowledgebaseFile;
use App\Services\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiAssistantKnowledgebaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_returns_matching_passage_when_no_provider_is_configured(): void
    {
        config(['services.ai.api_key' => null]);

        KnowledgebaseFile::create([
            'name' => 'guide.txt',
            'path' => 'guide.txt',
            'mime' => 'text/plain',
            'size' => 64,
            'body' => 'Buses depart Lagos every morning from the main terminal.',
            'is_active' => true,
        ]);

        $result = app(AiAssistantService::class)->chat([
            ['role' => 'user', 'content' => 'When do buses depart Lagos?'],
        ]);

        $this->assertSame([], $result['actions']);
        $this->assertStringContainsString('Buses depart Lagos', $result['reply']);
    }

    public function test_chat_says_when_the_knowledgebase_does_not_cover_the_question(): void
    {
        config(['services.ai.api_key' => null]);

        KnowledgebaseFile::create([
            'name' => 'guide.txt',
            'path' => 'guide.txt',
            'mime' => 'text/plain',
            'size' => 64,
            'body' => 'Buses depart Lagos every morning from the main terminal.',
            'is_active' => true,
        ]);

        $result = app(AiAssistantService::class)->chat([
            ['role' => 'user', 'content' => 'Explain quantum entanglement recipes'],
        ]);

        $this->assertSame([], $result['actions']);
        $this->assertSame('The knowledgebase does not cover that question.', $result['reply']);
    }

    public function test_admin_upload_feeds_chat_and_hides_the_provider_key(): void
    {
        config(['services.ai.api_key' => null]);
        Storage::fake('knowledgebase');

        $developer = Developer::create([
            'name' => 'Admin',
            'email' => 'admin@opoobo.com',
            'password' => 'password',
        ]);

        $this->withSession([
            'developer_id' => $developer->id,
            'developer_email' => $developer->email,
            'developer_name' => $developer->name,
            'login_via' => 'admin',
        ]);

        $this->post(route('admin.assistant.files.store'), [
            'file' => UploadedFile::fake()->createWithContent(
                'guide.txt',
                'Buses depart Lagos every morning from the main terminal.',
            ),
        ])->assertRedirect();

        $this->assertDatabaseHas('knowledgebase_files', ['name' => 'guide.txt', 'is_active' => true]);

        $chat = app(AiAssistantService::class)->chat([
            ['role' => 'user', 'content' => 'When do buses depart Lagos?'],
        ]);

        $this->assertSame([], $chat['actions']);
        $this->assertStringContainsString('Buses depart Lagos', $chat['reply']);

        $secret = 'sk-secret-key-1234';
        $this->post(route('admin.assistant.providers.store'), [
            'name' => 'OpenAI',
            'base_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-4o-mini',
            'api_key' => $secret,
            'is_enabled' => '1',
        ])->assertRedirect();

        $page = $this->get(route('admin.assistant'));
        $page->assertOk();
        $page->assertDontSee($secret, false);

        $provider = AiProvider::query()->first();
        $this->assertNotNull($provider);
        $this->assertSame($secret, $provider->api_key);
        $this->assertArrayNotHasKey('api_key', $provider->toArray());
    }
}
