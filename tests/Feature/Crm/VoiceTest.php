<?php

namespace Tests\Feature\Crm;

use App\Http\Voice\VoiceCatalog;
use App\Infrastructure\Persistence\Crm\ContactRecord;
use App\Infrastructure\Persistence\Crm\VoiceRunStore;
use App\Jobs\RunVoiceInstruction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_tools_are_named_routes_and_skip_pages_and_downloads(): void
    {
        $routes = [];
        foreach (VoiceCatalog::tools() as $tool) {
            $routes[] = $tool['route'];
            $this->assertTrue(Route::has($tool['route']), $tool['route']);
            $this->assertSame(str_replace('.', '_', $tool['route']), $tool['name']);
        }

        foreach (VoiceCatalog::EXCLUDED as $route) {
            $this->assertNotContains($route, $routes);
            $this->assertTrue(Route::has($route), $route);
        }
    }

    public function test_tool_endpoint_rejects_a_missing_token(): void
    {
        $this->postJson(route('voice.tools', ['tool' => 'contacts_store']), ['name' => 'Northwind'])
            ->assertUnauthorized()
            ->assertJsonPath('code', 'voice.token.required')
            ->assertJsonPath('feature_id', 'F38');
    }

    public function test_contacts_store_tool_creates_a_contact_for_the_run_acting_user(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $run = app(VoiceRunStore::class)->create('session-ada', $ada->id, 'create a contact named Northwind');

        $this->withHeaders($this->voiceHeaders($run->id))
            ->postJson(route('voice.tools', ['tool' => 'contacts_store']), ['name' => 'Northwind'])
            ->assertOk()
            ->assertJsonPath('name', 'Northwind')
            ->assertJsonPath('column', 'new');

        $this->assertDatabaseHas('contacts', [
            'name' => 'Northwind',
            'column' => 'new',
            'user_id' => $ada->id,
        ]);
    }

    public function test_contacts_show_hides_another_users_contact(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $ben = User::factory()->create(['name' => 'Ben']);
        $this->withSession(['acting_user_id' => $ben->id])
            ->post(route('contacts.store'), ['name' => 'Northwind']);
        $contactId = (int) ContactRecord::query()->where('name', 'Northwind')->value('id');
        $run = app(VoiceRunStore::class)->create('session-ada', $ada->id, 'show Northwind');

        $this->withHeaders($this->voiceHeaders($run->id))
            ->postJson(route('voice.tools', ['tool' => 'contacts_show']), ['contact_id' => $contactId])
            ->assertNotFound()
            ->assertJsonPath('code', 'voice.not_found');
    }

    public function test_voice_store_saves_the_transcript_and_queues_the_agent(): void
    {
        Bus::fake();
        Http::fake([
            'http://whisper.test/v1/transcribe' => Http::response([
                'text' => 'create a contact named Northwind',
                'language' => 'en',
            ]),
        ]);
        $ada = User::factory()->create(['name' => 'Ada']);

        $response = $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('voice.store'), [
                'clip' => UploadedFile::fake()->createWithContent('clip.webm', 'webm-bytes'),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('voice_runs', [
            'acting_user_id' => $ada->id,
            'transcript' => 'create a contact named Northwind',
            'status' => 'queued',
        ]);
        Bus::assertDispatched(RunVoiceInstruction::class);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://whisper.test/v1/transcribe'
                && $request->hasHeader('Authorization', 'Bearer test-whisper');
        });
    }

    /**
     * @return array<string, string>
     */
    private function voiceHeaders(int $runId): array
    {
        return [
            'Authorization' => 'Bearer test-voice-token',
            'X-Voice-Run' => (string) $runId,
        ];
    }
}
