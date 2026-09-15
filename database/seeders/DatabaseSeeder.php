<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default local admin (dev only). Email/password: admin@kumpulin.test / password
        User::factory()->create([
            'name' => 'Admin Kumpulin',
            'email' => 'admin@kumpulin.test',
        ]);
    }
}
