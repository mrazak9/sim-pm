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
        // Template configuration
        $formConfig = [
            'type' => 'table',
            'label' => 'Data Luaran Pengabdian kepada Masyarakat',
            'help_text' => 'Isikan seluruh luaran dari kegiatan PKM (publikasi, produk, HKI, dll) dalam periode akreditasi',
            'columns' => [
                [
                    'name' => 'jenis_luaran',
                    'label' => 'Jenis Luaran',
                    'type' => 'select',
                    'required' => true,
                    'width' => '15%',
                    'options' => [
                        'publikasi' => 'Publikasi',
                        'produk' => 'Produk/Karya',
                        'hki' => 'HKI/Paten',
                        'model' => 'Model/Prototype',
                        'buku' => 'Buku Ajar/Modul'
                    ]
                ],
                [
                    'name' => 'judul',
                    'label' => 'Judul/Nama Luaran',
                    'type' => 'text',
                    'required' => true,
                    'width' => '25%',
                    'placeholder' => 'Masukkan judul/nama luaran...',
                    'max_length' => 500
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
                    'name' => 'nama_dosen',
                    'label' => 'Nama Dosen',
                    'type' => 'text',
                    'required' => true,
                    'width' => '18%'
                ],
                [
                    'name' => 'kegiatan_pkm',
                    'label' => 'Kegiatan PKM Terkait',
                    'type' => 'text',
                    'required' => true,
                    'width' => '20%',
                    'placeholder' => 'Nama kegiatan PKM yang menghasilkan luaran ini'
                ],
                [
                    'name' => 'url',
                    'label' => 'Link/URL',
                    'type' => 'text',
                    'required' => false,
                    'width' => '14%',
                    'placeholder' => 'https://...'
                ]
            ],
            'validations' => [
                'min_rows' => 1,
                'max_rows' => 150
            ],
            'options' => [
                'allow_add' => true,
                'allow_delete' => true,
                'show_summary' => true
            ]
        ];

        // Get butir that match
        $butirs = DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'ILIKE', '%Luaran%PKM%')
                      ->orWhere('nama', 'ILIKE', '%PKM%Luaran%')
                      ->orWhere('nama', 'ILIKE', '%Pengabdian%')
                      ->orWhere('kode', 'ILIKE', '%C.2%');
            })
            ->get();

        // Update each butir
        foreach ($butirs as $butir) {
            $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
            $metadata['form_config'] = $formConfig;

            DB::table('butir_akreditasis')
                ->where('id', $butir->id)
                ->update(['metadata' => json_encode($metadata)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get butir that match
        $butirs = DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'ILIKE', '%Luaran%PKM%')
                      ->orWhere('nama', 'ILIKE', '%PKM%Luaran%');
            })
            ->get();

        // Remove form_config from metadata
        foreach ($butirs as $butir) {
            $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
            unset($metadata['form_config']);

            DB::table('butir_akreditasis')
                ->where('id', $butir->id)
                ->update(['metadata' => json_encode($metadata)]);
        }
    }
};
