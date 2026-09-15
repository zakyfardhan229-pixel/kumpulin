<?php

namespace Database\Factories;

use App\Models\SourceOfTruth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceOfTruth>
 */
class SourceOfTruthFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'version' => 1,
            'questions' => [
                [
                    'question' => 'Jelaskan pengertian algoritma.',
                    'expected' => 'Langkah sistematis untuk menyelesaikan masalah.',
                    'validation_type' => SourceOfTruth::VALIDATION_SEMANTIC,
                    'required_concepts' => ['langkah sistematis', 'menyelesaikan masalah'],
                ],
            ],
        ];
    }
}
