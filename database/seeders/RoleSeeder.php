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

        // Create additional roles that don't exist yet
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $responden = Role::firstOrCreate(['name' => 'responden']);

        $this->command->info('Additional roles created: admin, responden');

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
