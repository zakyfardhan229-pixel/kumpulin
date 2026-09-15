<?php

namespace Database\Factories;

use App\Models\AiValidation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiValidation>
 */
class AiValidationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model' => 'gpt-5.5',
            'status' => AiValidation::STATUS_COMPLETED,
            'result' => AiValidation::RESULT_LIKELY_COMPLETED,
            'confidence' => 0.92,
            'analysis' => [
                'overall_result' => AiValidation::RESULT_LIKELY_COMPLETED,
                'confidence' => 0.92,
                'questions' => [],
                'missing_questions' => [],
                'unreadable_questions' => [],
                'summary' => 'Most required answers were detected.',
            ],
            'raw_response' => '{"overall_result":"likely_completed"}',
            'source_of_truth_version' => 1,
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ];
    }
}
