<?php

namespace App\Services;

use App\Models\ButirAkreditasi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DynamicFormValidatorService
{
    /**
     * Validate form data against butir template configuration
     *
     * @param array $formData The form data to validate
     * @param ButirAkreditasi $butir The butir with form configuration
     * @return array Validation rules
     * @throws ValidationException
     */
    public function validate(array $formData, ButirAkreditasi $butir): array
    {
        if (!isset($butir->metadata['form_config'])) {
            return []; // No dynamic validation needed
        }

        $config = $butir->metadata['form_config'];
        $type = $config['type'] ?? null;

        if (!$type) {
            return [];
        }

        return match($type) {
            'table' => $this->validateTableForm($formData, $config),
            'narrative' => $this->validateNarrativeForm($formData, $config),
            'checklist' => $this->validateChecklistForm($formData, $config),
            'metric' => $this->validateMetricForm($formData, $config),
            'mixed' => $this->validateMixedForm($formData, $config),
            default => []
        };
    }

    /**
     * Validate table form data
     */
    protected function validateTableForm(array $data, array $config): array
    {
        $rules = [];
        $messages = [];

        // Validate min/max rows
        if (isset($config['validations']['min_rows'])) {
            $minRows = $config['validations']['min_rows'];
            $rules['rows'] = "required|array|min:{$minRows}";
            $messages['rows.min'] = "Minimal {$minRows} baris data harus diisi";
        } else {
            $rules['rows'] = 'required|array';
        }

        if (isset($config['validations']['max_rows'])) {
            $maxRows = $config['validations']['max_rows'];
            $rules['rows'] .= "|max:{$maxRows}";
            $messages['rows.max'] = "Maksimal {$maxRows} baris data";
        }

        // Validate each column in each row
        foreach ($config['columns'] as $column) {
            $fieldRules = $this->getFieldRules($column);

            if (!empty($fieldRules)) {
                $rules['rows.*.' . $column['name']] = implode('|', $fieldRules);

                // Custom messages
                if ($column['required'] ?? false) {
                    $messages['rows.*.' . $column['name'] . '.required'] = "Kolom {$column['label']} wajib diisi";
                }
            }
        }

        return ['rules' => $rules, 'messages' => $messages];
    }

    /**
     * Validate narrative form data
     */
    protected function validateNarrativeForm(array $data, array $config): array
    {
        $rules = [];
        $messages = [];

        foreach ($config['fields'] as $field) {
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
                $messages[$field['name'] . '.required'] = "Field {$field['label']} wajib diisi";
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field['type'] === 'richtext') {
                $fieldRules[] = 'string';

                if (isset($field['min_length'])) {
                    $fieldRules[] = 'min:' . $field['min_length'];
                    $messages[$field['name'] . '.min'] = "Field {$field['label']} minimal {$field['min_length']} karakter";
                }

                if (isset($field['max_length'])) {
                    $fieldRules[] = 'max:' . $field['max_length'];
                    $messages[$field['name'] . '.max'] = "Field {$field['label']} maksimal {$field['max_length']} karakter";
                }
            }

            $rules[$field['name']] = implode('|', $fieldRules);
        }

        return ['rules' => $rules, 'messages' => $messages];
    }

    /**
     * Validate checklist form data
     */
    protected function validateChecklistForm(array $data, array $config): array
    {
        $rules = [];
        $messages = [];

        $rules['items'] = 'required|array';

        foreach ($config['items'] as $item) {
            if ($item['required'] ?? false) {
                $rules['items.*.checked'] = 'required|boolean';

                if ($item['file_required'] ?? false) {
                    $rules['items.*.file'] = 'required|string';
                    $messages['items.*.file.required'] = "File untuk {$item['label']} wajib diupload";
                }
            }
        }

        return ['rules' => $rules, 'messages' => $messages];
    }

    /**
     * Validate metric form data
     */
    protected function validateMetricForm(array $data, array $config): array
    {
        $rules = [];
        $messages = [];

        foreach ($config['metrics'] as $metric) {
            $fieldRules = $this->getFieldRules($metric);

            if (!empty($fieldRules)) {
                $rules[$metric['name']] = implode('|', $fieldRules);
            }
        }

        return ['rules' => $rules, 'messages' => $messages];
    }

    /**
     * Validate mixed form data
     */
    protected function validateMixedForm(array $data, array $config): array
    {
        $rules = [];
        $messages = [];

        foreach ($config['sections'] as $index => $section) {
            $sectionRules = match($section['type']) {
                'table' => $this->validateTableForm($data[$section['title']] ?? [], $section),
                'narrative' => $this->validateNarrativeForm($data[$section['title']] ?? [], $section),
                'checklist' => $this->validateChecklistForm($data[$section['title']] ?? [], $section),
                default => ['rules' => [], 'messages' => []]
            };

            foreach ($sectionRules['rules'] as $key => $rule) {
                $rules[$section['title'] . '.' . $key] = $rule;
            }

            foreach ($sectionRules['messages'] as $key => $message) {
                $messages[$section['title'] . '.' . $key] = $message;
            }
        }

        return ['rules' => $rules, 'messages' => $messages];
    }

    /**
     * Get validation rules for a field based on its configuration
     */
    protected function getFieldRules(array $field): array
    {
        $rules = [];

        if ($field['required'] ?? false) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($field['type']) {
            case 'text':
                $rules[] = 'string';
                if (isset($field['max_length'])) {
                    $rules[] = 'max:' . $field['max_length'];
                }
                break;

            case 'number':
                $rules[] = 'numeric';
                if (isset($field['min'])) {
                    $rules[] = 'min:' . $field['min'];
                }
                if (isset($field['max'])) {
                    $rules[] = 'max:' . $field['max'];
                }
                break;

            case 'currency':
            case 'decimal':
                $rules[] = 'numeric';
                $rules[] = 'min:0';
                break;

            case 'select':
                if (isset($field['options'])) {
                    $options = is_array($field['options']) ? array_keys($field['options']) : [];
                    if (!empty($options)) {
                        $rules[] = 'in:' . implode(',', $options);
                    }
                }
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'email':
                $rules[] = 'email';
                break;

            case 'url':
                $rules[] = 'url';
                break;

            case 'boolean':
                $rules[] = 'boolean';
                break;

            case 'percentage':
                $rules[] = 'numeric';
                $rules[] = 'min:0';
                $rules[] = 'max:100';
                break;
        }

        return $rules;
    }

    /**
     * Calculate completion percentage based on form data and config
     */
    public function calculateCompletion(array $formData, array $config): float
    {
        $type = $config['type'] ?? null;

        return match($type) {
            'table' => $this->calculateTableCompletion($formData, $config),
            'narrative' => $this->calculateNarrativeCompletion($formData, $config),
            'checklist' => $this->calculateChecklistCompletion($formData, $config),
            'metric' => $this->calculateMetricCompletion($formData, $config),
            'mixed' => $this->calculateMixedCompletion($formData, $config),
            default => 0.0
        };
    }

    protected function calculateTableCompletion(array $data, array $config): float
    {
        $rows = $data['rows'] ?? [];
        $minRows = $config['validations']['min_rows'] ?? 1;

        if (empty($rows)) {
            return 0.0;
        }

        $requiredFields = array_filter($config['columns'], fn($col) => $col['required'] ?? false);
        $requiredCount = count($requiredFields);

        if ($requiredCount === 0) {
            return 100.0; // No required fields, consider complete if has any rows
        }

        $filledRows = 0;
        foreach ($rows as $row) {
            $filledFields = 0;
            foreach ($requiredFields as $field) {
                if (!empty($row[$field['name']])) {
                    $filledFields++;
                }
            }
            if ($filledFields === $requiredCount) {
                $filledRows++;
            }
        }

        // Calculate completion based on:
        // 1. Row count vs minimum required (50%)
        // 2. Data completeness in required fields (50%)
        $rowCompletion = min(100, (count($rows) / max($minRows, 1)) * 100);
        $dataCompletion = ($filledRows / max(count($rows), 1)) * 100;

        return ($rowCompletion + $dataCompletion) / 2;
    }

    protected function calculateNarrativeCompletion(array $data, array $config): float
    {
        $totalFields = count($config['fields']);
        $requiredFields = array_filter($config['fields'], fn($f) => $f['required'] ?? false);
        $filledFields = 0;

        foreach ($requiredFields as $field) {
            if (!empty($data[$field['name']])) {
                $filledFields++;
            }
        }

        if (empty($requiredFields)) {
            return 100.0;
        }

        return ($filledFields / count($requiredFields)) * 100;
    }

    protected function calculateChecklistCompletion(array $data, array $config): float
    {
        $items = $data['items'] ?? [];
        $totalItems = count($config['items']);

        if ($totalItems === 0) {
            return 100.0;
        }

        $checkedItems = 0;
        foreach ($items as $item) {
            if ($item['checked'] ?? false) {
                $checkedItems++;
            }
        }

        return ($checkedItems / $totalItems) * 100;
    }

    protected function calculateMetricCompletion(array $data, array $config): float
    {
        $requiredMetrics = array_filter($config['metrics'], fn($m) => $m['required'] ?? false);
        $filledMetrics = 0;

        foreach ($requiredMetrics as $metric) {
            if (isset($data[$metric['name']]) && $data[$metric['name']] !== null) {
                $filledMetrics++;
            }
        }

        if (empty($requiredMetrics)) {
            return 100.0;
        }

        return ($filledMetrics / count($requiredMetrics)) * 100;
    }

    protected function calculateMixedCompletion(array $data, array $config): float
    {
        $sectionCompletions = [];

        foreach ($config['sections'] as $section) {
            $sectionData = $data[$section['title']] ?? [];
            $completion = match($section['type']) {
                'table' => $this->calculateTableCompletion($sectionData, $section),
                'narrative' => $this->calculateNarrativeCompletion($sectionData, $section),
                'checklist' => $this->calculateChecklistCompletion($sectionData, $section),
                default => 0.0
            };
            $sectionCompletions[] = $completion;
        }

        if (empty($sectionCompletions)) {
            return 0.0;
        }

        return array_sum($sectionCompletions) / count($sectionCompletions);
    }
}
