<?php

namespace Tests\Feature\Admin;

use App\Models\AssignmentSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentSessionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Tugas Informatika',
            'subject' => 'Informatika',
            'description' => 'Kerjakan dengan jujur.',
            'assignment_date' => '2026-09-29',
            'questions' => [
                [
                    'question' => 'Jelaskan pengertian algoritma.',
                    'expected' => 'Langkah sistematis menyelesaikan masalah.',
                    'validation_type' => 'semantic',
                    'required_concepts' => 'langkah sistematis, instruksi',
                ],
            ],
        ], $overrides);
    }

    public function test_guest_cannot_access_sessions(): void
    {
        $this->get(route('admin.sessions.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_session_with_sot_v1(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.sessions.store'), $this->payload());

        $session = AssignmentSession::first();
        $this->assertNotNull($session);
        $response->assertRedirect(route('admin.sessions.show', $session));

        $this->assertMatchesRegularExpression('/^TSK-INF-290926-[A-Z0-9]{4}$/', $session->session_code);
        $this->assertSame('active', $session->status);
        $this->assertSame($admin->id, $session->created_by);

        $sot = $session->currentSourceOfTruth;
        $this->assertSame(1, $sot->version);
        $this->assertSame(['langkah sistematis', 'instruksi'], $sot->questions[0]['required_concepts']);
    }

    public function test_session_codes_are_unique(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.sessions.store'), $this->payload());
        $this->actingAs($admin)->post(route('admin.sessions.store'), $this->payload());

        $this->assertCount(2, AssignmentSession::all());
        $this->assertNotSame(
            AssignmentSession::first()->session_code,
            AssignmentSession::latest('id')->first()->session_code
        );
    }

    public function test_updating_sot_creates_new_version(): void
    {
        $admin = User::factory()->create();
        $session = AssignmentSession::factory()->create(['created_by' => $admin->id]);
        $session->sourceOfTruths()->create([
            'version' => 1,
            'questions' => [['question' => 'Q1', 'expected' => 'E1', 'validation_type' => 'semantic', 'required_concepts' => []]],
        ]);

        $payload = $this->payload(['questions' => [
            ['question' => 'Q1 edited', 'expected' => 'E1', 'validation_type' => 'semantic', 'required_concepts' => ''],
        ]]);

        $this->actingAs($admin)->put(route('admin.sessions.update', $session), $payload)->assertRedirect();

        $this->assertSame(2, $session->refresh()->currentSourceOfTruth->version);
        $this->assertSame(2, $session->sourceOfTruths()->count());
        // Old version preserved.
        $this->assertSame('Q1', $session->sourceOfTruths()->where('version', 1)->first()->questions[0]['question']);
    }

    public function test_updating_without_sot_change_keeps_version(): void
    {
        $admin = User::factory()->create();
        $session = AssignmentSession::factory()->create(['created_by' => $admin->id]);
        $questions = [['question' => 'Q1', 'expected' => 'E1', 'validation_type' => 'semantic', 'required_concepts' => []]];
        $session->sourceOfTruths()->create(['version' => 1, 'questions' => $questions]);

        $payload = $this->payload([
            'title' => 'Judul Baru',
            'questions' => [
                ['question' => 'Q1', 'expected' => 'E1', 'validation_type' => 'semantic', 'required_concepts' => ''],
            ],
        ]);

        $this->actingAs($admin)->put(route('admin.sessions.update', $session), $payload)->assertRedirect();

        $this->assertSame('Judul Baru', $session->refresh()->title);
        $this->assertSame(1, $session->sourceOfTruths()->count());
    }

    public function test_admin_can_close_active_session(): void
    {
        $admin = User::factory()->create();
        $session = AssignmentSession::factory()->create(['status' => 'active']);

        $this->actingAs($admin)->patch(route('admin.sessions.close', $session))->assertRedirect();

        $this->assertSame('closed', $session->refresh()->status);
    }

    public function test_admin_can_view_session_pages(): void
    {
        $admin = User::factory()->create();
        $session = AssignmentSession::factory()->create(['created_by' => $admin->id]);
        $session->sourceOfTruths()->create([
            'version' => 1,
            'questions' => [['question' => 'Q1', 'expected' => 'E1', 'validation_type' => 'semantic', 'required_concepts' => ['a']]],
        ]);

        $this->actingAs($admin)->get(route('admin.sessions.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.sessions.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.sessions.show', $session))->assertOk();
        $this->actingAs($admin)->get(route('admin.sessions.edit', $session))->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_store_validation_requires_questions(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.sessions.store'), $this->payload(['questions' => []]))
            ->assertSessionHasErrors('questions');

        $this->assertCount(0, AssignmentSession::all());
    }
}
