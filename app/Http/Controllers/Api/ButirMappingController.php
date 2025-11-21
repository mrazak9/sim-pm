<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ButirMappingService;
use App\Models\ButirColumnMapping;
use Illuminate\Http\Request;

class ButirMappingController extends Controller
{
    protected ButirMappingService $service;

    public function __construct(ButirMappingService $service)
    {
        $this->service = $service;
    }

    /**
     * Get mappings for a butir
     * GET /api/butir-akreditasis/{butirId}/mappings
     */
    public function index(int $butirId)
    {
        $mappings = ButirColumnMapping::getByButirId($butirId);

        return response()->json([
            'success' => true,
            'data' => $mappings,
        ]);
    }

    /**
     * Setup mappings from form config
     * POST /api/butir-akreditasis/{butirId}/mappings/setup
     */
    public function setupFromFormConfig(int $butirId)
    {
        try {
            $mappings = $this->service->setupFromFormConfig($butirId);

            return response()->json([
                'success' => true,
                'message' => 'Mapping berhasil dibuat',
                'data' => $mappings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update mappings manually
     * PUT /api/butir-akreditasis/{butirId}/mappings
     */
    public function update(Request $request, int $butirId)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.label' => 'required|string',
            'fields.*.type' => 'required|string',
        ]);

        try {
            $mappings = $this->service->updateMappings($butirId, $request->fields);

            return response()->json([
                'success' => true,
                'message' => 'Mapping berhasil diupdate',
                'data' => $mappings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
