<?php

namespace Tests\Feature\Student;

use App\Models\AssignmentSession;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function makeSession(array $overrides = []): AssignmentSession
    {
        return AssignmentSession::factory()->create($overrides);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Zaky Fardhan',
            'kelas' => 'X RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'catatan' => 'Soal nomor 3 di halaman 2.',
            'file' => UploadedFile::fake()->image('tugas.jpg', 800, 1000),
        ], $overrides);
    }

    public function test_submit_form_renders_for_active_session(): void
    {
        $session = $this->makeSession(['status' => 'active']);

        $this->get(route('student.submit', $session->session_code))
            ->assertOk()
            ->assertSee($session->title);
    }

    public function test_submit_form_shows_closed_message_for_closed_session(): void
    {
        $session = $this->makeSession(['status' => 'closed']);

        $this->get(route('student.submit', $session->session_code))
            ->assertOk()
            ->assertSee('Session Ditutup');
    }

    public function test_submit_form_404_for_unknown_code(): void
    {
        $this->get(route('student.submit', 'TSK-XXX-000000-XXXX'))->assertNotFound();
    }

    public function test_student_can_submit_assignment(): void
    {
        Storage::fake('local');
        Http::fake(); // Empty 200: AI fails gracefully, student still gets their ID.
        $session = $this->makeSession(['status' => 'active']);

        $response = $this->post(route('student.submit.store', $session->session_code), $this->payload());

        $submission = Submission::first();
        $this->assertNotNull($submission);
        $response->assertRedirect(route('student.success', $submission->submission_code));

        $this->assertMatchesRegularExpression('/^KMP-[A-Z0-9]{8}$/', $submission->submission_code);
        $this->assertSame('processing', $submission->status);
        $this->assertTrue($submission->refresh()->latestAiValidation->failed());
        $this->assertSame('Zaky Fardhan', $submission->nama_lengkap);

        $file = $submission->files()->first();
        $this->assertNotNull($file);
        $this->assertSame('local', $file->disk);
        $this->assertSame('tugas.jpg', $file->original_name);
        Storage::disk('local')->assertExists($file->path);
        $this->assertStringStartsWith('submissions/'.$session->session_code.'/', $file->path);
    }

    public function test_submission_requires_valid_fields(): void
    {
        $session = $this->makeSession(['status' => 'active']);

        $this->post(route('student.submit.store', $session->session_code), [
            'nama_lengkap' => '',
            'kelas' => '',
            'jurusan' => '',
            'file' => UploadedFile::fake()->create('tugas.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors(['nama_lengkap', 'kelas', 'jurusan', 'file']);

        $this->assertCount(0, Submission::all());
    }

    public function test_submission_rejects_oversized_image(): void
    {
        $session = $this->makeSession(['status' => 'active']);

        $this->post(route('student.submit.store', $session->session_code), $this->payload([
            'file' => UploadedFile::fake()->image('besar.jpg')->size(11 * 1024),
        ]))->assertSessionHasErrors('file');

        $this->assertCount(0, Submission::all());
    }

    public function test_duplicate_identity_requires_confirmation(): void
    {
        Storage::fake('local');
        Http::fake();
        $session = $this->makeSession(['status' => 'active']);

        $this->post(route('student.submit.store', $session->session_code), $this->payload())->assertRedirect();
        $this->assertCount(1, Submission::all());

        // Second submit without confirmation is held back with a warning.
        $this->post(route('student.submit.store', $session->session_code), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('duplicate_warning', true);
        $this->assertCount(1, Submission::all());

        // With confirmation a new submission with a new code is created.
        $response = $this->post(route('student.submit.store', $session->session_code), $this->payload([
            'confirm_resubmit' => '1',
        ]));
        $this->assertCount(2, Submission::all());

        $codes = Submission::pluck('submission_code');
        $this->assertCount(2, $codes->unique());
        $response->assertRedirect(route('student.success', Submission::latest('id')->first()->submission_code));
    }

    public function test_different_student_can_submit_to_same_session(): void
    {
        Storage::fake('local');
        Http::fake();
        $session = $this->makeSession(['status' => 'active']);

        $this->post(route('student.submit.store', $session->session_code), $this->payload())->assertRedirect();
        $this->post(route('student.submit.store', $session->session_code), $this->payload([
            'nama_lengkap' => 'Siswa Lain',
        ]))->assertRedirect();

        $this->assertCount(2, Submission::all());
    }

    public function test_cannot_submit_to_closed_session(): void
    {
        Storage::fake('local');
        $session = $this->makeSession(['status' => 'closed']);

        $this->post(route('student.submit.store', $session->session_code), $this->payload())
            ->assertOk()
            ->assertSee('Session Ditutup');

        $this->assertCount(0, Submission::all());
    }

    public function test_success_page_shows_submission_code(): void
    {
        $submission = Submission::factory()->create();

        $this->get(route('student.success', $submission->submission_code))
            ->assertOk()
            ->assertSee($submission->submission_code);
    }

    public function test_admin_sidebar_placeholders_still_render(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}
