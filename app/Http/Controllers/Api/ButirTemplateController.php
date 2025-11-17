<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ButirTemplateController extends Controller
{
    /**
     * Get all butir with template status
     */
    public function index(Request $request)
    {
        $query = DB::table('butir_akreditasis')
            ->select('id', 'kode', 'nama', 'deskripsi', 'kategori', 'metadata')
            ->orderBy('kode');

        // Filter by template status
        if ($request->has('has_template')) {
            $hasTemplate = filter_var($request->has_template, FILTER_VALIDATE_BOOLEAN);

            if ($hasTemplate) {
                $query->whereRaw("metadata::jsonb -> 'form_config' IS NOT NULL");
            } else {
                $query->whereRaw("metadata::jsonb -> 'form_config' IS NULL");
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'ILIKE', "%{$search}%")
                  ->orWhere('kode', 'ILIKE', "%{$search}%");
            });
        }

        $butirs = $query->get();

        // Transform data
        $data = $butirs->map(function($butir) {
            $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
            $hasTemplate = isset($metadata['form_config']);

            return [
                'id' => $butir->id,
                'kode' => $butir->kode,
                'nama' => $butir->nama,
                'deskripsi' => $butir->deskripsi,
                'kategori' => $butir->kategori,
                'has_template' => $hasTemplate,
                'template_type' => $hasTemplate ? ($metadata['form_config']['type'] ?? null) : null,
                'template_label' => $hasTemplate ? ($metadata['form_config']['label'] ?? null) : null,
            ];
        });

        // Statistics
        $total = $butirs->count();
        $withTemplate = $data->where('has_template', true)->count();
        $percentage = $total > 0 ? round(($withTemplate / $total) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'stats' => [
                'total' => $total,
                'with_template' => $withTemplate,
                'without_template' => $total - $withTemplate,
                'percentage' => $percentage,
            ]
        ]);
    }

    /**
     * Get template configuration for a butir
     */
    public function show($id)
    {
        $butir = DB::table('butir_akreditasis')
            ->where('id', $id)
            ->first();

        if (!$butir) {
            return response()->json([
                'success' => false,
                'message' => 'Butir tidak ditemukan'
            ], 404);
        }

        $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
        $formConfig = $metadata['form_config'] ?? null;

        return response()->json([
            'success' => true,
            'butir' => [
                'id' => $butir->id,
                'kode' => $butir->kode,
                'nama' => $butir->nama,
                'deskripsi' => $butir->deskripsi,
            ],
            'template' => $formConfig,
            'has_template' => $formConfig !== null,
        ]);
    }

    /**
     * Store or update template configuration
     */
    public function store(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'form_config.type' => 'required|in:table,narrative,checklist,metric,mixed',
            'form_config.label' => 'required|string|max:255',
            'form_config.help_text' => 'nullable|string',
            'form_config.columns' => 'required_if:form_config.type,table|array',
            'form_config.columns.*.name' => 'required_with:form_config.columns|string',
            'form_config.columns.*.label' => 'required_with:form_config.columns|string',
            'form_config.columns.*.type' => 'required_with:form_config.columns|in:text,textarea,number,currency,decimal,percentage,select,date',
            'form_config.columns.*.required' => 'required_with:form_config.columns|boolean',
            'form_config.fields' => 'required_if:form_config.type,narrative|array',
            'form_config.items' => 'required_if:form_config.type,checklist|array',
            'form_config.metrics' => 'required_if:form_config.type,metric|array',
            'form_config.sections' => 'required_if:form_config.type,mixed|array|min:1',
            'form_config.sections.*.type' => 'required_with:form_config.sections|in:table,narrative,checklist,metric',
            'form_config.sections.*.label' => 'required_with:form_config.sections|string',
            'form_config.sections.*.config' => 'required_with:form_config.sections',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get butir
        $butir = DB::table('butir_akreditasis')->where('id', $id)->first();

        if (!$butir) {
            return response()->json([
                'success' => false,
                'message' => 'Butir tidak ditemukan'
            ], 404);
        }

        // Build form config from request
        $formConfig = $request->form_config;

        // Update metadata
        $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
        $metadata['form_config'] = $formConfig;

        DB::table('butir_akreditasis')
            ->where('id', $id)
            ->update(['metadata' => json_encode($metadata)]);

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil disimpan',
            'data' => [
                'id' => $butir->id,
                'kode' => $butir->kode,
                'form_config' => $formConfig,
            ]
        ]);
    }

    /**
     * Delete template configuration
     */
    public function destroy($id)
    {
        $butir = DB::table('butir_akreditasis')->where('id', $id)->first();

        if (!$butir) {
            return response()->json([
                'success' => false,
                'message' => 'Butir tidak ditemukan'
            ], 404);
        }

        $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];

        if (!isset($metadata['form_config'])) {
            return response()->json([
                'success' => false,
                'message' => 'Butir tidak memiliki template'
            ], 404);
        }

        // Remove form_config
        unset($metadata['form_config']);

        DB::table('butir_akreditasis')
            ->where('id', $id)
            ->update(['metadata' => json_encode($metadata)]);

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil dihapus'
        ]);
    }

    /**
     * Copy template from another butir
     */
    public function copy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'source_butir_id' => 'required|integer|exists:butir_akreditasis,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get source butir
        $sourceButir = DB::table('butir_akreditasis')
            ->where('id', $request->source_butir_id)
            ->first();

        $sourceMetadata = $sourceButir->metadata ? json_decode($sourceButir->metadata, true) : [];

        if (!isset($sourceMetadata['form_config'])) {
            return response()->json([
                'success' => false,
                'message' => 'Butir sumber tidak memiliki template'
            ], 400);
        }

        // Get target butir
        $targetButir = DB::table('butir_akreditasis')->where('id', $id)->first();

        if (!$targetButir) {
            return response()->json([
                'success' => false,
                'message' => 'Butir target tidak ditemukan'
            ], 404);
        }

        // Copy template
        $targetMetadata = $targetButir->metadata ? json_decode($targetButir->metadata, true) : [];
        $targetMetadata['form_config'] = $sourceMetadata['form_config'];

        // Update label to match target butir
        $targetMetadata['form_config']['label'] = "Data {$targetButir->nama}";

        DB::table('butir_akreditasis')
            ->where('id', $id)
            ->update(['metadata' => json_encode($targetMetadata)]);

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil disalin',
            'data' => [
                'id' => $targetButir->id,
                'form_config' => $targetMetadata['form_config'],
            ]
        ]);
    }
}
