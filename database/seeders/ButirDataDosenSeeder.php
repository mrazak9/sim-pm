<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ButirAkreditasi;
use App\Models\PeriodeAkreditasi;
use App\Models\PengisianButir;
use App\Services\ButirMappingService;
use App\Services\ButirDataService;

class ButirDataDosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mappingService = new ButirMappingService();
        $dataService = new ButirDataService();

        // Create atau ambil butir untuk data dosen
        $butir = ButirAkreditasi::where('kode', 'LIKE', '%C%')
            ->where('nama', 'LIKE', '%Dosen%')
            ->first();

        if (!$butir) {
            // Create butir baru jika belum ada
            $butir = ButirAkreditasi::create([
                'kode' => 'C.1',
                'nama' => 'Data Dosen Tetap',
                'deskripsi' => 'Data dosen tetap program studi',
                'instrumen' => 'APT 2.0',
                'kategori' => 'Sumber Daya Manusia',
                'bobot' => 10,
                'is_mandatory' => true,
                'urutan' => 1,
            ]);
        }

        // Setup mapping untuk butir dosen
        $this->info('Setting up column mappings for butir dosen...');

        try {
            $mappingService->setupMappings($butir->id, [
                [
                    'name' => 'nama_dosen',
                    'label' => 'Nama Dosen',
                    'type' => 'text',
                    'required' => true,
                    'width' => '20%',
                    'placeholder' => 'Masukkan nama lengkap dosen...',
                ],
                [
                    'name' => 'nip',
                    'label' => 'NIP',
                    'type' => 'text',
                    'required' => true,
                    'width' => '15%',
                    'placeholder' => 'NIP 18 digit',
                ],
                [
                    'name' => 'nidn',
                    'label' => 'NIDN/NIDK',
                    'type' => 'text',
                    'required' => true,
                    'width' => '12%',
                    'placeholder' => 'NIDN 10 digit',
                ],
                [
                    'name' => 'pendidikan',
                    'label' => 'Pendidikan Terakhir',
                    'type' => 'select',
                    'required' => true,
                    'width' => '10%',
                    'options' => [
                        'S1' => 'Sarjana (S1)',
                        'S2' => 'Magister (S2)',
                        'S3' => 'Doktor (S3)',
                    ],
                ],
                [
                    'name' => 'jafung',
                    'label' => 'Jabatan Fungsional',
                    'type' => 'select',
                    'required' => true,
                    'width' => '15%',
                    'options' => [
                        'Asisten Ahli' => 'Asisten Ahli',
                        'Lektor' => 'Lektor',
                        'Lektor Kepala' => 'Lektor Kepala',
                        'Guru Besar' => 'Guru Besar',
                    ],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'required' => true,
                    'width' => '10%',
                    'options' => [
                        'Aktif' => 'Aktif',
                        'Tugas Belajar' => 'Tugas Belajar',
                        'Pensiun' => 'Pensiun',
                    ],
                ],
                [
                    'name' => 'unit_kerja',
                    'label' => 'Unit Kerja',
                    'type' => 'text',
                    'width' => '15%',
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'width' => '15%',
                    'placeholder' => 'email@univ.ac.id',
                ],
                [
                    'name' => 'hp',
                    'label' => 'No. HP',
                    'type' => 'text',
                    'width' => '12%',
                    'placeholder' => '08xxxxxxxxxx',
                ],
                [
                    'name' => 'tahun_masuk',
                    'label' => 'Tahun Masuk',
                    'type' => 'number',
                    'width' => '10%',
                    'min' => 1990,
                    'max' => 2030,
                ],
            ]);

            $this->info('Column mappings created successfully!');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->info('Column mappings already exist, skipping...');
            } else {
                throw $e;
            }
        }

        // Cari atau buat periode akreditasi
        $periode = PeriodeAkreditasi::first();

        if (!$periode) {
            $this->warn('No periode akreditasi found. Creating sample periode...');
            $periode = PeriodeAkreditasi::create([
                'nama' => 'Akreditasi 2024',
                'tahun' => 2024,
                'instrumen' => 'APT 2.0',
                'status' => 'aktif',
                'tanggal_mulai' => now()->startOfYear(),
                'tanggal_selesai' => now()->endOfYear(),
            ]);
        }

        // Cari atau buat pengisian butir
        $pengisian = PengisianButir::where('periode_akreditasi_id', $periode->id)
            ->where('butir_akreditasi_id', $butir->id)
            ->first();

        if (!$pengisian) {
            $pengisian = PengisianButir::create([
                'periode_akreditasi_id' => $periode->id,
                'butir_akreditasi_id' => $butir->id,
                'status' => 'draft',
            ]);
        }

        // Seed sample data dosen
        $this->info('Seeding dosen data...');

        $dosenData = [
            [
                'nama_dosen' => 'Dr. Ahmad Santoso, M.Kom',
                'nip' => '198501012010121001',
                'nidn' => '0101018501',
                'pendidikan' => 'S3',
                'jafung' => 'Lektor Kepala',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'ahmad.santoso@univ.ac.id',
                'hp' => '081234567890',
                'tahun_masuk' => 2010,
            ],
            [
                'nama_dosen' => 'Prof. Dr. Budi Raharjo, M.T',
                'nip' => '197801012005011001',
                'nidn' => '0101017801',
                'pendidikan' => 'S3',
                'jafung' => 'Guru Besar',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'budi.raharjo@univ.ac.id',
                'hp' => '081234567891',
                'tahun_masuk' => 2005,
            ],
            [
                'nama_dosen' => 'Citra Dewi, S.Kom, M.Cs',
                'nip' => '199001012015082001',
                'nidn' => '0101019001',
                'pendidikan' => 'S2',
                'jafung' => 'Lektor',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'citra.dewi@univ.ac.id',
                'hp' => '081234567892',
                'tahun_masuk' => 2015,
            ],
            [
                'nama_dosen' => 'Dr. Eko Prasetyo, M.Kom',
                'nip' => '198802152012121002',
                'nidn' => '0215028802',
                'pendidikan' => 'S3',
                'jafung' => 'Lektor',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'eko.prasetyo@univ.ac.id',
                'hp' => '081234567893',
                'tahun_masuk' => 2012,
            ],
            [
                'nama_dosen' => 'Fitri Handayani, S.T, M.T',
                'nip' => '199203102017082002',
                'nidn' => '0310039202',
                'pendidikan' => 'S2',
                'jafung' => 'Asisten Ahli',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'fitri.handayani@univ.ac.id',
                'hp' => '081234567894',
                'tahun_masuk' => 2017,
            ],
        ];

        $dataService->bulkCreate($pengisian->id, $dosenData);

        $this->info('Successfully seeded ' . count($dosenData) . ' dosen records!');
    }

    /**
     * Helper to output info message
     */
    protected function info($message)
    {
        $this->command->info($message);
    }

    /**
     * Helper to output warning message
     */
    protected function warn($message)
    {
        $this->command->warn($message);
    }
}
