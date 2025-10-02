<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all required roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $relawan = Role::firstOrCreate(['name' => 'relawan']);
        $psikolog = Role::firstOrCreate(['name' => 'psikolog']);
        $responden = Role::firstOrCreate(['name' => 'responden']);

        $this->command->info('All roles created: super_admin, admin, relawan, psikolog, responden');

        // Assign roles to existing users based on their current 'role' field
        $users = \App\Models\User::whereDoesntHave('roles')->get();

        foreach ($users as $user) {
            if ($user->role && Role::where('name', $user->role)->exists()) {
                $user->assignRole($user->role);
                $this->command->info("Assigned role '{$user->role}' to user: {$user->name}");
            }
        }

        $this->command->info('User role assignments completed!');
    }
}
