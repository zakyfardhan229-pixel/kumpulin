<?php

namespace Database\Factories;

use App\Models\AdminReview;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminReview>
 */
class AdminReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'admin_id' => User::factory(),
            'decision' => Submission::STATUS_COMPLETED,
            'note' => fake()->optional()->sentence(),
            'reviewed_at' => now(),
        ];
    }
}
