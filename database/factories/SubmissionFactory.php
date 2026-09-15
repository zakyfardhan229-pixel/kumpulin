<?php

namespace Database\Factories;

use App\Models\AssignmentSession;
use App\Models\Submission;
use App\Services\SubmissionCodeService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_session_id' => AssignmentSession::factory(),
            'submission_code' => app(SubmissionCodeService::class)->generate(),
            'nama_lengkap' => fake()->name(),
            'kelas' => 'X RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'catatan' => fake()->optional()->sentence(),
            'status' => Submission::STATUS_PROCESSING,
        ];
    }
}
