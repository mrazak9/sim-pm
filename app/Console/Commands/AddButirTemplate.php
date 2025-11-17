<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddButirTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'butir:add-template
                            {butir_id? : ID of the butir (optional)}
                            {--list : List all butir without templates}
                            {--template= : Template type (table|narrative|checklist|metric|mixed)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add dynamic form template to butir akreditasi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // List mode
        if ($this->option('list')) {
            $this->listButirWithoutTemplates();
            return 0;
        }

        // Get butir ID
        $butirId = $this->argument('butir_id');

        if (!$butirId) {
            $this->info('Available commands:');
            $this->line('  php artisan butir:add-template --list          # List all butir without templates');
            $this->line('  php artisan butir:add-template {id}            # Add template to specific butir');
            $this->line('  php artisan butir:add-template {id} --template=table  # Use predefined template');
            return 0;
        }

        // Get butir
        $butir = DB::table('butir_akreditasis')->where('id', $butirId)->first();

        if (!$butir) {
            $this->error("Butir with ID {$butirId} not found!");
            return 1;
        }

        $this->info("Butir: [{$butir->kode}] {$butir->nama}");

        // Check if already has template
        $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
        if (isset($metadata['form_config'])) {
            if (!$this->confirm('This butir already has a template. Overwrite?', false)) {
                $this->info('Cancelled.');
                return 0;
            }
        }

        // Get template type
        $templateType = $this->option('template');

        if (!$templateType) {
            $templateType = $this->choice(
                'Select template type',
                ['table', 'narrative', 'checklist', 'metric', 'mixed'],
                0
            );
        }

        // Get predefined template or create custom
        if ($this->confirm('Use predefined template?', true)) {
            $template = $this->getPredefinedTemplate($templateType, $butir);
        } else {
            $this->warn('Custom template builder not yet implemented. Using predefined template.');
            $template = $this->getPredefinedTemplate($templateType, $butir);
        }

        // Apply template
        $metadata['form_config'] = $template;

        DB::table('butir_akreditasis')
            ->where('id', $butirId)
            ->update(['metadata' => json_encode($metadata)]);

        $this->info("✓ Template successfully added to butir {$butir->kode}");
        $this->line("  Type: {$template['type']}");
        $this->line("  Label: {$template['label']}");

        return 0;
    }

    /**
     * List all butir without templates
     */
    protected function listButirWithoutTemplates()
    {
        $butirs = DB::table('butir_akreditasis')
            ->select('id', 'kode', 'nama', 'metadata')
            ->orderBy('kode')
            ->get();

        $withoutTemplate = $butirs->filter(function ($butir) {
            $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
            return !isset($metadata['form_config']);
        });

        if ($withoutTemplate->isEmpty()) {
            $this->info('All butir already have templates!');
            return;
        }

        $this->info("Butir WITHOUT templates ({$withoutTemplate->count()}):");
        $this->table(
            ['ID', 'Kode', 'Nama'],
            $withoutTemplate->map(fn($b) => [$b->id, $b->kode, $b->nama])
        );

        $totalButirs = $butirs->count();
        $withTemplate = $totalButirs - $withoutTemplate->count();
        $percentage = round(($withTemplate / $totalButirs) * 100, 2);

        $this->line('');
        $this->info("Progress: {$withTemplate}/{$totalButirs} butir have templates ({$percentage}%)");
    }

    /**
     * Get predefined template based on type
     */
    protected function getPredefinedTemplate(string $type, $butir): array
    {
        $templates = [
            'table' => $this->getTableTemplate($butir),
            'narrative' => $this->getNarrativeTemplate($butir),
            'checklist' => $this->getChecklistTemplate($butir),
            'metric' => $this->getMetricTemplate($butir),
            'mixed' => $this->getMixedTemplate($butir),
        ];

        return $templates[$type] ?? $templates['table'];
    }

    /**
     * Generic table template
     */
    protected function getTableTemplate($butir): array
    {
        $label = "Data {$butir->nama}";

        return [
            'type' => 'table',
            'label' => $label,
            'help_text' => "Isikan data {$butir->nama} sesuai dengan panduan BAN-PT",
            'columns' => [
                [
                    'name' => 'item',
                    'label' => 'Item/Nama',
                    'type' => 'text',
                    'required' => true,
                    'width' => '40%',
                    'placeholder' => 'Masukkan nama item...',
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
                    'name' => 'keterangan',
                    'label' => 'Keterangan',
                    'type' => 'text',
                    'required' => false,
                    'width' => '50%',
                ],
            ],
            'validations' => [
                'min_rows' => 1,
                'max_rows' => 100
            ],
            'options' => [
                'allow_add' => true,
                'allow_delete' => true,
                'show_summary' => false
            ]
        ];
    }

    /**
     * Generic narrative template
     */
    protected function getNarrativeTemplate($butir): array
    {
        return [
            'type' => 'narrative',
            'label' => $butir->nama,
            'help_text' => "Tuliskan {$butir->nama} secara lengkap dan jelas",
            'fields' => [
                [
                    'name' => 'uraian',
                    'label' => 'Uraian',
                    'type' => 'richtext',
                    'required' => true,
                    'min_length' => 100,
                    'max_length' => 5000,
                    'help_text' => "Tuliskan uraian lengkap mengenai {$butir->nama}"
                ]
            ],
            'validations' => [
                'require_all' => true
            ]
        ];
    }

    /**
     * Generic checklist template
     */
    protected function getChecklistTemplate($butir): array
    {
        return [
            'type' => 'checklist',
            'label' => "Checklist {$butir->nama}",
            'help_text' => "Centang item yang sudah terpenuhi",
            'items' => [
                [
                    'id' => 1,
                    'label' => 'Item 1',
                    'description' => 'Deskripsi item 1',
                    'required' => true
                ],
                [
                    'id' => 2,
                    'label' => 'Item 2',
                    'description' => 'Deskripsi item 2',
                    'required' => false
                ]
            ]
        ];
    }

    /**
     * Generic metric template
     */
    protected function getMetricTemplate($butir): array
    {
        return [
            'type' => 'metric',
            'label' => "Metrik {$butir->nama}",
            'help_text' => "Isikan nilai metrik/indikator",
            'metrics' => [
                [
                    'name' => 'nilai',
                    'label' => 'Nilai',
                    'type' => 'number',
                    'required' => true,
                    'help_text' => 'Nilai metrik'
                ],
                [
                    'name' => 'target',
                    'label' => 'Target',
                    'type' => 'number',
                    'required' => true,
                    'help_text' => 'Target yang ditetapkan'
                ]
            ]
        ];
    }

    /**
     * Generic mixed template
     */
    protected function getMixedTemplate($butir): array
    {
        return [
            'type' => 'mixed',
            'label' => $butir->nama,
            'sections' => [
                [
                    'title' => 'Data Tabel',
                    'type' => 'table',
                    'columns' => [
                        [
                            'name' => 'item',
                            'label' => 'Item',
                            'type' => 'text',
                            'required' => true
                        ]
                    ]
                ],
                [
                    'title' => 'Uraian',
                    'type' => 'narrative',
                    'fields' => [
                        [
                            'name' => 'deskripsi',
                            'label' => 'Deskripsi',
                            'type' => 'richtext',
                            'required' => true
                        ]
                    ]
                ]
            ]
        ];
    }
}
