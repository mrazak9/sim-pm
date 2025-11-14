<?php

namespace Database\Seeders;

use App\Models\PeriodeAkreditasi;
use App\Models\ProgramStudi;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PeriodeAkreditasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        PeriodeAkreditasi::query()->delete();

        // Get sample program studi and unit kerja (jika ada)
        $prodiSample = ProgramStudi::first();
        $unitKerjaSample = UnitKerja::first();

        // Sample Data: Periode Akreditasi Institusi
        PeriodeAkreditasi::create([
            'nama' => 'Akreditasi Institusi 2025',
            'jenis_akreditasi' => 'institusi',
            'lembaga' => 'BAN-PT',
            'instrumen' => '4.0',
            'jenjang' => null,
            'unit_kerja_id' => $unitKerjaSample?->id,
            'program_studi_id' => null,
            'tanggal_mulai' => Carbon::parse('2025-01-01'),
            'deadline_pengumpulan' => Carbon::parse('2025-06-30'),
            'jadwal_visitasi' => Carbon::parse('2025-07-15'),
            'tanggal_berakhir' => Carbon::parse('2025-08-31'),
            'status' => 'pengisian',
            'keterangan' => 'Periode akreditasi institusi untuk penilaian BAN-PT tahun 2025. Target peringkat: Unggul.',
            'metadata' => [
                'target_peringkat' => 'Unggul',
                'pic_name' => 'Tim LPM',
                'contact_email' => 'lpm@institusi.ac.id',
            ],
        ]);

        // Sample Data: Periode Akreditasi Program Studi (jika ada data prodi)
        if ($prodiSample) {
            PeriodeAkreditasi::create([
                'nama' => 'Akreditasi ' . $prodiSample->nama . ' 2025',
                'jenis_akreditasi' => 'program_studi',
                'lembaga' => 'BAN-PT',
                'instrumen' => '4.0',
                'jenjang' => $prodiSample->jenjang ?? 'S1',
                'unit_kerja_id' => $prodiSample->unit_kerja_id,
                'program_studi_id' => $prodiSample->id,
                'tanggal_mulai' => Carbon::parse('2025-02-01'),
                'deadline_pengumpulan' => Carbon::parse('2025-07-31'),
                'jadwal_visitasi' => Carbon::parse('2025-08-20'),
                'tanggal_berakhir' => Carbon::parse('2025-09-30'),
                'status' => 'persiapan',
                'keterangan' => 'Periode akreditasi program studi ' . $prodiSample->nama . ' untuk perpanjangan akreditasi BAN-PT.',
                'metadata' => [
                    'target_peringkat' => 'Baik Sekali',
                    'akreditasi_sebelumnya' => 'B',
                    'masa_berlaku_sebelumnya' => '2020-2025',
                ],
            ]);
        }

        // Sample Data: Periode Akreditasi Internasional (LAM/Internasional)
        if ($prodiSample) {
            PeriodeAkreditasi::create([
                'nama' => 'Akreditasi Internasional - ABET 2026',
                'jenis_akreditasi' => 'program_studi',
                'lembaga' => 'Internasional',
                'instrumen' => 'ABET',
                'jenjang' => 'S1',
                'unit_kerja_id' => $prodiSample->unit_kerja_id,
                'program_studi_id' => $prodiSample->id,
                'tanggal_mulai' => Carbon::parse('2025-09-01'),
                'deadline_pengumpulan' => Carbon::parse('2026-03-31'),
                'jadwal_visitasi' => Carbon::parse('2026-05-15'),
                'tanggal_berakhir' => Carbon::parse('2026-06-30'),
                'status' => 'persiapan',
                'keterangan' => 'Persiapan akreditasi internasional ABET untuk program studi Teknik.',
                'metadata' => [
                    'target_peringkat' => 'Accredited',
                    'persiapan_required' => [
                        'Self-Study Report',
                        'Program Educational Objectives (PEOs)',
                        'Student Outcomes (SOs)',
                        'Continuous Improvement Process',
                    ],
                ],
            ]);
        }

        $this->command->info('✅ Periode Akreditasi sample data berhasil di-seed!');
        $this->command->info('   Total: ' . PeriodeAkreditasi::count() . ' periode akreditasi');
    }
}
