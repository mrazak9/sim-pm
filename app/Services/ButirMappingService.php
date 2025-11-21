<?php

namespace App\Services;

use App\Models\ButirAkreditasi;
use App\Models\ButirColumnMapping;
use Illuminate\Support\Facades\DB;

class ButirMappingService
{
    /**
     * Setup column mappings for a butir from form config
     *
     * @param int $butirId
     * @return array Created mappings
     */
    public function setupFromFormConfig(int $butirId): array
    {
        $butir = ButirAkreditasi::findOrFail($butirId);
        $formConfig = $butir->metadata['form_config'] ?? null;

        if (!$formConfig) {
            throw new \Exception("Butir tidak memiliki form_config");
        }

        // Get fields from config
        $fields = $formConfig['columns'] ?? $formConfig['fields'] ?? [];

        return $this->setupMappings($butirId, $fields);
    }

    /**
     * Setup column mappings manually
     *
     * @param int $butirId
     * @param array $fields
     * @return array Created mappings
     */
    public function setupMappings(int $butirId, array $fields): array
    {
        DB::beginTransaction();

        try {
            $mappings = [];
            $columnIndex = 1;

            foreach ($fields as $field) {
                if ($columnIndex > 30) {
                    throw new \Exception("Maksimal 30 field per butir");
                }

                $mapping = ButirColumnMapping::create([
                    'butir_akreditasi_id' => $butirId,
                    'field_name' => $field['name'],
                    'field_label' => $field['label'] ?? ucfirst($field['name']),
                    'column_name' => "c{$columnIndex}",
                    'field_type' => $field['type'] ?? 'text',
                    'field_config' => $this->buildFieldConfig($field),
                    'display_order' => $columnIndex,
                    'width' => $field['width'] ?? null,
                    'is_required' => $field['required'] ?? false,
                    'help_text' => $field['help_text'] ?? null,
                    'placeholder' => $field['placeholder'] ?? null,
                ]);

                $mappings[] = $mapping;
                $columnIndex++;
            }

            DB::commit();

            return $mappings;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing mappings
     *
     * @param int $butirId
     * @param array $fields
     * @return array
     */
    public function updateMappings(int $butirId, array $fields): array
    {
        DB::beginTransaction();

        try {
            // Delete existing mappings
            ButirColumnMapping::where('butir_akreditasi_id', $butirId)->delete();

            // Create new mappings
            $mappings = $this->setupMappings($butirId, $fields);

            DB::commit();

            return $mappings;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Build field config from field definition
     *
     * @param array $field
     * @return array
     */
    protected function buildFieldConfig(array $field): array
    {
        $config = [];

        // Add validation rules
        if (isset($field['min'])) $config['validation']['min'] = $field['min'];
        if (isset($field['max'])) $config['validation']['max'] = $field['max'];
        if (isset($field['min_length'])) $config['validation']['min_length'] = $field['min_length'];
        if (isset($field['max_length'])) $config['validation']['max_length'] = $field['max_length'];
        if (isset($field['regex'])) $config['validation']['regex'] = $field['regex'];

        // Add options for select/radio
        if (isset($field['options'])) {
            $config['options'] = $field['options'];
        }

        // Add display config
        if (isset($field['prefix'])) $config['display']['prefix'] = $field['prefix'];
        if (isset($field['suffix'])) $config['display']['suffix'] = $field['suffix'];
        if (isset($field['format'])) $config['display']['format'] = $field['format'];

        return $config;
    }

    /**
     * Get mappings as associative array (field_name => column_name)
     *
     * @param int $butirId
     * @return array
     */
    public function getMappingsDictionary(int $butirId): array
    {
        return ButirColumnMapping::where('butir_akreditasi_id', $butirId)
            ->pluck('column_name', 'field_name')
            ->toArray();
    }

    /**
     * Get reverse mappings (column_name => field_name)
     *
     * @param int $butirId
     * @return array
     */
    public function getReverseMappingsDictionary(int $butirId): array
    {
        return ButirColumnMapping::where('butir_akreditasi_id', $butirId)
            ->pluck('field_name', 'column_name')
            ->toArray();
    }
}
