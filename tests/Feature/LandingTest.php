<?php

namespace Tests\Feature;

use App\Models\AssignmentSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kumpulin!')
            ->assertSee('Mulai Kumpulkan')
            ->assertSee('Cek Pengumpulan');
    }

    public function test_landing_redirects_session_code_to_submit_form(): void
    {
        $session = AssignmentSession::factory()->create();

        $this->get(route('home', ['session' => strtolower($session->session_code)]))
            ->assertRedirect(route('student.submit', $session->session_code));
    }

    public function test_flash_status_renders_as_toast(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'title' => 'Tugas Informatika',
            'subject' => 'Informatika',
            'assignment_date' => '2026-09-29',
            'questions' => [
                [
                    'question' => 'Q?',
                    'expected' => 'E.',
                    'validation_type' => 'semantic',
                    'required_concepts' => '',
                ],
            ],
        ]);

        $response->assertSessionHas('status', 'Session berhasil dibuat.');
        $this->followRedirects($response)->assertSee('id="toast"', false)->assertSee('Session berhasil dibuat.');
    }
}
