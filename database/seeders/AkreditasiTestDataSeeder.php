<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ButirAkreditasi;
use App\Models\PeriodeAkreditasi;
use App\Models\PengisianButir;
use App\Models\User;
use App\Services\ButirMappingService;
use App\Services\ButirDataService;
use App\Services\ButirAkreditasiService;
use Illuminate\Support\Facades\DB;

class AkreditasiTestDataSeeder extends Seeder
{
    protected $mappingService;
    protected $dataService;
    protected $butirService;

    public function __construct()
    {
        $this->mappingService = new ButirMappingService();
        $this->dataService = new ButirDataService();
        $this->butirService = new ButirAkreditasiService(
            app(\App\Repositories\ButirAkreditasiRepository::class)
        );
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Akreditasi Test Data Seeding (Column Mapping System)...');
        $this->command->newLine();

        // Get periode akreditasi (first one)
        $periode = PeriodeAkreditasi::first();

        if (!$periode) {
            $this->command->error('❌ No periode akreditasi found. Please run PeriodeAkreditasiSeeder first.');
            return;
        }

        $this->command->info("📋 Using Periode: {$periode->nama}");
        $this->command->newLine();

        // Get or create PIC user
        $pic = User::first();
        if (!$pic) {
            $this->command->warn('⚠️  No user found. Creating dummy user for PIC...');
            $pic = User::create([
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'nip' => '199001012020121001',
            ]);
        }

        // Copy butir templates to periode and setup data
        $this->command->info('Step 1: Copying butir templates to periode...');
        $this->copyButirsToPeriode($periode);
        $this->command->newLine();

        $this->command->info('Step 2: Setting up column mappings and sample data...');
        $this->setupButirDosen($periode, $pic);
        $this->setupButirPublikasi($periode, $pic);
        $this->setupButirPKM($periode, $pic);
        $this->setupButirMahasiswa($periode, $pic);
        $this->command->newLine();

        $this->command->info('✅ Akreditasi Test Data Seeding Completed!');
        $this->command->info('   Total Butir in Periode: ' . ButirAkreditasi::where('periode_akreditasi_id', $periode->id)->count());
        $this->command->info('   Total Pengisian: ' . PengisianButir::where('periode_akreditasi_id', $periode->id)->count());
        $this->command->newLine();
    }

