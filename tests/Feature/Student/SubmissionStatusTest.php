<?php

namespace Tests\Feature\Student;

use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_form_renders(): void
    {
        $this->get(route('student.check'))->assertOk()->assertSee('Cek Status');
    }

    public function test_check_form_redirects_to_status_page(): void
    {
        $submission = Submission::factory()->create();

        $this->get(route('student.check', ['code' => strtolower($submission->submission_code)]))
            ->assertRedirect(route('student.check.show', $submission->submission_code));
    }

    public function test_status_page_shows_public_info_only(): void
    {
        $submission = Submission::factory()->create(['status' => 'pending_review']);

        $response = $this->get(route('student.check.show', $submission->submission_code))->assertOk();

        $response->assertSee($submission->submission_code);
        $response->assertSee($submission->session->title);
        $response->assertSee('Sedang ditinjau Admin');
        $response->assertDontSee('systemPrompt');
        $response->assertDontSee('raw_response');
    }

    public function test_each_status_renders_its_heading(): void
    {
        $headings = [
            'processing' => 'Sedang diproses',
            'pending_review' => 'Sedang ditinjau Admin',
            'completed' => 'Tugas Selesai',
            'incomplete' => 'Tugas Belum Selesai',
            'revision_required' => 'Perlu Perbaikan',
        ];

        foreach ($headings as $status => $heading) {
            $submission = Submission::factory()->create(['status' => $status]);

            $this->get(route('student.check.show', $submission->submission_code))
                ->assertOk()
                ->assertSee($heading);
        }
    }

    public function test_unknown_code_shows_friendly_page(): void
    {
        $this->get(route('student.check.show', 'KMP-UNKNOWN1'))
            ->assertOk()
            ->assertSee('Submission Tidak Ditemukan');
    }

    public function test_code_lookup_is_case_insensitive(): void
    {
        $submission = Submission::factory()->create();

        $this->get('/check/'.strtolower($submission->submission_code))->assertOk();
    }
}
