<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UnitKerja;
use App\Models\ProgramStudi;
use App\Models\Jabatan;
use App\Models\TahunAkademik;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Unit Kerja
        $lpm = UnitKerja::create([
            'kode_unit' => 'LPM',
            'nama_unit' => 'Lembaga Penjaminan Mutu',
            'deskripsi' => 'Lembaga yang bertanggung jawab atas penjaminan mutu institusi',
            'jenis_unit' => 'lembaga',
            'is_active' => true,
        ]);

        $fakultasTeknik = UnitKerja::create([
            'kode_unit' => 'FT',
            'nama_unit' => 'Fakultas Teknik',
            'deskripsi' => 'Fakultas Teknik',
            'jenis_unit' => 'fakultas',
            'is_active' => true,
        ]);

        $fakultasEkonomi = UnitKerja::create([
            'kode_unit' => 'FE',
            'nama_unit' => 'Fakultas Ekonomi dan Bisnis',
            'deskripsi' => 'Fakultas Ekonomi dan Bisnis',
            'jenis_unit' => 'fakultas',
            'is_active' => true,
        ]);

        // Seed Program Studi
        ProgramStudi::create([
            'kode_prodi' => 'TI',
            'nama_prodi' => 'Teknik Informatika',
            'unit_kerja_id' => $fakultasTeknik->id,
            'jenjang' => 'S1',
            'akreditasi' => 'A',
            'tanggal_akreditasi' => '2023-01-15',
            'kuota_mahasiswa' => 120,
            'is_active' => true,
        ]);

        ProgramStudi::create([
            'kode_prodi' => 'SI',
            'nama_prodi' => 'Sistem Informasi',
            'unit_kerja_id' => $fakultasTeknik->id,
            'jenjang' => 'S1',
            'akreditasi' => 'B',
            'tanggal_akreditasi' => '2022-06-20',
            'kuota_mahasiswa' => 100,
            'is_active' => true,
        ]);

        ProgramStudi::create([
            'kode_prodi' => 'MAN',
            'nama_prodi' => 'Manajemen',
            'unit_kerja_id' => $fakultasEkonomi->id,
            'jenjang' => 'S1',
            'akreditasi' => 'A',
            'tanggal_akreditasi' => '2023-03-10',
            'kuota_mahasiswa' => 150,
            'is_active' => true,
        ]);

        ProgramStudi::create([
            'kode_prodi' => 'AKT',
            'nama_prodi' => 'Akuntansi',
            'unit_kerja_id' => $fakultasEkonomi->id,
            'jenjang' => 'S1',
            'akreditasi' => 'B',
            'tanggal_akreditasi' => '2022-09-15',
            'kuota_mahasiswa' => 130,
            'is_active' => true,
        ]);

        // Seed Jabatan
        Jabatan::create([
            'kode_jabatan' => 'REKTOR',
            'nama_jabatan' => 'Rektor',
            'deskripsi' => 'Pimpinan tertinggi universitas',
            'kategori' => 'struktural',
            'level' => 1,
            'is_active' => true,
        ]);

        Jabatan::create([
            'kode_jabatan' => 'DEKAN',
            'nama_jabatan' => 'Dekan',
            'deskripsi' => 'Pimpinan fakultas',
            'kategori' => 'struktural',
            'level' => 2,
            'is_active' => true,
        ]);

        Jabatan::create([
            'kode_jabatan' => 'KAPRODI',
            'nama_jabatan' => 'Kepala Program Studi',
            'deskripsi' => 'Pimpinan program studi',
            'kategori' => 'struktural',
            'level' => 3,
            'is_active' => true,
        ]);

        Jabatan::create([
            'kode_jabatan' => 'DOSEN',
            'nama_jabatan' => 'Dosen',
            'deskripsi' => 'Tenaga pengajar',
            'kategori' => 'dosen',
            'level' => null,
            'is_active' => true,
        ]);

        Jabatan::create([
            'kode_jabatan' => 'TENDIK',
            'nama_jabatan' => 'Tenaga Kependidikan',
            'deskripsi' => 'Tenaga administrasi dan pendukung',
            'kategori' => 'tendik',
            'level' => null,
            'is_active' => true,
        ]);

        // Seed Tahun Akademik
        TahunAkademik::create([
            'kode_tahun' => '2023-GANJIL',
            'nama_tahun' => '2023/2024 Ganjil',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2023-09-01',
            'tanggal_selesai' => '2024-01-31',
            'is_active' => false,
        ]);

        TahunAkademik::create([
            'kode_tahun' => '2024-GENAP',
            'nama_tahun' => '2023/2024 Genap',
            'semester' => 'genap',
            'tanggal_mulai' => '2024-02-01',
            'tanggal_selesai' => '2024-06-30',
            'is_active' => false,
        ]);

        TahunAkademik::create([
            'kode_tahun' => '2024-GANJIL',
            'nama_tahun' => '2024/2025 Ganjil',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2024-09-01',
            'tanggal_selesai' => '2025-01-31',
            'is_active' => true,
        ]);
    }
}
