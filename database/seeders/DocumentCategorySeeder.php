<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kebijakan',
                'code' => 'KEBIJAKAN',
                'description' => 'Dokumen kebijakan institusi',
                'icon' => 'shield-check',
                'color' => '#EF4444',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Pedoman',
                'code' => 'PEDOMAN',
                'description' => 'Pedoman dan panduan institusi',
                'icon' => 'book-open',
                'color' => '#F59E0B',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Manual',
                'code' => 'MANUAL',
                'description' => 'Manual prosedur dan operasional',
                'icon' => 'document-text',
                'color' => '#10B981',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'SOP',
                'code' => 'SOP',
                'description' => 'Standard Operating Procedure',
                'icon' => 'clipboard-list',
                'color' => '#3B82F6',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Formulir',
                'code' => 'FORMULIR',
                'description' => 'Form dan template dokumen',
                'icon' => 'document-duplicate',
                'color' => '#8B5CF6',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Instruksi Kerja',
                'code' => 'INSTRUKSI_KERJA',
                'description' => 'Instruksi kerja detail',
                'icon' => 'clipboard-check',
                'color' => '#EC4899',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Laporan',
                'code' => 'LAPORAN',
                'description' => 'Laporan dan report',
                'icon' => 'chart-bar',
                'color' => '#06B6D4',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Sertifikat',
                'code' => 'SERTIFIKAT',
                'description' => 'Sertifikat dan penghargaan',
                'icon' => 'badge-check',
                'color' => '#14B8A6',
                'order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Surat Keputusan',
                'code' => 'SK',
                'description' => 'Surat keputusan resmi',
                'icon' => 'document-check',
                'color' => '#F97316',
                'order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'code' => 'LAINNYA',
                'description' => 'Dokumen kategori lainnya',
                'icon' => 'folder',
                'color' => '#6B7280',
                'order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DocumentCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        $this->command->info('Document categories seeded successfully!');
    }
}
