<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'responden',
        ]);

        User::factory()->create([
            'name' => 'Relawan User',
            'email' => 'relawan@example.com',
            'role' => 'relawan',
        ]);

        User::factory()->create([
            'name' => 'Psikolog User',
            'email' => 'psikolog@example.com',
            'role' => 'psikolog',
        ]);

        $this->call([
            ScreeningQuestionSeeder::class,
        ]);
    }
}
