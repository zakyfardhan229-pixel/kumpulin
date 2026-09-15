<?php

namespace Database\Factories;

use App\Models\SubmissionFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubmissionFile>
 */
class SubmissionFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disk' => 'local',
            'path' => 'submissions/test/'.fake()->uuid().'.jpg',
            'original_name' => 'tugas.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10000, 500000),
        ];
    }
}
