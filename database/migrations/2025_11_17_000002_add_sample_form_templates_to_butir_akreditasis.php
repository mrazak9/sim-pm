<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Example 1: PKM (Pengabdian kepada Masyarakat) - Table Form
        $this->addPKMTemplate();

        // Example 2: Publikasi - Table Form
        $this->addPublikasiTemplate();

        // Example 3: Visi Misi - Narrative Form
        $this->addVisiMisiTemplate();
    }

    /**
     * Add PKM template
     */
    protected function addPKMTemplate(): void
    {
        // Find PKM butir (adjust kode based on your data)
        // This is just an example - update the WHERE clause based on actual butir codes
        DB::table('butir_akreditasis')
            ->where('nama', 'LIKE', '%Pengabdian%Masyarakat%')
            ->orWhere('kode', 'LIKE', '%C.2%')
            ->limit(1)
            ->update([
                'metadata' => json_encode([
                    'form_config' => [
                        'type' => 'table',
                        'label' => 'Data Pengabdian kepada Masyarakat',
                        'columns' => [
                            [
                                'name' => 'judul',
                                'label' => 'Judul Kegiatan PKM',
                                'type' => 'text',
                                'required' => true,
                                'width' => '30%',
                                'placeholder' => 'Masukkan judul kegiatan PKM...',
                                'max_length' => 500
                            ],
                            [
                                'name' => 'tahun',
                                'label' => 'Tahun',
                                'type' => 'number',
                                'required' => true,
                                'width' => '10%',
                                'min' => 2020,
                                'max' => 2030
                            ],
                            [
                                'name' => 'nama_dosen',
                                'label' => 'Nama Dosen',
                                'type' => 'text',
                                'required' => true,
                                'width' => '20%'
                            ],
                            [
                                'name' => 'mitra',
                                'label' => 'Mitra PKM',
                                'type' => 'text',
                                'required' => true,
                                'width' => '20%'
                            ],
                            [
                                'name' => 'dana',
                                'label' => 'Dana (Rp)',
                                'type' => 'currency',
                                'required' => false,
                                'width' => '15%'
                            ],
                            [
                                'name' => 'status',
                                'label' => 'Status',
                                'type' => 'select',
                                'required' => true,
                                'width' => '10%',
                                'options' => [
                                    'selesai' => 'Selesai',
                                    'berjalan' => 'Sedang Berjalan',
                                    'rencana' => 'Rencana'
                                ]
                            ]
                        ],
                        'validations' => [
                            'min_rows' => 1,
                            'max_rows' => 100
                        ],
                        'options' => [
                            'allow_add' => true,
                            'allow_delete' => true,
                            'allow_import' => false,
                            'allow_export' => false,
                            'show_summary' => true
                        ]
                    ],
                    'help_text' => 'Isikan seluruh kegiatan Pengabdian kepada Masyarakat yang dilakukan dalam periode akreditasi',
                    'example_data' => [
                        [
                            'judul' => 'Pelatihan Digital Marketing UMKM Desa ABC',
                            'tahun' => 2024,
                            'nama_dosen' => 'Dr. Ahmad Santoso, M.Kom',
                            'mitra' => 'Kelompok UMKM Desa ABC',
                            'dana' => 15000000,
                            'status' => 'selesai'
                        ]
                    ]
                ])
            ]);
    }

    /**
     * Add Publikasi template
     */
    protected function addPublikasiTemplate(): void
    {
        DB::table('butir_akreditasis')
            ->where('nama', 'LIKE', '%Publikasi%')
            ->orWhere('kode', 'LIKE', '%C.3%')
            ->limit(1)
            ->update([
                'metadata' => json_encode([
                    'form_config' => [
                        'type' => 'table',
                        'label' => 'Data Publikasi Ilmiah Dosen',
                        'columns' => [
                            [
                                'name' => 'judul_artikel',
                                'label' => 'Judul Artikel',
                                'type' => 'text',
                                'required' => true,
                                'width' => '30%',
                                'max_length' => 500
                            ],
                            [
                                'name' => 'nama_dosen',
                                'label' => 'Nama Dosen',
                                'type' => 'text',
                                'required' => true,
                                'width' => '18%'
                            ],
                            [
                                'name' => 'nama_jurnal',
                                'label' => 'Nama Jurnal',
                                'type' => 'text',
                                'required' => true,
                                'width' => '22%'
                            ],
                            [
                                'name' => 'tahun',
                                'label' => 'Tahun',
                                'type' => 'number',
                                'required' => true,
                                'width' => '8%',
                                'min' => 2020,
                                'max' => 2030
                            ],
                            [
                                'name' => 'tingkat',
                                'label' => 'Tingkat',
                                'type' => 'select',
                                'required' => true,
                                'width' => '15%',
                                'options' => [
                                    'internasional_bereputasi' => 'Internasional Bereputasi',
                                    'internasional' => 'Internasional',
                                    'nasional_terakreditasi' => 'Nasional Terakreditasi',
                                    'nasional' => 'Nasional'
                                ]
                            ],
                            [
                                'name' => 'url',
                                'label' => 'Link/DOI',
                                'type' => 'text',
                                'required' => false,
                                'width' => '12%',
                                'placeholder' => 'https://...'
                            ]
                        ],
                        'validations' => [
                            'min_rows' => 1,
                            'max_rows' => 200
                        ],
                        'options' => [
                            'allow_add' => true,
                            'allow_delete' => true,
                            'show_summary' => true
                        ]
                    ],
                    'help_text' => 'Isikan data publikasi ilmiah dosen pada jurnal dalam 3 tahun terakhir'
                ])
            ]);
    }

    /**
     * Add Visi Misi template
     */
    protected function addVisiMisiTemplate(): void
    {
        DB::table('butir_akreditasis')
            ->where('nama', 'LIKE', '%Visi%Misi%')
            ->orWhere('kode', 'LIKE', '%A.1%')
            ->limit(1)
            ->update([
                'metadata' => json_encode([
                    'form_config' => [
                        'type' => 'narrative',
                        'label' => 'Visi, Misi, Tujuan, dan Strategi',
                        'fields' => [
                            [
                                'name' => 'visi',
                                'label' => 'Visi Program Studi',
                                'type' => 'richtext',
                                'required' => true,
                                'min_length' => 50,
                                'max_length' => 2000,
                                'help_text' => 'Tuliskan visi program studi yang jelas, realistis, dan terukur'
                            ],
                            [
                                'name' => 'misi',
                                'label' => 'Misi Program Studi',
                                'type' => 'richtext',
                                'required' => true,
                                'min_length' => 100,
                                'max_length' => 3000,
                                'help_text' => 'Tuliskan misi program studi yang mendukung pencapaian visi'
                            ],
                            [
                                'name' => 'tujuan',
                                'label' => 'Tujuan Program Studi',
                                'type' => 'richtext',
                                'required' => true,
                                'min_length' => 100,
                                'max_length' => 3000,
                                'help_text' => 'Tuliskan tujuan program studi yang spesifik dan terukur'
                            ],
                            [
                                'name' => 'strategi',
                                'label' => 'Strategi Pencapaian',
                                'type' => 'richtext',
                                'required' => true,
                                'min_length' => 100,
                                'max_length' => 5000,
                                'help_text' => 'Tuliskan strategi yang akan ditempuh untuk mencapai visi, misi, dan tujuan'
                            ]
                        ],
                        'validations' => [
                            'require_all' => true
                        ]
                    ],
                    'help_text' => 'Isikan visi, misi, tujuan, dan strategi program studi secara lengkap dan jelas'
                ])
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset metadata for affected butir
        DB::table('butir_akreditasis')
            ->where('nama', 'LIKE', '%Pengabdian%Masyarakat%')
            ->orWhere('nama', 'LIKE', '%Publikasi%')
            ->orWhere('nama', 'LIKE', '%Visi%Misi%')
            ->update(['metadata' => null]);
    }
};
