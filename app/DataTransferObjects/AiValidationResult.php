<?php

namespace App\DataTransferObjects;

readonly class AiValidationResult
{
    /**
     * @param  array<int, array{number: int, status: string, confidence: float, reason: string}>  $questions
     * @param  array<int, mixed>  $missingQuestions
     * @param  array<int, mixed>  $unreadableQuestions
     */
    public function __construct(
        public string $overallResult,
        public float $confidence,
        public array $questions,
        public array $missingQuestions,
        public array $unreadableQuestions,
        public string $summary,
        public string $rawText,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'overall_result' => $this->overallResult,
            'confidence' => $this->confidence,
            'questions' => $this->questions,
            'missing_questions' => $this->missingQuestions,
            'unreadable_questions' => $this->unreadableQuestions,
            'summary' => $this->summary,
        ];
    }
}
