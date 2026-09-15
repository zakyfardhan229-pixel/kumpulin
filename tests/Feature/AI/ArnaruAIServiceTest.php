<?php

namespace Tests\Feature\AI;

use App\Exceptions\ArnaruAIException;
use App\Services\ArnaruAIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArnaruAIServiceTest extends TestCase
{
    private function sse(string $answer, int $chunkSize = 7): string
    {
        $out = '';

        foreach (mb_str_split($answer, $chunkSize) as $token) {
            $out .= 'data: '.json_encode([
                'success' => true,
                'statusCode' => 200,
                'answer' => $token,
                'model' => 'gpt-5.5',
                'conversationId' => 'test-convo',
            ])."\n\n";
        }

        return $out."data: [DONE]\n\n";
    }

    private function answer(array $overrides = []): string
    {
        return json_encode(array_merge([
            'overall_result' => 'likely_completed',
            'confidence' => 0.92,
            'questions' => [
                ['number' => 1, 'status' => 'correct', 'confidence' => 0.97, 'reason' => 'Matches the expected concept.'],
                ['number' => 2, 'status' => 'not_a_real_status', 'confidence' => 0.5, 'reason' => 'Hmm.'],
            ],
            'missing_questions' => [],
            'unreadable_questions' => [],
            'summary' => 'Most required answers were detected.',
        ], $overrides));
    }

    private function questions(): array
    {
        return [
            ['question' => 'Jelaskan pengertian algoritma.', 'expected' => 'Langkah sistematis.', 'validation_type' => 'semantic', 'required_concepts' => ['langkah sistematis']],
        ];
    }

    public function test_parses_sse_stream_into_normalized_result(): void
    {
        Http::fake(['*' => Http::response($this->sse($this->answer()), 200)]);

        $result = app(ArnaruAIService::class)->validate(
            [['contents' => 'fake-bytes', 'filename' => 'tugas.jpg']],
            'Tugas Informatika', 'Informatika', $this->questions()
        );

        $this->assertSame('likely_completed', $result->overallResult);
        $this->assertSame(0.92, $result->confidence);
        $this->assertCount(2, $result->questions);
        $this->assertSame('correct', $result->questions[0]['status']);
        // Unknown per-question statuses coerce to uncertain, never fail the run.
        $this->assertSame('uncertain', $result->questions[1]['status']);
        $this->assertSame('Most required answers were detected.', $result->summary);
    }

    public function test_sends_multipart_request_with_files_field_and_prompts(): void
    {
        Http::fake(['*' => Http::response($this->sse($this->answer()), 200)]);

        app(ArnaruAIService::class)->validate(
            [['contents' => 'fake-bytes', 'filename' => 'tugas.jpg']],
            'Tugas Informatika', 'Informatika', $this->questions()
        );

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://arnaru-ai.vercel.app/api/chat') {
                return false;
            }

            // Multipart payload arrives as a list of name/contents parts.
            $parts = collect($request->data())->keyBy('name');

            return ($parts['model']['contents'] ?? null) === 'gpt-5.5'
                && str_contains($parts['question']['contents'] ?? '', 'Jelaskan pengertian algoritma.')
                && str_contains($parts['question']['contents'] ?? '', 'Tugas Informatika')
                && isset($parts['systemPrompt'])
                && isset($parts['files']);
        });
    }

    public function test_malformed_json_answer_throws(): void
    {
        Http::fake(['*' => Http::response($this->sse('this is not json at all'), 200)]);

        $this->expectException(ArnaruAIException::class);

        app(ArnaruAIService::class)->validate(
            [['contents' => 'x', 'filename' => 't.jpg']], 'T', 'S', $this->questions()
        );
    }

    public function test_invalid_overall_result_throws(): void
    {
        Http::fake(['*' => Http::response($this->sse($this->answer(['overall_result' => 'approved'])), 200)]);

        $this->expectException(ArnaruAIException::class);

        app(ArnaruAIService::class)->validate(
            [['contents' => 'x', 'filename' => 't.jpg']], 'T', 'S', $this->questions()
        );
    }

    public function test_api_error_chunk_throws(): void
    {
        Http::fake(['*' => Http::response("data: {\"success\":false,\"error\":\"Rate limit\"}\n\ndata: [DONE]\n\n", 200)]);

        $this->expectException(ArnaruAIException::class);

        app(ArnaruAIService::class)->validate(
            [['contents' => 'x', 'filename' => 't.jpg']], 'T', 'S', $this->questions()
        );
    }

    public function test_http_error_throws(): void
    {
        Http::fake(['*' => Http::response('Server error', 500)]);

        $this->expectException(ArnaruAIException::class);

        app(ArnaruAIService::class)->validate(
            [['contents' => 'x', 'filename' => 't.jpg']], 'T', 'S', $this->questions()
        );
    }

    public function test_empty_files_throws_without_http_call(): void
    {
        Http::fake();

        $this->expectException(ArnaruAIException::class);

        app(ArnaruAIService::class)->validate([], 'T', 'S', $this->questions());

        Http::assertNothingSent();
    }
}
