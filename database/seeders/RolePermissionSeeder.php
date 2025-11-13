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

            // IKU Target management
            'iku-target.view',
            'iku-target.create',
            'iku-target.edit',
            'iku-target.delete',

            // IKU Progress management
            'iku-progress.view',
            'iku-progress.create',
            'iku-progress.edit',
            'iku-progress.delete',

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
            'iku-target.view', 'iku-target.create', 'iku-target.edit',
            'iku-progress.view', 'iku-progress.create', 'iku-progress.edit',
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
            'iku-target.view', 'iku-target.create', 'iku-target.edit',
            'iku-progress.view', 'iku-progress.create', 'iku-progress.edit',
            'survey.view', 'survey.respond',
            'spmi.view', 'spmi.create', 'spmi.edit',
            'dashboard.view', 'report.view',
        ]);

        // Staff - basic access
        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            'document.view', 'document.create',
            'audit.view',
            'iku.view',
            'iku-target.view',
            'iku-progress.view', 'iku-progress.create',
            'survey.view', 'survey.respond',
            'dashboard.view',
        ]);

        // Regular User - minimal access
        $user = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            'survey.respond',
            'dashboard.view',
        ]);

        // Get UnitKerja and Jabatan IDs for demo users
        $lpm = \App\Models\UnitKerja::where('kode_unit', 'LPM')->first();
        $fakultasTeknik = \App\Models\UnitKerja::where('kode_unit', 'FT')->first();
        $jabatanRektor = \App\Models\Jabatan::where('kode_jabatan', 'REKTOR')->first();
        $jabatanDekan = \App\Models\Jabatan::where('kode_jabatan', 'DEKAN')->first();
        $jabatanDosen = \App\Models\Jabatan::where('kode_jabatan', 'DOSEN')->first();

        // Create default super admin user
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sim-pm.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'nip' => '198001012005011001',
            'phone' => '081234567890',
            'unit_kerja_id' => $lpm?->id,
            'jabatan_id' => $jabatanRektor?->id,
            'is_active' => true,
        ]);
        $superAdminUser->assignRole('super-admin');

        // Create demo users
        $demoAdmin = User::create([
            'name' => 'Demo Admin',
            'email' => 'demo@sim-pm.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'nip' => '198505152010121002',
            'phone' => '081234567891',
            'unit_kerja_id' => $fakultasTeknik?->id,
            'jabatan_id' => $jabatanDekan?->id,
            'is_active' => true,
        ]);
        $demoAdmin->assignRole('admin');

        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'user@sim-pm.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'nip' => '199203202015031003',
            'nidn' => '0020039201',
            'phone' => '081234567892',
            'unit_kerja_id' => $fakultasTeknik?->id,
            'jabatan_id' => $jabatanDosen?->id,
            'is_active' => true,
        ]);
        $demoUser->assignRole('user');
    }
}
