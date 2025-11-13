<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Document management
            'document.view',
            'document.create',
            'document.edit',
            'document.delete',
            'document.approve',

            // Audit management
            'audit.view',
            'audit.create',
            'audit.edit',
            'audit.delete',

            // Accreditation management
            'accreditation.view',
            'accreditation.create',
            'accreditation.edit',
            'accreditation.delete',

            // IKU management
            'iku.view',
            'iku.create',
            'iku.edit',
            'iku.delete',

            // Survey/Questionnaire management
            'survey.view',
            'survey.create',
            'survey.edit',
            'survey.delete',
            'survey.respond',

            // SPMI management
            'spmi.view',
            'spmi.create',
            'spmi.edit',
            'spmi.delete',

            // Dashboard/Reports
            'dashboard.view',
            'report.view',
            'report.export',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - all permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - most permissions except user management
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'document.view', 'document.create', 'document.edit', 'document.approve',
            'audit.view', 'audit.create', 'audit.edit',
            'accreditation.view', 'accreditation.create', 'accreditation.edit',
            'iku.view', 'iku.create', 'iku.edit',
            'survey.view', 'survey.create', 'survey.edit',
            'spmi.view', 'spmi.create', 'spmi.edit',
            'dashboard.view', 'report.view', 'report.export',
        ]);

        // Auditor - audit and document view
        $auditor = Role::create(['name' => 'auditor']);
        $auditor->givePermissionTo([
            'document.view',
            'audit.view', 'audit.create', 'audit.edit',
            'dashboard.view', 'report.view',
        ]);

        // Unit Head - unit-level management
        $unitHead = Role::create(['name' => 'unit-head']);
        $unitHead->givePermissionTo([
            'document.view', 'document.create', 'document.edit',
            'audit.view',
            'accreditation.view', 'accreditation.create', 'accreditation.edit',
            'iku.view', 'iku.create', 'iku.edit',
            'survey.view', 'survey.respond',
            'spmi.view', 'spmi.create', 'spmi.edit',
            'dashboard.view', 'report.view',
        ]);

        // Staff - basic access
        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            'document.view', 'document.create',
            'audit.view',
            'survey.view', 'survey.respond',
            'dashboard.view',
        ]);

        // Regular User - minimal access
        $user = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            'survey.respond',
            'dashboard.view',
        ]);

        // Create default super admin user
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sim-pm.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdminUser->assignRole('super-admin');

        // Create demo users
        $demoAdmin = User::create([
            'name' => 'Demo Admin',
            'email' => 'demo@sim-pm.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $demoAdmin->assignRole('admin');

        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'user@sim-pm.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $demoUser->assignRole('user');
    }
}
