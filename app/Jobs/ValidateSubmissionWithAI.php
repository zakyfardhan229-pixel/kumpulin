<?php

namespace App\Jobs;

use App\Exceptions\ArnaruAIException;
use App\Models\AiValidation;
use App\Models\Submission;
use App\Services\ArnaruAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ValidateSubmissionWithAI implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $submissionId) {}

    public function handle(ArnaruAIService $ai): void
    {
        $submission = Submission::with(['session.currentSourceOfTruth', 'files'])->find($this->submissionId);

        if (! $submission) {
            return;
        }

        Log::info('AI validation started.', ['submission_id' => $submission->id]);

        $startedAt = now();
        $sot = $submission->session->currentSourceOfTruth;

        try {
            $files = $submission->files->map(fn ($file) => [
                'contents' => $file->contents(),
                'filename' => $file->original_name,
            ])->all();

            $result = $ai->validate($files, $submission->session->title, $submission->session->subject, $sot?->questions ?? []);

            $submission->aiValidations()->create([
                'model' => $ai->defaultModel(),
                'status' => AiValidation::STATUS_COMPLETED,
                'result' => $result->overallResult,
                'confidence' => $result->confidence,
                'analysis' => $result->toArray(),
                'raw_response' => $result->rawText,
                'source_of_truth_version' => $sot?->version,
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            if ($submission->status === Submission::STATUS_PROCESSING) {
                $submission->update(['status' => Submission::STATUS_PENDING_REVIEW]);
            }

            Log::info('AI validation completed.', ['submission_id' => $submission->id]);
        } catch (Throwable $e) {
            report($e);

            $submission->aiValidations()->create([
                'model' => $ai->defaultModel(),
                'status' => AiValidation::STATUS_ERROR,
                'raw_response' => $e instanceof ArnaruAIException ? $e->getMessage() : 'Unexpected error during AI validation.',
                'source_of_truth_version' => $sot?->version,
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            Log::warning('AI validation failed.', ['submission_id' => $submission->id]);

            // Do not rethrow: the submission stays `processing` and admin can retry.
        }
    }
}
