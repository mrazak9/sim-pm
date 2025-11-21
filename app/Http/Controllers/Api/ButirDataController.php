<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ButirDataService;
use Illuminate\Http\Request;

class ButirDataController extends Controller
{
    protected ButirDataService $service;

    public function __construct(ButirDataService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all data for a pengisian butir
     * GET /api/pengisian-butirs/{pengisianButirId}/data
     */
    public function index(int $pengisianButirId)
    {
        $data = $this->service->getByPengisian($pengisianButirId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create new row
     * POST /api/pengisian-butirs/{pengisianButirId}/data
     */
    public function store(Request $request, int $pengisianButirId)
    {
        $data = $this->service->create($pengisianButirId, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $data->toNamedFields(),
        ], 201);
    }

    /**
     * Update row
     * PUT /api/butir-data/{id}
     */
    public function update(Request $request, int $id)
    {
        $data = $this->service->update($id, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $data->toNamedFields(),
        ]);
    }

    /**
     * Delete row
     * DELETE /api/butir-data/{id}
     */
    public function destroy(int $id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }

    /**
     * Bulk create
     * POST /api/pengisian-butirs/{pengisianButirId}/data/bulk
     */
    public function bulkStore(Request $request, int $pengisianButirId)
    {
        $request->validate([
            'rows' => 'required|array',
            'rows.*' => 'required|array',
        ]);

        $data = $this->service->bulkCreate($pengisianButirId, $request->rows);

        return response()->json([
            'success' => true,
            'message' => count($data) . ' data berhasil ditambahkan',
            'data' => collect($data)->map->toNamedFields(),
        ], 201);
    }

    /**
     * Sync data (replace all with new data)
     * PUT /api/pengisian-butirs/{pengisianButirId}/data/sync
     */
    public function sync(Request $request, int $pengisianButirId)
    {
        $request->validate([
            'rows' => 'required|array',
            'rows.*' => 'required|array',
        ]);

        $data = $this->service->syncData($pengisianButirId, $request->rows);

        return response()->json([
            'success' => true,
            'message' => count($data) . ' data berhasil disinkronkan',
            'data' => collect($data)->map->toNamedFields(),
        ]);
    }

    /**
     * Export to array
     * GET /api/pengisian-butirs/{pengisianButirId}/data/export
     */
    public function export(int $pengisianButirId)
    {
        $data = $this->service->export($pengisianButirId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Query data with filters
     * POST /api/butir-data/query
     */
    public function query(Request $request)
    {
        $request->validate([
            'butir_id' => 'required|integer',
            'filters' => 'array',
            'filters.*.field' => 'required|string',
            'filters.*.operator' => 'required|string',
            'filters.*.value' => 'required',
        ]);

        $query = $this->service->query($request->butir_id);

        foreach ($request->filters ?? [] as $filter) {
            $query->whereField($filter['field'], $filter['operator'], $filter['value']);
        }

        if ($request->has('order_by')) {
            $query->orderByField($request->order_by, $request->get('order_direction', 'asc'));
        }

        if ($request->has('pengisian_butir_id')) {
            $query->byPengisian($request->pengisian_butir_id);
        }

        $data = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