    /**
     * Copy template butirs to periode
     */
    protected function copyButirsToPeriode($periode)
    {
        // Get template butirs (first 10 for testing)
        $templateButirs = ButirAkreditasi::whereNull('periode_akreditasi_id')
            ->whereNull('parent_id')
            ->take(10)
            ->get();

        foreach ($templateButirs as $template) {
            try {
                $this->butirService->copyFromTemplate($template->id, $periode->id);
                $this->command->info("  ✓ Copied: {$template->kode} - {$template->nama}");
            } catch (\Exception $e) {
                // Skip if already exists
                if (strpos($e->getMessage(), 'sudah ada') !== false) {
                    $this->command->comment("  - Skipped (exists): {$template->kode}");
                } else {
                    $this->command->warn("  ! Error copying {$template->kode}: {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Setup Butir Data Dosen
     */
    protected function setupButirDosen($periode, $pic)
    {
        $this->command->info('  📊 Setting up: Data Dosen Tetap');

        // Find or create butir
        $butir = ButirAkreditasi::firstOrCreate([
            'periode_akreditasi_id' => $periode->id,
            'kode' => 'SDM.1',
        ], [
            'nama' => 'Data Dosen Tetap Program Studi',
            'deskripsi' => 'Data dosen tetap yang ditugaskan sebagai pengampu mata kuliah',
            'instrumen' => $periode->instrumen,
            'kategori' => 'Sumber Daya Manusia',
            'bobot' => 10,
            'urutan' => 1,
        ]);

        // Setup column mapping
        try {
            $this->mappingService->setupMappings($butir->id, [
                ['name' => 'nama_dosen', 'label' => 'Nama Lengkap Dosen', 'type' => 'text', 'required' => true, 'width' => '20%'],
                ['name' => 'nip', 'label' => 'NIP', 'type' => 'text', 'required' => true, 'width' => '12%'],
                ['name' => 'nidn', 'label' => 'NIDN/NIDK', 'type' => 'text', 'required' => true, 'width' => '10%'],
                ['name' => 'pendidikan', 'label' => 'Pendidikan Terakhir', 'type' => 'select', 'required' => true, 'width' => '10%',
                 'options' => ['S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3']],
                ['name' => 'bidang_keahlian', 'label' => 'Bidang Keahlian', 'type' => 'text', 'width' => '15%'],
                ['name' => 'jabatan_akademik', 'label' => 'Jabatan Akademik', 'type' => 'select', 'required' => true, 'width' => '12%',
                 'options' => ['Asisten Ahli' => 'Asisten Ahli', 'Lektor' => 'Lektor', 'Lektor Kepala' => 'Lektor Kepala', 'Guru Besar' => 'Guru Besar']],
                ['name' => 'sertifikat_pendidik', 'label' => 'Sertifikat Pendidik', 'type' => 'select', 'width' => '10%',
                 'options' => ['Ada' => 'Ada', 'Tidak Ada' => 'Tidak Ada']],
                ['name' => 'mata_kuliah', 'label' => 'Mata Kuliah yang Diampu', 'type' => 'text', 'width' => '18%'],
                ['name' => 'kesesuaian', 'label' => 'Kesesuaian Bidang', 'type' => 'select', 'width' => '10%',
                 'options' => ['Sesuai' => 'Sesuai', 'Tidak Sesuai' => 'Tidak Sesuai']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'width' => '8%',
                 'options' => ['Aktif' => 'Aktif', 'Tugas Belajar' => 'Tugas Belajar', 'Pensiun' => 'Pensiun']],
            ]);

            // Create pengisian
            $pengisian = PengisianButir::firstOrCreate([
                'periode_akreditasi_id' => $periode->id,
                'butir_akreditasi_id' => $butir->id,
            ], [
                'pic_user_id' => $pic->id,
                'status' => 'draft',
            ]);

            // Sample data
            $dosenData = [
                ['nama_dosen' => 'Prof. Dr. Ir. Budi Santoso, M.T.', 'nip' => '196801011993031001', 'nidn' => '0101016801', 'pendidikan' => 'S3', 'bidang_keahlian' => 'Rekayasa Perangkat Lunak', 'jabatan_akademik' => 'Guru Besar', 'sertifikat_pendidik' => 'Ada', 'mata_kuliah' => 'Rekayasa Perangkat Lunak, Manajemen Proyek TI', 'kesesuaian' => 'Sesuai', 'status' => 'Aktif'],
                ['nama_dosen' => 'Dr. Siti Nurhaliza, S.Kom, M.Kom', 'nip' => '197505152005012001', 'nidn' => '0115057501', 'pendidikan' => 'S3', 'bidang_keahlian' => 'Kecerdasan Buatan', 'jabatan_akademik' => 'Lektor Kepala', 'sertifikat_pendidik' => 'Ada', 'mata_kuliah' => 'Kecerdasan Buatan, Machine Learning', 'kesesuaian' => 'Sesuai', 'status' => 'Aktif'],
                ['nama_dosen' => 'Ahmad Fauzi, S.T., M.Cs', 'nip' => '198203102010121002', 'nidn' => '0110038201', 'pendidikan' => 'S2', 'bidang_keahlian' => 'Jaringan Komputer', 'jabatan_akademik' => 'Lektor', 'sertifikat_pendidik' => 'Ada', 'mata_kuliah' => 'Jaringan Komputer, Keamanan Sistem', 'kesesuaian' => 'Sesuai', 'status' => 'Aktif'],
                ['nama_dosen' => 'Dr. Eng. Dewi Kusuma, S.Kom, M.T.', 'nip' => '198807202015082001', 'nidn' => '0120078801', 'pendidikan' => 'S3', 'bidang_keahlian' => 'Sistem Informasi', 'jabatan_akademik' => 'Lektor', 'sertifikat_pendidik' => 'Ada', 'mata_kuliah' => 'Sistem Informasi, Basis Data', 'kesesuaian' => 'Sesuai', 'status' => 'Aktif'],
                ['nama_dosen' => 'Rizky Pratama, S.Kom, M.Kom', 'nip' => '199001152018031001', 'nidn' => '0115019001', 'pendidikan' => 'S2', 'bidang_keahlian' => 'Pemrograman', 'jabatan_akademik' => 'Asisten Ahli', 'sertifikat_pendidik' => 'Ada', 'mata_kuliah' => 'Algoritma Pemrograman, Struktur Data', 'kesesuaian' => 'Sesuai', 'status' => 'Aktif'],
                ['nama_dosen' => 'Dr. Hendra Wijaya, S.Si, M.Kom', 'nip' => '197912102008121001', 'nidn' => '0110127901', 'pendidikan' => 'S3', 'bidang_keahlian' => 'Komputasi', 'jabatan_akademik' => 'Lektor Kepala', 'sertifikat_pendidik' => 'Ada', 'mata_kuliah' => 'Matematika Diskrit, Teori Komputasi', 'kesesuaian' => 'Sesuai', 'status' => 'Tugas Belajar'],
            ];

            $this->dataService->bulkCreate($pengisian->id, $dosenData);
            $this->command->info('    ✓ Created ' . count($dosenData) . ' dosen records');

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->command->comment('    - Mapping already exists');
            } else {
                $this->command->warn("    ! Error: {$e->getMessage()}");
            }
        }
    }

    /**
     * Setup Butir Publikasi
     */
    protected function setupButirPublikasi($periode, $pic)
    {
        $this->command->info('  📚 Setting up: Data Publikasi Ilmiah');

        $butir = ButirAkreditasi::firstOrCreate([
            'periode_akreditasi_id' => $periode->id,
            'kode' => 'PEN.1',
        ], [
            'nama' => 'Publikasi Ilmiah Dosen',
            'deskripsi' => 'Publikasi ilmiah yang dihasilkan oleh dosen dalam 3 tahun terakhir',
            'instrumen' => $periode->instrumen,
            'kategori' => 'Penelitian',
            'bobot' => 15,
            'urutan' => 2,
        ]);

        try {
            $this->mappingService->setupMappings($butir->id, [
                ['name' => 'judul', 'label' => 'Judul Artikel/Paper', 'type' => 'text', 'required' => true, 'width' => '25%'],
                ['name' => 'penulis', 'label' => 'Nama Penulis', 'type' => 'text', 'required' => true, 'width' => '15%'],
                ['name' => 'jurnal', 'label' => 'Nama Jurnal/Prosiding', 'type' => 'text', 'required' => true, 'width' => '18%'],
                ['name' => 'tahun', 'label' => 'Tahun', 'type' => 'number', 'required' => true, 'width' => '8%', 'min' => 2020, 'max' => 2025],
                ['name' => 'volume', 'label' => 'Volume/Issue', 'type' => 'text', 'width' => '8%'],
                ['name' => 'tingkat', 'label' => 'Tingkat', 'type' => 'select', 'required' => true, 'width' => '12%',
                 'options' => [
                     'Internasional Bereputasi' => 'Internasional Bereputasi',
                     'Internasional' => 'Internasional',
                     'Nasional Terakreditasi' => 'Nasional Terakreditasi',
                     'Nasional' => 'Nasional'
                 ]],
                ['name' => 'url', 'label' => 'Link/DOI', 'type' => 'text', 'width' => '14%'],
            ]);

            $pengisian = PengisianButir::firstOrCreate([
                'periode_akreditasi_id' => $periode->id,
                'butir_akreditasi_id' => $butir->id,
            ], [
                'pic_user_id' => $pic->id,
                'status' => 'draft',
            ]);

            $publikasiData = [
                ['judul' => 'Deep Learning Approach for Image Recognition in Medical Imaging', 'penulis' => 'Siti Nurhaliza, Ahmad Fauzi', 'jurnal' => 'IEEE Transactions on Medical Imaging', 'tahun' => 2024, 'volume' => 'Vol 43, No 2', 'tingkat' => 'Internasional Bereputasi', 'url' => 'https://doi.org/10.1109/TMI.2024.xxxxx'],
                ['judul' => 'Optimasi Algoritma Genetika untuk Penjadwalan Mata Kuliah', 'penulis' => 'Budi Santoso, Dewi Kusuma', 'jurnal' => 'Jurnal Teknologi Informasi Indonesia', 'tahun' => 2024, 'volume' => 'Vol 15, No 1', 'tingkat' => 'Nasional Terakreditasi', 'url' => 'https://jurnal.ac.id/xxx'],
                ['judul' => 'Blockchain Technology for Secure Healthcare Data Management', 'penulis' => 'Ahmad Fauzi, Hendra Wijaya', 'jurnal' => 'International Journal of Computer Science', 'tahun' => 2023, 'volume' => 'Vol 18, No 4', 'tingkat' => 'Internasional', 'url' => 'https://doi.org/10.xxxx/ijcs.2023.xxx'],
                ['judul' => 'Implementasi IoT untuk Smart Campus: Studi Kasus', 'penulis' => 'Rizky Pratama', 'jurnal' => 'Seminar Nasional Teknologi Informasi', 'tahun' => 2023, 'volume' => '2023', 'tingkat' => 'Nasional', 'url' => ''],
            ];

            $this->dataService->bulkCreate($pengisian->id, $publikasiData);
            $this->command->info('    ✓ Created ' . count($publikasiData) . ' publikasi records');

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->command->comment('    - Mapping already exists');
            } else {
                $this->command->warn("    ! Error: {$e->getMessage()}");
            }
        }
    }

    /**
     * Setup Butir PKM
     */
    protected function setupButirPKM($periode, $pic)
    {
        $this->command->info('  🤝 Setting up: Data PKM');

        $butir = ButirAkreditasi::firstOrCreate([
            'periode_akreditasi_id' => $periode->id,
            'kode' => 'PKM.1',
        ], [
            'nama' => 'Pengabdian kepada Masyarakat',
            'deskripsi' => 'Kegiatan pengabdian kepada masyarakat yang dilaksanakan oleh dosen',
            'instrumen' => $periode->instrumen,
            'kategori' => 'Pengabdian Masyarakat',
            'bobot' => 10,
            'urutan' => 3,
        ]);

        try {
            $this->mappingService->setupMappings($butir->id, [
                ['name' => 'judul_kegiatan', 'label' => 'Judul Kegiatan PKM', 'type' => 'text', 'required' => true, 'width' => '25%'],
                ['name' => 'ketua', 'label' => 'Ketua Pelaksana', 'type' => 'text', 'required' => true, 'width' => '15%'],
                ['name' => 'anggota', 'label' => 'Anggota Tim', 'type' => 'text', 'width' => '15%'],
                ['name' => 'mitra', 'label' => 'Mitra PKM', 'type' => 'text', 'required' => true, 'width' => '15%'],
                ['name' => 'tahun', 'label' => 'Tahun', 'type' => 'number', 'required' => true, 'width' => '8%'],
                ['name' => 'dana', 'label' => 'Dana (Rp)', 'type' => 'number', 'width' => '12%'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'width' => '10%',
                 'options' => ['Selesai' => 'Selesai', 'Berjalan' => 'Berjalan', 'Rencana' => 'Rencana']],
            ]);

            $pengisian = PengisianButir::firstOrCreate([
                'periode_akreditasi_id' => $periode->id,
                'butir_akreditasi_id' => $butir->id,
            ], [
                'pic_user_id' => $pic->id,
                'status' => 'draft',
            ]);

            $pkmData = [
                ['judul_kegiatan' => 'Pelatihan Digital Marketing untuk UMKM', 'ketua' => 'Budi Santoso', 'anggota' => 'Siti Nurhaliza, Rizky Pratama', 'mitra' => 'UMKM Desa Sejahtera', 'tahun' => 2024, 'dana' => 15000000, 'status' => 'Selesai'],
                ['judul_kegiatan' => 'Pendampingan Implementasi Sistem Informasi Desa', 'ketua' => 'Dewi Kusuma', 'anggota' => 'Ahmad Fauzi', 'mitra' => 'Pemerintah Desa Maju', 'tahun' => 2024, 'dana' => 20000000, 'status' => 'Berjalan'],
                ['judul_kegiatan' => 'Sosialisasi Keamanan Siber untuk Guru SMK', 'ketua' => 'Ahmad Fauzi', 'anggota' => 'Hendra Wijaya', 'mitra' => 'SMK Negeri 1 Kota', 'tahun' => 2023, 'dana' => 10000000, 'status' => 'Selesai'],
            ];

            $this->dataService->bulkCreate($pengisian->id, $pkmData);
            $this->command->info('    ✓ Created ' . count($pkmData) . ' PKM records');

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->command->comment('    - Mapping already exists');
            } else {
                $this->command->warn("    ! Error: {$e->getMessage()}");
            }
        }
    }

    /**
     * Setup Butir Mahasiswa
     */
    protected function setupButirMahasiswa($periode, $pic)
    {
        $this->command->info('  🎓 Setting up: Data Mahasiswa');

        $butir = ButirAkreditasi::firstOrCreate([
            'periode_akreditasi_id' => $periode->id,
            'kode' => 'MHS.1',
        ], [
            'nama' => 'Data Mahasiswa Program Studi',
            'deskripsi' => 'Data mahasiswa aktif per tahun akademik',
            'instrumen' => $periode->instrumen,
            'kategori' => 'Mahasiswa',
            'bobot' => 8,
            'urutan' => 4,
        ]);

        try {
            $this->mappingService->setupMappings($butir->id, [
                ['name' => 'tahun_akademik', 'label' => 'Tahun Akademik', 'type' => 'text', 'required' => true, 'width' => '15%'],
                ['name' => 'angkatan', 'label' => 'Angkatan', 'type' => 'number', 'required' => true, 'width' => '10%'],
                ['name' => 'jumlah_pendaftar', 'label' => 'Pendaftar', 'type' => 'number', 'required' => true, 'width' => '12%'],
                ['name' => 'jumlah_lulus_seleksi', 'label' => 'Lulus Seleksi', 'type' => 'number', 'required' => true, 'width' => '12%'],
                ['name' => 'jumlah_mahasiswa_baru', 'label' => 'Mhs Baru', 'type' => 'number', 'required' => true, 'width' => '12%'],
                ['name' => 'jumlah_mahasiswa_aktif', 'label' => 'Mhs Aktif', 'type' => 'number', 'required' => true, 'width' => '12%'],
                ['name' => 'jumlah_lulusan', 'label' => 'Lulusan', 'type' => 'number', 'width' => '12%'],
                ['name' => 'ipk_rata_rata', 'label' => 'Rata-rata IPK', 'type' => 'number', 'width' => '15%'],
            ]);

            $pengisian = PengisianButir::firstOrCreate([
                'periode_akreditasi_id' => $periode->id,
                'butir_akreditasi_id' => $butir->id,
            ], [
                'pic_user_id' => $pic->id,
                'status' => 'draft',
            ]);

            $mahasiswaData = [
                ['tahun_akademik' => '2023/2024', 'angkatan' => 2023, 'jumlah_pendaftar' => 450, 'jumlah_lulus_seleksi' => 120, 'jumlah_mahasiswa_baru' => 100, 'jumlah_mahasiswa_aktif' => 380, 'jumlah_lulusan' => 85, 'ipk_rata_rata' => 3.45],
                ['tahun_akademik' => '2022/2023', 'angkatan' => 2022, 'jumlah_pendaftar' => 420, 'jumlah_lulus_seleksi' => 110, 'jumlah_mahasiswa_baru' => 95, 'jumlah_mahasiswa_aktif' => 365, 'jumlah_lulusan' => 82, 'ipk_rata_rata' => 3.42],
                ['tahun_akademik' => '2021/2022', 'angkatan' => 2021, 'jumlah_pendaftar' => 400, 'jumlah_lulus_seleksi' => 105, 'jumlah_mahasiswa_baru' => 90, 'jumlah_mahasiswa_aktif' => 350, 'jumlah_lulusan' => 78, 'ipk_rata_rata' => 3.38],
            ];

            $this->dataService->bulkCreate($pengisian->id, $mahasiswaData);
            $this->command->info('    ✓ Created ' . count($mahasiswaData) . ' mahasiswa records');

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->command->comment('    - Mapping already exists');
            } else {
                $this->command->warn("    ! Error: {$e->getMessage()}");
            }
        }
    }
}
