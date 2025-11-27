<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();

        // 1. Seed Roles & Permissions (Foundation)
        $this->command->info('📋 Step 1: Seeding Roles & Permissions');
        $this->call(RolePermissionSeeder::class);
        $this->command->newLine();

        // 2. Seed Master Data (Unit Kerja, Program Studi, Jabatan, Tahun Akademik)
        $this->command->info('📋 Step 2: Seeding Master Data');
        $this->call(MasterDataSeeder::class);
        $this->command->newLine();

        // 3. Seed Instrumen Akreditasi
        $this->command->info('📋 Step 3: Seeding Instrumen Akreditasi');
        $this->call(InstrumenAkreditasiSeeder::class);
        $this->command->newLine();

        // 4. Seed IKU Data
        $this->command->info('📋 Step 4: Seeding IKU Data');
        $this->call(IKUSeeder::class);
        $this->command->newLine();

        // 5. Seed Template Butir Akreditasi
        $this->command->info('📋 Step 5: Seeding Template Butir Akreditasi');
        $this->call([
            ButirAkreditasiSeeder::class,     // Template BANPT 4.0 & 9.0
            ButirAkreditasiLAMSeeder::class,  // Template LAMEMBA & LAMINFOKOM
        ]);
        $this->command->newLine();

        // 6. Seed Periode Akreditasi (Sample Data)
        $this->command->info('📋 Step 6: Seeding Periode Akreditasi');
        $this->call(PeriodeAkreditasiSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
    }
}
