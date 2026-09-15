<?php

namespace Tests\Feature\AI;

use App\Jobs\ValidateSubmissionWithAI;
use App\Models\AiValidation;
use App\Models\AssignmentSession;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ValidateSubmissionWithAITest extends TestCase
{
    use RefreshDatabase;

    private function sse(string $answer): string
    {
        $out = '';

        foreach (mb_str_split($answer, 11) as $token) {
            $out .= 'data: '.json_encode(['success' => true, 'statusCode' => 200, 'answer' => $token])."\n\n";
        }

        return $out."data: [DONE]\n\n";
    }

    private function okAnswer(): string
    {
        return json_encode([
            'overall_result' => 'likely_completed',
            'confidence' => 0.88,
            'questions' => [['number' => 1, 'status' => 'correct', 'confidence' => 0.9, 'reason' => 'OK']],
            'missing_questions' => [],
            'unreadable_questions' => [],
            'summary' => 'Done.',
        ]);
    }

    private function submissionWithFile(): Submission
    {
        Storage::fake('local');

        $submission = Submission::factory()->create(['status' => 'processing']);
        $submission->session->sourceOfTruths()->create([
            'version' => 3,
            'questions' => [['question' => 'Q', 'expected' => 'E', 'validation_type' => 'semantic', 'required_concepts' => []]],
        ]);

        $path = 'submissions/'.$submission->session->session_code.'/tugas.jpg';
        Storage::disk('local')->put($path, 'fake-image-bytes');
        $submission->files()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'tugas.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1234,
        ]);

        return $submission;
    }

    public function test_successful_run_records_validation_and_advances_status(): void
    {
        Http::fake(['*' => Http::response($this->sse($this->okAnswer()), 200)]);
        $submission = $this->submissionWithFile();

        ValidateSubmissionWithAI::dispatchSync($submission->id);

        $validation = $submission->refresh()->latestAiValidation;
        $this->assertNotNull($validation);
        $this->assertSame('completed', $validation->status);
        $this->assertSame('likely_completed', $validation->result);
        $this->assertSame(0.88, $validation->confidence);
        $this->assertSame(3, $validation->source_of_truth_version);
        $this->assertSame('gpt-5.5', $validation->model);
        $this->assertNotNull($validation->raw_response);
        $this->assertSame('pending_review', $submission->refresh()->status);
    }

    public function test_failed_run_records_error_and_keeps_processing(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);
        $submission = $this->submissionWithFile();

        ValidateSubmissionWithAI::dispatchSync($submission->id);

        $validation = $submission->refresh()->latestAiValidation;
        $this->assertNotNull($validation);
        $this->assertTrue($validation->failed());
        $this->assertNull($validation->result);
        $this->assertSame('processing', $submission->refresh()->status);
    }

    public function test_retry_appends_new_record_and_keeps_history(): void
    {
        $submission = $this->submissionWithFile();

        Http::fake(['*' => Http::sequence()
            ->push('boom', 500)
            ->push($this->sse($this->okAnswer()), 200),
        ]);

        ValidateSubmissionWithAI::dispatchSync($submission->id);
        ValidateSubmissionWithAI::dispatchSync($submission->id);

        $this->assertSame(2, $submission->aiValidations()->count());
        $this->assertSame('completed', $submission->refresh()->latestAiValidation->status);
        $this->assertSame('pending_review', $submission->refresh()->status);
    }

    public function test_http_submit_triggers_ai_and_reaches_pending_review(): void
    {
        Storage::fake('local');
        Http::fake(['*' => Http::response($this->sse($this->okAnswer()), 200)]);

        $session = AssignmentSession::factory()->create(['status' => 'active']);

        $response = $this->post(route('student.submit.store', $session->session_code), [
            'nama_lengkap' => 'Zaky Fardhan',
            'kelas' => 'X RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'file' => UploadedFile::fake()->image('tugas.jpg', 800, 1000),
        ]);

        $submission = Submission::first();
        $this->assertNotNull($submission);
        $response->assertRedirect(route('student.success', $submission->submission_code));
        $this->assertSame('pending_review', $submission->refresh()->status);
        $this->assertSame(1, AiValidation::count());
    }
}
