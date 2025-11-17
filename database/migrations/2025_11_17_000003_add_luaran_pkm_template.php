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
        // Add template for Luaran PKM
        DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'LIKE', '%Luaran%PKM%')
                      ->orWhere('nama', 'LIKE', '%PKM%Luaran%')
                      ->orWhere('nama', 'LIKE', '%Pengabdian%')
                      ->orWhere('kode', 'LIKE', '%C.2%');
            })
            ->update([
                'metadata' => DB::raw("JSON_SET(
                    COALESCE(metadata, '{}'),
                    '$.form_config', JSON_OBJECT(
                        'type', 'table',
                        'label', 'Data Luaran Pengabdian kepada Masyarakat',
                        'help_text', 'Isikan seluruh luaran dari kegiatan PKM (publikasi, produk, HKI, dll) dalam periode akreditasi',
                        'columns', JSON_ARRAY(
                            JSON_OBJECT(
                                'name', 'jenis_luaran',
                                'label', 'Jenis Luaran',
                                'type', 'select',
                                'required', true,
                                'width', '15%',
                                'options', JSON_OBJECT(
                                    'publikasi', 'Publikasi',
                                    'produk', 'Produk/Karya',
                                    'hki', 'HKI/Paten',
                                    'model', 'Model/Prototype',
                                    'buku', 'Buku Ajar/Modul'
                                )
                            ),
                            JSON_OBJECT(
                                'name', 'judul',
                                'label', 'Judul/Nama Luaran',
                                'type', 'text',
                                'required', true,
                                'width', '25%',
                                'placeholder', 'Masukkan judul/nama luaran...',
                                'max_length', 500
                            ),
                            JSON_OBJECT(
                                'name', 'tahun',
                                'label', 'Tahun',
                                'type', 'number',
                                'required', true,
                                'width', '8%',
                                'min', 2020,
                                'max', 2030
                            ),
                            JSON_OBJECT(
                                'name', 'nama_dosen',
                                'label', 'Nama Dosen',
                                'type', 'text',
                                'required', true,
                                'width', '18%'
                            ),
                            JSON_OBJECT(
                                'name', 'kegiatan_pkm',
                                'label', 'Kegiatan PKM Terkait',
                                'type', 'text',
                                'required', true,
                                'width', '20%',
                                'placeholder', 'Nama kegiatan PKM yang menghasilkan luaran ini'
                            ),
                            JSON_OBJECT(
                                'name', 'url',
                                'label', 'Link/URL',
                                'type', 'text',
                                'required', false,
                                'width', '14%',
                                'placeholder', 'https://...'
                            )
                        ),
                        'validations', JSON_OBJECT(
                            'min_rows', 1,
                            'max_rows', 150
                        ),
                        'options', JSON_OBJECT(
                            'allow_add', true,
                            'allow_delete', true,
                            'show_summary', true
                        )
                    )
                )")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove form_config from Luaran PKM butir
        DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'LIKE', '%Luaran%PKM%')
                      ->orWhere('nama', 'LIKE', '%PKM%Luaran%');
            })
            ->update([
                'metadata' => DB::raw("JSON_REMOVE(metadata, '$.form_config')")
            ]);
    }
};
