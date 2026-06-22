<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $developer = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
        $ktbo = Role::firstOrCreate(['name' => 'ktbo', 'guard_name' => 'web']);
        $registrator = Role::firstOrCreate(['name' => 'registrator', 'guard_name' => 'web']);

        // Create permissions
        $permissions = [
            // User management (only developer)
            'manage users',
            'create users',
            'edit users',
            'delete users',

            // Applicant management
            'view applicants',
            'approve applicants',
            'manage exam attempts',

            // Question management
            'manage questions',
            'create questions',
            'edit questions',
            'delete questions',

            // Results
            'view results',
            'export results',

            // System
            'access admin panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles

        // Developer: full access
        $developer->givePermissionTo(Permission::all());

        // KTBO: manage applicants, exams, questions, view results
        $ktbo->givePermissionTo([
            'view applicants',
            'approve applicants',
            'manage exam attempts',
            'manage questions',
            'create questions',
            'edit questions',
            'delete questions',
            'view results',
            'export results',
            'access admin panel',
        ]);

        // Registrator: view applicants and approve within assigned exam types
        $registrator->givePermissionTo([
            'view applicants',
            'approve applicants',
            'access admin panel',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
