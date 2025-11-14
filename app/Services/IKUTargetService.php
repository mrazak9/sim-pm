<?php

namespace App\Services;

use App\Models\IKUTarget;
use App\Repositories\IKUTargetRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IKUTargetService
{
    protected IKUTargetRepository $repository;

    public function __construct(IKUTargetRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all IKU targets with filters
     */
    public function getAllTargets(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get IKU target by ID
     */
    public function getTargetById(int $id): ?IKUTarget
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new IKU target
     */
    public function createTarget(array $data): IKUTarget
    {
        DB::beginTransaction();

        try {
            $target = $this->repository->create($data);

            DB::commit();
            Log::info('IKU Target created successfully', ['target_id' => $target->id]);

            return $target;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create IKU Target', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update existing IKU target
     */
    public function updateTarget(int $id, array $data): IKUTarget
    {
        DB::beginTransaction();

        try {
            $target = $this->repository->findById($id);

            if (!$target) {
                throw new \Exception('IKU Target tidak ditemukan');
            }

            $target = $this->repository->update($target, $data);

            DB::commit();
            Log::info('IKU Target updated successfully', ['target_id' => $target->id]);

            return $target;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update IKU Target', ['target_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete IKU target
     */
    public function deleteTarget(int $id): bool
    {
        DB::beginTransaction();

        try {
            $target = $this->repository->findById($id);

            if (!$target) {
                throw new \Exception('IKU Target tidak ditemukan');
            }

            // Check if target has progress entries
            if ($target->progress()->count() > 0) {
                throw new \Exception('Target tidak dapat dihapus karena memiliki data progress');
            }

            $result = $this->repository->delete($target);

            DB::commit();
            Log::info('IKU Target deleted successfully', ['target_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete IKU Target', ['target_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get target statistics with status
     */
    public function getTargetStatistics(int $id): array
    {
        $target = $this->repository->findById($id);

        if (!$target) {
            throw new \Exception('IKU Target tidak ditemukan');
        }

        return $this->repository->getStatistics($target);
    }

    /**
     * Get dashboard statistics for all targets
     */
    public function getDashboardStatistics(): array
    {
        return $this->repository->getDashboardStatistics();
    }

    /**
     * Get targets that need attention (warning or critical)
     */
    public function getTargetsNeedAttention(): Collection
    {
        return $this->repository->getNeedAttention();
    }

    /**
     * Get targets by status
     */
    public function getTargetsByStatus(string $status): Collection
    {
        $validStatuses = ['achieved', 'on_track', 'warning', 'critical'];

        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Status tidak valid');
        }

        return $this->repository->getByStatus($status);
    }

    /**
     * Calculate if target is at risk
     */
    public function isTargetAtRisk(int $id): array
    {
        $target = $this->repository->findById($id);

        if (!$target) {
            throw new \Exception('IKU Target tidak ditemukan');
        }

        $status = $this->repository->getTargetStatus($target);
        $isAtRisk = in_array($status, ['warning', 'critical']);

        return [
            'target_id' => $target->id,
            'status' => $status,
            'is_at_risk' => $isAtRisk,
            'persentase_capaian' => $target->persentase_capaian,
            'target_value' => $target->target_value,
            'total_capaian' => $target->total_capaian,
            'gap' => $target->target_value - $target->total_capaian,
        ];
    }
}
