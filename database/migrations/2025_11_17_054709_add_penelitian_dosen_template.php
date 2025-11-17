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
        $formConfig = [
            'type' => 'table',
            'label' => 'Data Penelitian Dosen',
            'help_text' => 'Isikan seluruh penelitian yang dilakukan oleh dosen dalam periode akreditasi',
            'columns' => [
                [
                    'name' => 'judul_penelitian',
                    'label' => 'Judul Penelitian',
                    'type' => 'text',
                    'required' => true,
                    'width' => '30%',
                    'placeholder' => 'Masukkan judul penelitian...',
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
                    'label' => 'Nama Ketua Peneliti',
                    'type' => 'text',
                    'required' => true,
                    'width' => '20%'
                ],
                [
                    'name' => 'sumber_dana',
                    'label' => 'Sumber Dana',
                    'type' => 'select',
                    'required' => true,
                    'width' => '15%',
                    'options' => [
                        'internal' => 'Internal PT',
                        'dikti' => 'Kemenristekdikti',
                        'kemendagri' => 'Kementerian Lain',
                        'swasta' => 'Swasta/Industri',
                        'luar_negeri' => 'Luar Negeri'
                    ]
                ],
                [
                    'name' => 'dana',
                    'label' => 'Jumlah Dana (Rp)',
                    'type' => 'currency',
                    'required' => false,
                    'width' => '15%'
                ],
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'required' => true,
                    'width' => '12%',
                    'options' => [
                        'selesai' => 'Selesai',
                        'berjalan' => 'Sedang Berjalan',
                        'rencana' => 'Rencana'
                    ]
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
        ];

        // Apply to butir that contain "Penelitian"
        $butirs = DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'ILIKE', '%Penelitian%Dosen%')
                      ->orWhere('nama', 'ILIKE', '%Dosen%Penelitian%')
                      ->orWhere('kode', 'ILIKE', '%C.1%');
            })
            ->get();

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
        $butirs = DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'ILIKE', '%Penelitian%Dosen%')
                      ->orWhere('nama', 'ILIKE', '%Dosen%Penelitian%');
            })
            ->get();

        foreach ($butirs as $butir) {
            $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
            unset($metadata['form_config']);

            DB::table('butir_akreditasis')
                ->where('id', $butir->id)
                ->update(['metadata' => json_encode($metadata)]);
        }
    }
};
