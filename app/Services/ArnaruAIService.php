<?php

namespace App\Services;

use App\DataTransferObjects\AiValidationResult;
use App\Exceptions\ArnaruAIException;
use App\Models\AiValidation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ArnaruAIService
{
    public const MAX_FILES = 9;

    private readonly string $baseUrl;

    private readonly string $defaultModel;

    private readonly int $timeout;

    public function __construct()
    {
        $config = config('services.arnaru');

        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->defaultModel = $config['model'];
        $this->timeout = (int) $config['timeout'];
    }

    public function defaultModel(): string
    {
        return $this->defaultModel;
    }

    /**
     * Analyze student assignment image(s) against the session Source of Truth.
     *
     * @param  array<int, array{contents: string, filename: string}>  $files  Max 9 files.
     * @param  array<int, array{question: string, expected: string, validation_type: string, required_concepts: array<int, string>}>  $questions
     *
     * @throws ArnaruAIException
     */
    public function validate(array $files, string $title, string $subject, array $questions, ?string $model = null): AiValidationResult
    {
        if ($files === []) {
            throw new ArnaruAIException('No assignment files provided for AI validation.');
        }

        if (count($files) > self::MAX_FILES) {
            throw new ArnaruAIException('AI validation supports at most 9 files.');
        }

        $model ??= $this->defaultModel;

        $request = Http::timeout($this->timeout)->asMultipart();

        foreach ($files as $file) {
            $request->attach('files', $file['contents'], $file['filename']);
        }

        try {
            $response = $request->post($this->baseUrl.'/api/chat', [
                'question' => $this->buildQuestionPrompt($title, $subject, $questions),
                'model' => $model,
                'systemPrompt' => $this->buildSystemPrompt(),
            ]);
            $response->throw();
        } catch (ConnectionException $e) {
            throw new ArnaruAIException('AI request timed out. Please retry later.', previous: $e);
        } catch (RequestException $e) {
            throw new ArnaruAIException('AI service unavailable (HTTP '.$e->response->status().').', previous: $e);
        }

        $answer = $this->extractAnswer($response->body());
        $data = $this->extractJson($answer);

        return $this->normalize($data, $answer);
    }

    /**
     * Concatenate streamed `answer` tokens from the SSE response.
     *
     * @throws ArnaruAIException
     */
    public function extractAnswer(string $stream): string
    {
        $answer = '';

        foreach (preg_split('/\r?\n/', $stream) ?: [] as $line) {
            $line = trim($line);

            if (! str_starts_with($line, 'data:')) {
                continue;
            }

            $payload = trim(substr($line, strlen('data:')));

            if ($payload === '[DONE]') {
                break;
            }

            $chunk = json_decode($payload, true);

            if (! is_array($chunk)) {
                continue;
            }

            if (($chunk['success'] ?? true) === false) {
                throw new ArnaruAIException('AI service returned an error.');
            }

            $answer .= (string) ($chunk['answer'] ?? '');
        }

        if ($answer === '') {
            throw new ArnaruAIException('AI service returned an empty response.');
        }

        return $answer;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ArnaruAIException
     */
    private function extractJson(string $text): array
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            throw new ArnaruAIException('AI response did not contain valid JSON.');
        }

        $data = json_decode(substr($text, $start, $end - $start + 1), true);

        if (! is_array($data)) {
            throw new ArnaruAIException('AI response did not contain valid JSON.');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ArnaruAIException
     */
    private function normalize(array $data, string $rawText): AiValidationResult
    {
        $overall = $data['overall_result'] ?? null;

        if (! in_array($overall, AiValidation::RESULTS, true)) {
            throw new ArnaruAIException('AI response has an invalid overall_result.');
        }

        $confidence = $data['confidence'] ?? null;

        if (! is_numeric($confidence) || $confidence < 0 || $confidence > 1) {
            throw new ArnaruAIException('AI response has an invalid confidence value.');
        }

        $questions = [];

        foreach ((array) ($data['questions'] ?? []) as $q) {
            if (! is_array($q)) {
                continue;
            }

            $questions[] = [
                'number' => (int) ($q['number'] ?? 0),
                'status' => $this->normalizeQuestionStatus($q['status'] ?? null),
                'confidence' => is_numeric($q['confidence'] ?? null) ? (float) $q['confidence'] : 0.0,
                'reason' => (string) ($q['reason'] ?? ''),
            ];
        }

        return new AiValidationResult(
            overallResult: $overall,
            confidence: (float) $confidence,
            questions: $questions,
            missingQuestions: array_values((array) ($data['missing_questions'] ?? [])),
            unreadableQuestions: array_values((array) ($data['unreadable_questions'] ?? [])),
            summary: (string) ($data['summary'] ?? ''),
            rawText: $rawText,
        );
    }

    private function normalizeQuestionStatus(mixed $status): string
    {
        return match ($status) {
            'correct', 'incorrect', 'uncertain', 'unreadable', 'missing' => $status,
            default => 'uncertain',
        };
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
            You are an AI assignment validation assistant.

            Your role is to analyze a student's assignment against the provided Source of Truth.

            You are NOT the final decision maker. Your result is only a recommendation for an administrator.
            Never claim that the assignment is officially approved.

            Analyze:
            1. Whether required questions are answered.
            2. Whether answers match the Source of Truth.
            3. Whether answers appear incorrect.
            4. Whether parts of the image are unreadable.
            5. Confidence for each analysis (0.0 to 1.0).
            6. Overall recommendation: likely_completed, likely_incomplete, or uncertain.

            Return structured JSON only, with exactly this shape:
            {"overall_result": "likely_completed|likely_incomplete|uncertain", "confidence": 0.0-1.0,
             "questions": [{"number": 1, "status": "correct|incorrect|uncertain|unreadable|missing", "confidence": 0.0-1.0, "reason": "..."}],
             "missing_questions": [], "unreadable_questions": [], "summary": "..."}
            PROMPT;
    }

    /**
     * @param  array<int, array{question: string, expected: string, validation_type: string, required_concepts: array<int, string>}>  $questions
     */
    private function buildQuestionPrompt(string $title, string $subject, array $questions): string
    {
        $lines = [
            'Assignment Information',
            '',
            "Title: {$title}",
            "Subject: {$subject}",
            '',
            'Source of Truth:',
        ];

        foreach (array_values($questions) as $i => $q) {
            $lines[] = '';
            $lines[] = 'Question '.($i + 1).': '.$q['question'];
            $lines[] = 'Expected Answer / Concept: '.$q['expected'];
            $lines[] = 'Validation Type: '.$q['validation_type'];

            if (! empty($q['required_concepts'])) {
                $lines[] = 'Required Concepts: '.implode(', ', $q['required_concepts']);
            }
        }

        $lines[] = '';
        $lines[] = 'Instructions:';
        $lines[] = 'Analyze the attached student assignment image(s). Compare the student answers with the Source of Truth.';
        $lines[] = 'Identify: answered questions, missing questions, incorrect answers, uncertain answers, unreadable areas.';
        $lines[] = 'Return the requested structured JSON. Remember: the administrator makes the final decision.';

        return implode("\n", $lines);
    }
}
