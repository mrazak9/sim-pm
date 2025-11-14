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
        // Seed Master Data first (if needed)
        // $this->call(MasterDataSeeder::class);
        // $this->call(RolePermissionSeeder::class);

        // Seed Akreditasi Data
        $this->call([
            ButirAkreditasiSeeder::class,  // Template BANPT 4.0
            ButirAkreditasiLAMSeeder::class, // Template LAMEMBA & LAMINFOKOM
            PeriodeAkreditasiSeeder::class,
        ]);
    }
}
