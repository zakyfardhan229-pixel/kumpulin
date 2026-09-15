<?php

namespace Database\Factories;

use App\Models\AssignmentSession;
use App\Models\User;
use App\Services\SessionCodeService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentSession>
 */
class AssignmentSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subject = fake()->randomElement(['Informatika', 'Matematika', 'Fisika']);
        $date = fake()->dateTimeBetween('-1 week', '+2 weeks');

        return [
            'session_code' => app(SessionCodeService::class)->generate($subject, $date),
            'title' => 'Tugas '.$subject,
            'subject' => $subject,
            'description' => fake()->optional()->sentence(),
            'assignment_date' => $date->format('Y-m-d'),
            'status' => AssignmentSession::STATUS_ACTIVE,
            'created_by' => User::factory(),
        ];
    }
}
