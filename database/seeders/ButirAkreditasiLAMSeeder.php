<?php

namespace Database\Seeders;

use App\Models\ButirAkreditasi;
use Illuminate\Database\Seeder;

class ButirAkreditasiLAMSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Seeding Template Butir Akreditasi LAM...');

        // Hapus template butir LAM yang sudah ada (untuk menghindari duplicate)
        $this->command->info('   🗑️  Menghapus template LAM yang sudah ada...');
        ButirAkreditasi::whereIn('instrumen', ['LAMEMBA', 'LAMINFOKOM'])
            ->whereNull('periode_akreditasi_id')
            ->delete();

        // Template Butir Akreditasi LAMEMBA (LAM Ekonomi, Manajemen, Bisnis, dan Akuntansi)
        $this->seedLAMEMBA();

        // Template Butir Akreditasi LAMINFOKOM (LAM Infokom)
        $this->seedLAMINFOKOM();

        $this->command->info('✅ Template Butir Akreditasi LAM berhasil di-seed!');
        $this->command->info('   Total: ' . ButirAkreditasi::templatesOnly()->count() . ' template butir');
    }

    /**
     * Seed template butir LAMEMBA
     */
    private function seedLAMEMBA(): void
    {
        $instrumen = 'LAMEMBA';

        $butirList = [
            'Standar 1: Visi, Misi, Tujuan, dan Sasaran' => [
                [
                    'kode' => '1.1',
                    'nama' => 'Visi, Misi, Tujuan, dan Sasaran Program Studi',
                    'deskripsi' => 'Program studi memiliki visi, misi, tujuan, dan sasaran yang jelas, realistis, dan selaras dengan visi dan misi institusi.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 2: Tata Pamong, Kepemimpinan, Sistem Pengelolaan, dan Penjaminan Mutu' => [
                [
                    'kode' => '2.1',
                    'nama' => 'Tata Pamong',
                    'deskripsi' => 'Sistem tata pamong program studi dilaksanakan secara kredibel, transparan, akuntabel, bertanggung jawab, dan adil.',
                    'bobot' => 5.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '2.2',
                    'nama' => 'Kepemimpinan',
                    'deskripsi' => 'Kepemimpinan program studi efektif dalam mendukung pencapaian visi, misi, dan tujuan.',
                    'bobot' => 5.00,
                    'urutan' => 2,
                ],
                [
                    'kode' => '2.3',
                    'nama' => 'Sistem Pengelolaan',
                    'deskripsi' => 'Sistem pengelolaan program studi dilaksanakan secara efektif dan efisien.',
                    'bobot' => 5.00,
                    'urutan' => 3,
                ],
                [
                    'kode' => '2.4',
                    'nama' => 'Penjaminan Mutu',
                    'deskripsi' => 'Program studi menerapkan sistem penjaminan mutu internal yang berkelanjutan.',
                    'bobot' => 5.00,
                    'urutan' => 4,
                ],
            ],

            'Standar 3: Mahasiswa' => [
                [
                    'kode' => '3.1',
                    'nama' => 'Sistem Rekrutmen dan Seleksi',
                    'deskripsi' => 'Program studi memiliki sistem rekrutmen dan seleksi mahasiswa yang kredibel dan akuntabel.',
                    'bobot' => 5.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '3.2',
                    'nama' => 'Layanan Kemahasiswaan',
                    'deskripsi' => 'Program studi menyediakan layanan kemahasiswaan yang memadai.',
                    'bobot' => 5.00,
                    'urutan' => 2,
                ],
            ],

            'Standar 4: Sumber Daya Manusia' => [
                [
                    'kode' => '4.1',
                    'nama' => 'Dosen',
                    'deskripsi' => 'Program studi memiliki dosen yang memadai dari segi kualitas dan kuantitas.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '4.2',
                    'nama' => 'Tenaga Kependidikan',
                    'deskripsi' => 'Program studi memiliki tenaga kependidikan yang memadai.',
                    'bobot' => 5.00,
                    'urutan' => 2,
                ],
            ],

            'Standar 5: Kurikulum, Pembelajaran, dan Suasana Akademik' => [
                [
                    'kode' => '5.1',
                    'nama' => 'Kurikulum',
                    'deskripsi' => 'Kurikulum program studi relevan dengan kebutuhan dan perkembangan ilmu.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '5.2',
                    'nama' => 'Pembelajaran',
                    'deskripsi' => 'Proses pembelajaran di program studi efektif dan berkualitas.',
                    'bobot' => 10.00,
                    'urutan' => 2,
                ],
                [
                    'kode' => '5.3',
                    'nama' => 'Suasana Akademik',
                    'deskripsi' => 'Program studi menciptakan suasana akademik yang kondusif.',
                    'bobot' => 5.00,
                    'urutan' => 3,
                ],
            ],

            'Standar 6: Pembiayaan, Sarana dan Prasarana' => [
                [
                    'kode' => '6.1',
                    'nama' => 'Pembiayaan',
                    'deskripsi' => 'Program studi memiliki pendanaan yang memadai untuk operasional dan pengembangan.',
                    'bobot' => 5.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '6.2',
                    'nama' => 'Sarana dan Prasarana',
                    'deskripsi' => 'Program studi memiliki sarana dan prasarana yang memadai untuk mendukung pembelajaran.',
                    'bobot' => 5.00,
                    'urutan' => 2,
                ],
            ],

            'Standar 7: Penelitian' => [
                [
                    'kode' => '7.1',
                    'nama' => 'Penelitian Dosen dan Mahasiswa',
                    'deskripsi' => 'Program studi melaksanakan penelitian yang relevan dan berkualitas.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 8: Pengabdian kepada Masyarakat' => [
                [
                    'kode' => '8.1',
                    'nama' => 'Pengabdian kepada Masyarakat',
                    'deskripsi' => 'Program studi melaksanakan pengabdian kepada masyarakat yang bermanfaat.',
                    'bobot' => 5.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 9: Luaran dan Capaian' => [
                [
                    'kode' => '9.1',
                    'nama' => 'Luaran Pendidikan',
                    'deskripsi' => 'Lulusan program studi memiliki kompetensi yang sesuai dengan capaian pembelajaran.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '9.2',
                    'nama' => 'Capaian Penelitian dan PkM',
                    'deskripsi' => 'Program studi menghasilkan luaran penelitian dan PkM yang berkualitas.',
                    'bobot' => 5.00,
                    'urutan' => 2,
                ],
            ],
        ];

        foreach ($butirList as $kategori => $butirs) {
            foreach ($butirs as $butir) {
                ButirAkreditasi::create([
                    'kode' => $butir['kode'],
                    'nama' => $butir['nama'],
                    'deskripsi' => $butir['deskripsi'],
                    'instrumen' => $instrumen,
                    'periode_akreditasi_id' => null, // Template
                    'kategori' => $kategori,
                    'bobot' => $butir['bobot'],
                    'urutan' => $butir['urutan'],
                    'is_mandatory' => true,
                ]);
            }
        }

        $this->command->info("   ✓ LAMEMBA: " . count($butirList, COUNT_RECURSIVE) . " butir");
    }

    /**
     * Seed template butir LAMINFOKOM
     */
    private function seedLAMINFOKOM(): void
    {
        $instrumen = 'LAMINFOKOM';

        $butirList = [
            'Standar 1: Visi, Misi, Tujuan, dan Strategi' => [
                [
                    'kode' => '1.1',
                    'nama' => 'Visi, Misi, Tujuan, dan Strategi Program Studi',
                    'deskripsi' => 'Program studi memiliki visi, misi, tujuan, dan strategi yang jelas dan terukur.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 2: Tata Kelola, Kepemimpinan, dan Sistem Pengelolaan' => [
                [
                    'kode' => '2.1',
                    'nama' => 'Tata Kelola',
                    'deskripsi' => 'Program studi memiliki sistem tata kelola yang efektif.',
                    'bobot' => 5.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '2.2',
                    'nama' => 'Kepemimpinan',
                    'deskripsi' => 'Kepemimpinan program studi visioner dan transformatif.',
                    'bobot' => 5.00,
                    'urutan' => 2,
                ],
                [
                    'kode' => '2.3',
                    'nama' => 'Sistem Pengelolaan',
                    'deskripsi' => 'Sistem pengelolaan program studi tertata dengan baik.',
                    'bobot' => 5.00,
                    'urutan' => 3,
                ],
            ],

            'Standar 3: Mahasiswa' => [
                [
                    'kode' => '3.1',
                    'nama' => 'Mahasiswa',
                    'deskripsi' => 'Program studi memiliki sistem penerimaan dan pengembangan mahasiswa yang baik.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 4: Sumber Daya Manusia' => [
                [
                    'kode' => '4.1',
                    'nama' => 'Dosen',
                    'deskripsi' => 'Program studi memiliki dosen berkualitas di bidang informatika dan komputer.',
                    'bobot' => 15.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 5: Kurikulum dan Pembelajaran' => [
                [
                    'kode' => '5.1',
                    'nama' => 'Kurikulum',
                    'deskripsi' => 'Kurikulum program studi sesuai dengan perkembangan teknologi informasi.',
                    'bobot' => 15.00,
                    'urutan' => 1,
                ],
                [
                    'kode' => '5.2',
                    'nama' => 'Pembelajaran',
                    'deskripsi' => 'Proses pembelajaran mendukung pencapaian kompetensi lulusan.',
                    'bobot' => 10.00,
                    'urutan' => 2,
                ],
            ],

            'Standar 6: Sarana dan Prasarana' => [
                [
                    'kode' => '6.1',
                    'nama' => 'Sarana dan Prasarana',
                    'deskripsi' => 'Program studi memiliki laboratorium dan infrastruktur TI yang memadai.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 7: Penelitian' => [
                [
                    'kode' => '7.1',
                    'nama' => 'Penelitian',
                    'deskripsi' => 'Program studi melaksanakan penelitian di bidang informatika dan komputer.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 8: Pengabdian kepada Masyarakat' => [
                [
                    'kode' => '8.1',
                    'nama' => 'Pengabdian kepada Masyarakat',
                    'deskripsi' => 'Program studi melaksanakan pengabdian masyarakat berbasis teknologi.',
                    'bobot' => 5.00,
                    'urutan' => 1,
                ],
            ],

            'Standar 9: Luaran dan Capaian' => [
                [
                    'kode' => '9.1',
                    'nama' => 'Luaran dan Capaian',
                    'deskripsi' => 'Lulusan program studi kompeten di bidang informatika dan komputer.',
                    'bobot' => 10.00,
                    'urutan' => 1,
                ],
            ],
        ];

        foreach ($butirList as $kategori => $butirs) {
            foreach ($butirs as $butir) {
                ButirAkreditasi::create([
                    'kode' => $butir['kode'],
                    'nama' => $butir['nama'],
                    'deskripsi' => $butir['deskripsi'],
                    'instrumen' => $instrumen,
                    'periode_akreditasi_id' => null, // Template
                    'kategori' => $kategori,
                    'bobot' => $butir['bobot'],
                    'urutan' => $butir['urutan'],
                    'is_mandatory' => true,
                ]);
            }
        }

        $this->command->info("   ✓ LAMINFOKOM: " . count($butirList, COUNT_RECURSIVE) . " butir");
    }
}
