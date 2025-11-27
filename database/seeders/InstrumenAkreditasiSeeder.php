<?php

namespace Database\Seeders;

use App\Models\InstrumenAkreditasi;
use Illuminate\Database\Seeder;

class InstrumenAkreditasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instrumens = [
            // Instrumen Program Studi
            [
                'kode' => '4.0',
                'nama' => 'IAPT 4.0',
                'deskripsi' => 'Instrumen Akreditasi Program Studi 4.0',
                'jenis' => 'program_studi',
                'lembaga' => 'BAN-PT',
                'tahun_berlaku' => 2019,
                'is_active' => true,
            ],
            [
                'kode' => '9.0',
                'nama' => 'IAPT 9.0',
                'deskripsi' => 'Instrumen Akreditasi Program Studi 9.0 (Siklus 3)',
                'jenis' => 'program_studi',
                'lembaga' => 'BAN-PT',
                'tahun_berlaku' => 2023,
                'is_active' => true,
            ],

            // Instrumen Institusi
            [
                'kode' => 'IAPS 4.0',
                'nama' => 'IAPS 4.0',
                'deskripsi' => 'Instrumen Akreditasi Perguruan Tinggi 4.0',
                'jenis' => 'institusi',
                'lembaga' => 'BAN-PT',
                'tahun_berlaku' => 2019,
                'is_active' => true,
            ],
            [
                'kode' => 'IAPS 9.0',
                'nama' => 'IAPS 9.0',
                'deskripsi' => 'Instrumen Akreditasi Perguruan Tinggi 9.0 (Siklus 3)',
                'jenis' => 'institusi',
                'lembaga' => 'BAN-PT',
                'tahun_berlaku' => 2023,
                'is_active' => true,
            ],

            // LAM - Teknik
            [
                'kode' => 'LAM-TEK 2023',
                'nama' => 'LAM-TEK 2023',
                'deskripsi' => 'Instrumen Akreditasi LAM-TEKnik 2023',
                'jenis' => 'program_studi',
                'lembaga' => 'LAM',
                'tahun_berlaku' => 2023,
                'is_active' => true,
            ],

            // LAM - Kesehatan
            [
                'kode' => 'LAM-KES 2023',
                'nama' => 'LAM-PTKes 2023',
                'deskripsi' => 'Instrumen Akreditasi LAM-PTKes 2023',
                'jenis' => 'program_studi',
                'lembaga' => 'LAM',
                'tahun_berlaku' => 2023,
                'is_active' => true,
            ],

            // LAM - Ekonomi
            [
                'kode' => 'LAM-INFOKOM 2023',
                'nama' => 'LAM-INFOKOM 2023',
                'deskripsi' => 'Instrumen Akreditasi LAM-INFOKOM 2023',
                'jenis' => 'program_studi',
                'lembaga' => 'LAM',
                'tahun_berlaku' => 2023,
                'is_active' => true,
            ],
        ];

        foreach ($instrumens as $instrumen) {
            InstrumenAkreditasi::updateOrCreate(
                ['kode' => $instrumen['kode']],
                $instrumen
            );
        }
    }
}
