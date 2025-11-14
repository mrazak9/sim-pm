<?php

namespace Database\Seeders;

use App\Models\ButirAkreditasi;
use Illuminate\Database\Seeder;

class ButirAkreditasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        ButirAkreditasi::query()->delete();

        // Template Butir Akreditasi BAN-PT 4.0
        $kriteria = $this->getButirAkreditasiBanPT40();

        foreach ($kriteria as $kategori => $butirList) {
            foreach ($butirList as $butirData) {
                $parent = null;

                // Create parent butir if exists
                if (isset($butirData['parent'])) {
                    $parent = ButirAkreditasi::create([
                        'kode' => $butirData['parent']['kode'],
                        'nama' => $butirData['parent']['nama'],
                        'deskripsi' => $butirData['parent']['deskripsi'] ?? null,
                        'instrumen' => '4.0',
                        'kategori' => $kategori,
                        'bobot' => $butirData['parent']['bobot'] ?? 0,
                        'urutan' => $butirData['parent']['urutan'],
                        'is_mandatory' => true,
                        'metadata' => $butirData['parent']['metadata'] ?? null,
                    ]);
                }

                // Create child butir
                if (isset($butirData['children'])) {
                    foreach ($butirData['children'] as $child) {
                        ButirAkreditasi::create([
                            'kode' => $child['kode'],
                            'nama' => $child['nama'],
                            'deskripsi' => $child['deskripsi'] ?? null,
                            'instrumen' => '4.0',
                            'kategori' => $kategori,
                            'bobot' => $child['bobot'] ?? 0,
                            'parent_id' => $parent?->id,
                            'urutan' => $child['urutan'],
                            'is_mandatory' => $child['is_mandatory'] ?? true,
                            'metadata' => $child['metadata'] ?? null,
                        ]);
                    }
                } else {
                    // Single butir without children
                    ButirAkreditasi::create([
                        'kode' => $butirData['kode'],
                        'nama' => $butirData['nama'],
                        'deskripsi' => $butirData['deskripsi'] ?? null,
                        'instrumen' => '4.0',
                        'kategori' => $kategori,
                        'bobot' => $butirData['bobot'] ?? 0,
                        'urutan' => $butirData['urutan'],
                        'is_mandatory' => $butirData['is_mandatory'] ?? true,
                        'metadata' => $butirData['metadata'] ?? null,
                    ]);
                }
            }
        }

        $this->command->info('✅ Butir Akreditasi BAN-PT 4.0 berhasil di-seed!');
        $this->command->info('   Total: ' . ButirAkreditasi::count() . ' butir akreditasi');
    }

    /**
     * Get template butir akreditasi BAN-PT 4.0
     */
    private function getButirAkreditasiBanPT40(): array
    {
        return [
            'Kriteria 1' => [
                [
                    'parent' => [
                        'kode' => '1.1',
                        'nama' => 'Visi, Misi, Tujuan, dan Strategi (VMTS)',
                        'deskripsi' => 'Visi, misi, tujuan, dan strategi program studi harus jelas dan realistis.',
                        'bobot' => 5.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '1.1.a',
                            'nama' => 'Mekanisme penyusunan VMTS',
                            'deskripsi' => 'Jelaskan mekanisme penyusunan visi, misi, tujuan, dan strategi program studi, serta pihak-pihak yang dilibatkan.',
                            'bobot' => 2.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '1.1.b',
                            'nama' => 'Konsistensi dan keterkaitan VMTS',
                            'deskripsi' => 'Analisis konsistensi dan keterkaitan VMTS program studi dengan VMTS institusi.',
                            'bobot' => 3.00,
                            'urutan' => 2,
                        ],
                    ],
                ],
                [
                    'parent' => [
                        'kode' => '1.2',
                        'nama' => 'Strategi Pencapaian',
                        'deskripsi' => 'Strategi pencapaian tujuan program studi harus terukur dan dapat dilaksanakan.',
                        'bobot' => 5.00,
                        'urutan' => 2,
                    ],
                    'children' => [
                        [
                            'kode' => '1.2.a',
                            'nama' => 'Strategi pencapaian tujuan',
                            'deskripsi' => 'Jelaskan strategi yang digunakan untuk mencapai tujuan program studi.',
                            'bobot' => 5.00,
                            'urutan' => 1,
                        ],
                    ],
                ],
            ],

            'Kriteria 2' => [
                [
                    'parent' => [
                        'kode' => '2.1',
                        'nama' => 'Tata Pamong, Tata Kelola, dan Kerjasama',
                        'deskripsi' => 'Tata pamong dan tata kelola program studi harus kredibel, transparan, akuntabel, dan adil.',
                        'bobot' => 10.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '2.1.a',
                            'nama' => 'Struktur organisasi dan tata kerja',
                            'deskripsi' => 'Jelaskan struktur organisasi, tugas, fungsi, dan wewenang pengelola program studi.',
                            'bobot' => 3.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '2.1.b',
                            'nama' => 'Sistem penjaminan mutu internal',
                            'deskripsi' => 'Jelaskan implementasi sistem penjaminan mutu internal (SPMI) di program studi.',
                            'bobot' => 4.00,
                            'urutan' => 2,
                        ],
                        [
                            'kode' => '2.1.c',
                            'nama' => 'Kerjasama institusi',
                            'deskripsi' => 'Jelaskan kerjasama yang dilakukan program studi dengan institusi lain (dalam dan luar negeri).',
                            'bobot' => 3.00,
                            'urutan' => 3,
                        ],
                    ],
                ],
            ],

            'Kriteria 3' => [
                [
                    'parent' => [
                        'kode' => '3.1',
                        'nama' => 'Mahasiswa',
                        'deskripsi' => 'Sistem rekrutmen, seleksi, dan manajemen data mahasiswa harus kredibel dan akuntabel.',
                        'bobot' => 10.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '3.1.a',
                            'nama' => 'Sistem rekrutmen dan seleksi',
                            'deskripsi' => 'Jelaskan sistem rekrutmen dan seleksi mahasiswa baru program studi.',
                            'bobot' => 3.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '3.1.b',
                            'nama' => 'Rasio mahasiswa terhadap dosen',
                            'deskripsi' => 'Berikan data rasio mahasiswa terhadap dosen tetap program studi dalam 3 tahun terakhir.',
                            'bobot' => 3.00,
                            'urutan' => 2,
                        ],
                        [
                            'kode' => '3.1.c',
                            'nama' => 'Layanan kemahasiswaan',
                            'deskripsi' => 'Jelaskan layanan kemahasiswaan yang disediakan (bimbingan akademik, konseling, minat/bakat, kesehatan).',
                            'bobot' => 4.00,
                            'urutan' => 3,
                        ],
                    ],
                ],
            ],

            'Kriteria 4' => [
                [
                    'parent' => [
                        'kode' => '4.1',
                        'nama' => 'Sumber Daya Manusia',
                        'deskripsi' => 'Program studi harus memiliki SDM yang memadai (kualitas dan kuantitas) untuk mendukung pelaksanaan tridharma.',
                        'bobot' => 15.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '4.1.a',
                            'nama' => 'Dosen tetap perguruan tinggi',
                            'deskripsi' => 'Berikan data dosen tetap perguruan tinggi yang ditugaskan sebagai pengampu mata kuliah di program studi.',
                            'bobot' => 5.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '4.1.b',
                            'nama' => 'Kualifikasi akademik dosen',
                            'deskripsi' => 'Berikan data kualifikasi akademik (pendidikan S2/S3 yang sesuai) dosen tetap program studi.',
                            'bobot' => 5.00,
                            'urutan' => 2,
                        ],
                        [
                            'kode' => '4.1.c',
                            'nama' => 'Jabatan akademik dosen',
                            'deskripsi' => 'Berikan data jabatan akademik dosen tetap program studi (Asisten Ahli, Lektor, Lektor Kepala, Guru Besar).',
                            'bobot' => 5.00,
                            'urutan' => 3,
                        ],
                    ],
                ],
            ],

            'Kriteria 5' => [
                [
                    'parent' => [
                        'kode' => '5.1',
                        'nama' => 'Keuangan, Sarana dan Prasarana',
                        'deskripsi' => 'Program studi harus memiliki pendanaan yang memadai dan sarana prasarana yang mendukung pembelajaran.',
                        'bobot' => 10.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '5.1.a',
                            'nama' => 'Dana operasional',
                            'deskripsi' => 'Berikan data dana operasional untuk menjalankan program studi dalam 3 tahun terakhir.',
                            'bobot' => 3.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '5.1.b',
                            'nama' => 'Prasarana pembelajaran',
                            'deskripsi' => 'Jelaskan prasarana yang digunakan untuk mendukung pembelajaran (ruang kuliah, lab, perpustakaan).',
                            'bobot' => 4.00,
                            'urutan' => 2,
                        ],
                        [
                            'kode' => '5.1.c',
                            'nama' => 'Aksesibilitas',
                            'deskripsi' => 'Jelaskan aksesibilitas prasarana untuk mahasiswa berkebutuhan khusus.',
                            'bobot' => 3.00,
                            'urutan' => 3,
                        ],
                    ],
                ],
            ],

            'Kriteria 6' => [
                [
                    'parent' => [
                        'kode' => '6.1',
                        'nama' => 'Pendidikan',
                        'deskripsi' => 'Kurikulum dan proses pembelajaran harus relevan dengan kebutuhan dan perkembangan ilmu.',
                        'bobot' => 20.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '6.1.a',
                            'nama' => 'Kurikulum dan capaian pembelajaran',
                            'deskripsi' => 'Jelaskan mekanisme penyusunan kurikulum dan capaian pembelajaran lulusan (CPL).',
                            'bobot' => 7.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '6.1.b',
                            'nama' => 'Kesesuaian capaian pembelajaran dengan visi misi',
                            'deskripsi' => 'Analisis kesesuaian CPL dengan visi, misi, dan tujuan program studi.',
                            'bobot' => 6.00,
                            'urutan' => 2,
                        ],
                        [
                            'kode' => '6.1.c',
                            'nama' => 'Sistem pembelajaran',
                            'deskripsi' => 'Jelaskan sistem pembelajaran yang diterapkan (student-centered learning, problem-based learning, dll).',
                            'bobot' => 7.00,
                            'urutan' => 3,
                        ],
                    ],
                ],
            ],

            'Kriteria 7' => [
                [
                    'parent' => [
                        'kode' => '7.1',
                        'nama' => 'Penelitian',
                        'deskripsi' => 'Penelitian dosen dan mahasiswa harus relevan dengan pengembangan keilmuan dan kebutuhan masyarakat.',
                        'bobot' => 15.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '7.1.a',
                            'nama' => 'Penelitian dosen',
                            'deskripsi' => 'Berikan data penelitian yang dilakukan oleh dosen tetap program studi dalam 3 tahun terakhir.',
                            'bobot' => 5.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '7.1.b',
                            'nama' => 'Penelitian mahasiswa',
                            'deskripsi' => 'Berikan data keterlibatan mahasiswa dalam penelitian dosen atau penelitian mandiri.',
                            'bobot' => 5.00,
                            'urutan' => 2,
                        ],
                        [
                            'kode' => '7.1.c',
                            'nama' => 'Mutu penelitian',
                            'deskripsi' => 'Jelaskan upaya peningkatan mutu penelitian (publikasi jurnal, HKI, dll).',
                            'bobot' => 5.00,
                            'urutan' => 3,
                        ],
                    ],
                ],
            ],

            'Kriteria 8' => [
                [
                    'parent' => [
                        'kode' => '8.1',
                        'nama' => 'Pengabdian kepada Masyarakat',
                        'deskripsi' => 'PkM harus berkontribusi terhadap pengembangan dan pemberdayaan masyarakat.',
                        'bobot' => 10.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '8.1.a',
                            'nama' => 'Kegiatan PkM dosen',
                            'deskripsi' => 'Berikan data kegiatan pengabdian kepada masyarakat yang dilakukan dosen dalam 3 tahun terakhir.',
                            'bobot' => 5.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '8.1.b',
                            'nama' => 'Keterlibatan mahasiswa dalam PkM',
                            'deskripsi' => 'Berikan data keterlibatan mahasiswa dalam kegiatan PkM.',
                            'bobot' => 5.00,
                            'urutan' => 2,
                        ],
                    ],
                ],
            ],

            'Kriteria 9' => [
                [
                    'parent' => [
                        'kode' => '9.1',
                        'nama' => 'Luaran dan Capaian Tridharma',
                        'deskripsi' => 'Luaran pendidikan, penelitian, dan PkM harus terukur dan berdampak.',
                        'bobot' => 15.00,
                        'urutan' => 1,
                    ],
                    'children' => [
                        [
                            'kode' => '9.1.a',
                            'nama' => 'IPK lulusan',
                            'deskripsi' => 'Berikan data Indeks Prestasi Kumulatif (IPK) lulusan dalam 3 tahun terakhir.',
                            'bobot' => 3.00,
                            'urutan' => 1,
                        ],
                        [
                            'kode' => '9.1.b',
                            'nama' => 'Masa studi lulusan',
                            'deskripsi' => 'Berikan data rata-rata masa studi lulusan dalam 3 tahun terakhir.',
                            'bobot' => 3.00,
                            'urutan' => 2,
                        ],
                        [
                            'kode' => '9.1.c',
                            'nama' => 'Prestasi akademik dan non-akademik mahasiswa',
                            'deskripsi' => 'Berikan data prestasi akademik dan non-akademik mahasiswa di tingkat regional, nasional, atau internasional.',
                            'bobot' => 3.00,
                            'urutan' => 3,
                        ],
                        [
                            'kode' => '9.1.d',
                            'nama' => 'Publikasi ilmiah',
                            'deskripsi' => 'Berikan data publikasi ilmiah dosen dan mahasiswa di jurnal nasional/internasional bereputasi.',
                            'bobot' => 3.00,
                            'urutan' => 4,
                        ],
                        [
                            'kode' => '9.1.e',
                            'nama' => 'Luaran PkM',
                            'deskripsi' => 'Berikan data luaran pengabdian kepada masyarakat yang mendapat pengakuan/penghargaan.',
                            'bobot' => 3.00,
                            'urutan' => 5,
                        ],
                    ],
                ],
            ],
        ];
    }
}
