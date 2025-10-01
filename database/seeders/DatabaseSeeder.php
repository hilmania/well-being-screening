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
        // Seed roles and permissions first
        $this->call([
            RoleSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('super_admin');

        // Create test users with roles
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'responden',
        ]);
        $testUser->assignRole('responden');

        $relawan = User::factory()->create([
            'name' => 'Relawan User',
            'email' => 'relawan@example.com',
            'role' => 'relawan',
        ]);
        $relawan->assignRole('relawan');

        $psikolog = User::factory()->create([
            'name' => 'Psikolog User',
            'email' => 'psikolog@example.com',
            'role' => 'psikolog',
        ]);
        $psikolog->assignRole('psikolog');

        $this->call([
            ScreeningQuestionSeeder::class,
        ]);
    }
}
