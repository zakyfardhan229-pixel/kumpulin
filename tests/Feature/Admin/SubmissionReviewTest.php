<?php

namespace Tests\Feature\Admin;

use App\Models\AdminReview;
use App\Models\AiValidation;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionReviewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function submission(array $overrides = []): Submission
    {
        return Submission::factory()->create($overrides);
    }

    public function test_guest_cannot_access_review_pages(): void
    {
        $submission = $this->submission();

        $this->get(route('admin.submissions.index'))->assertRedirect(route('login'));
        $this->get(route('admin.submissions.show', $submission))->assertRedirect(route('login'));
        $this->post(route('admin.submissions.review', $submission))->assertRedirect(route('login'));
    }

    public function test_admin_can_list_and_filter_submissions(): void
    {
        $admin = $this->admin();
        $this->submission(['status' => 'pending_review', 'nama_lengkap' => 'Andi']);
        $this->submission(['status' => 'completed', 'nama_lengkap' => 'Budi']);

        $response = $this->actingAs($admin)->get(route('admin.submissions.index'));
        $response->assertOk()->assertSee('Andi')->assertSee('Budi');

        $filtered = $this->actingAs($admin)->get(route('admin.submissions.index', ['status' => 'completed']));
        $filtered->assertOk()->assertSee('Budi')->assertDontSee('Andi');
    }

    public function test_admin_can_view_review_page(): void
    {
        $admin = $this->admin();
        $submission = $this->submission(['status' => 'pending_review']);
        AiValidation::factory()->for($submission)->create();

        $this->actingAs($admin)->get(route('admin.submissions.show', $submission))
            ->assertOk()
            ->assertSee($submission->submission_code)
            ->assertSee('Analisis AI')
            ->assertSee('Keputusan Admin');
    }

    public function test_admin_can_save_review_and_finalize_status(): void
    {
        $admin = $this->admin();
        $submission = $this->submission(['status' => 'pending_review']);
        $ai = AiValidation::factory()->for($submission)->create(['result' => 'likely_completed']);

        $this->actingAs($admin)
            ->post(route('admin.submissions.review', $submission), [
                'decision' => 'completed',
                'note' => 'Semua jawaban sudah sesuai.',
            ])
            ->assertRedirect();

        $review = $submission->refresh()->latestReview;
        $this->assertNotNull($review);
        $this->assertSame('completed', $review->decision);
        $this->assertSame('Semua jawaban sudah sesuai.', $review->note);
        $this->assertSame($admin->id, $review->admin_id);
        $this->assertNotNull($review->reviewed_at);
        $this->assertSame('completed', $submission->refresh()->status);

        // AI result is preserved, never overwritten by the admin decision.
        $this->assertSame('likely_completed', $ai->refresh()->result);
    }

    public function test_review_rejects_invalid_decision(): void
    {
        $admin = $this->admin();
        $submission = $this->submission(['status' => 'pending_review']);

        $this->actingAs($admin)
            ->post(route('admin.submissions.review', $submission), ['decision' => 'likely_completed'])
            ->assertSessionHasErrors('decision');

        $this->assertSame('pending_review', $submission->refresh()->status);
        $this->assertCount(0, AdminReview::all());
    }

    public function test_re_review_updates_status_and_keeps_history(): void
    {
        $admin = $this->admin();
        $submission = $this->submission(['status' => 'completed']);
        AdminReview::factory()->for($submission, 'submission')->create(['decision' => 'completed', 'admin_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.submissions.review', $submission), ['decision' => 'revision_required', 'note' => 'Nomor 3 belum lengkap.'])
            ->assertRedirect();

        $this->assertSame('revision_required', $submission->refresh()->status);
        $this->assertSame(2, $submission->adminReviews()->count());
    }

    public function test_admin_can_retry_ai_validation(): void
    {
        Storage::fake('local');
        Http::fake(['*' => Http::response('boom', 500)]);

        $admin = $this->admin();
        $submission = $this->submission(['status' => 'processing']);
        $path = 'submissions/x/tugas.jpg';
        Storage::disk('local')->put($path, 'fake-bytes');
        $submission->files()->create([
            'disk' => 'local', 'path' => $path, 'original_name' => 'tugas.jpg',
            'mime_type' => 'image/jpeg', 'size' => 10,
        ]);

        $this->actingAs($admin)->post(route('admin.submissions.retry', $submission))->assertRedirect();
        $this->assertSame(1, $submission->aiValidations()->count());

        $this->actingAs($admin)->post(route('admin.submissions.retry', $submission))->assertRedirect();
        $this->assertSame(2, $submission->aiValidations()->count());
    }

    public function test_admin_can_view_submission_file(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $submission = $this->submission();
        $path = 'submissions/x/tugas.jpg';
        Storage::disk('local')->put($path, 'fake-image-bytes');
        $file = $submission->files()->create([
            'disk' => 'local', 'path' => $path, 'original_name' => 'tugas.jpg',
            'mime_type' => 'image/jpeg', 'size' => 16,
        ]);

        $this->get(route('admin.files.show', $file))->assertRedirect(route('login'));

        $response = $this->actingAs($admin)->get(route('admin.files.show', $file));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_file_route_404_for_missing_file(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $submission = $this->submission();
        $file = $submission->files()->create([
            'disk' => 'local', 'path' => 'submissions/x/gone.jpg', 'original_name' => 'gone.jpg',
            'mime_type' => 'image/jpeg', 'size' => 1,
        ]);

        $this->actingAs($admin)->get(route('admin.files.show', $file))->assertNotFound();
    }

    public function test_dashboard_shows_statistics(): void
    {
        $admin = $this->admin();
        $this->submission(['status' => 'pending_review']);
        $this->submission(['status' => 'completed']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Review');
    }

    public function test_student_sees_admin_note_only_for_revision_required(): void
    {
        $admin = $this->admin();

        $needsFix = $this->submission(['status' => 'revision_required']);
        AdminReview::factory()->for($needsFix, 'submission')->create([
            'decision' => 'revision_required', 'note' => 'Nomor 3 belum lengkap.', 'admin_id' => $admin->id,
        ]);

        $done = $this->submission(['status' => 'completed']);
        AdminReview::factory()->for($done, 'submission')->create([
            'decision' => 'completed', 'note' => 'Internal secret note.', 'admin_id' => $admin->id,
        ]);

        $this->get(route('student.check.show', $needsFix->submission_code))
            ->assertOk()
            ->assertSee('Nomor 3 belum lengkap.');

        $this->get(route('student.check.show', $done->submission_code))
            ->assertOk()
            ->assertDontSee('Internal secret note.');
    }
}
