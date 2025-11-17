<?php

namespace App\Services;

use App\Models\SpmiMonitoring;
use App\Repositories\SpmiMonitoringRepository;
use App\Repositories\SpmiStandardRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class SpmiMonitoringService
{
    public function __construct(
        private SpmiMonitoringRepository $repository,
        private SpmiStandardRepository $standardRepository
    ) {}

    /**
     * Get all SPMI monitorings with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get SPMI monitoring by ID.
     */
    public function getById(int $id): ?SpmiMonitoring
    {
        return $this->repository->findById($id);
    }

    /**
     * Create monitoring with auto-code generation.
     */
    public function create(array $data): SpmiMonitoring
    {
        DB::beginTransaction();
        try {
            // Validate that standard exists
            $standard = $this->standardRepository->findById($data['spmi_standard_id']);
            if (!$standard) {
                throw new Exception('SPMI standard not found');
            }

            // Validate monitoring_date
            if (isset($data['monitoring_date'])) {
                $this->validateMonitoringDate($data['monitoring_date']);
            }

            // Generate code if not provided
            if (empty($data['monitoring_code'])) {
                $data['monitoring_code'] = $this->repository->generateMonitoringCode();
            } else {
                // Check if code already exists
                if ($this->repository->codeExists($data['monitoring_code'])) {
                    throw new Exception('Monitoring code already exists');
                }
            }

            // Set status and monitored_by
            $data['status'] = $data['status'] ?? 'planned';
            $data['monitored_by'] = auth()->id();

            $monitoring = $this->repository->create($data);

            DB::commit();
            Log::info('SPMI monitoring created', ['id' => $monitoring->id, 'code' => $monitoring->monitoring_code]);

            return $monitoring;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create SPMI monitoring', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update monitoring.
     */
    public function update(int $id, array $data): SpmiMonitoring
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->findById($id);

            if (!$monitoring) {
                throw new Exception('SPMI monitoring not found');
            }

            // Validate monitoring_date if being updated
            if (isset($data['monitoring_date'])) {
                $this->validateMonitoringDate($data['monitoring_date']);
            }

            // Validate code uniqueness if code is being changed
            if (isset($data['monitoring_code']) && $data['monitoring_code'] !== $monitoring->monitoring_code) {
                if ($this->repository->codeExists($data['monitoring_code'], $id)) {
                    throw new Exception('Monitoring code already exists');
                }
            }

            $this->repository->update($monitoring, $data);

            DB::commit();
            Log::info('SPMI monitoring updated', ['id' => $monitoring->id]);

            return $monitoring->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update SPMI monitoring', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete monitoring.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->findById($id);

            if (!$monitoring) {
                throw new Exception('SPMI monitoring not found');
            }

            // Clean up report file if exists
            if ($monitoring->report_file) {
                Storage::delete($monitoring->report_file);
            }

            $this->repository->delete($monitoring);

            DB::commit();
            Log::info('SPMI monitoring deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete SPMI monitoring', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Start monitoring (change status to 'ongoing').
     */
    public function start(int $id): SpmiMonitoring
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->findById($id);

            if (!$monitoring) {
                throw new Exception('SPMI monitoring not found');
            }

            // Only 'planned' status can be started
            if ($monitoring->status !== 'planned') {
                throw new Exception('Only planned monitorings can be started');
            }

            $this->repository->update($monitoring, ['status' => 'ongoing']);

            DB::commit();
            Log::info('SPMI monitoring started', ['id' => $id]);

            return $monitoring->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start SPMI monitoring', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Complete monitoring with findings, recommendations, and compliance score.
     */
    public function complete(int $id, array $completionData): SpmiMonitoring
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->findById($id);

            if (!$monitoring) {
                throw new Exception('SPMI monitoring not found');
            }

            // Only 'ongoing' status can be completed
            if ($monitoring->status !== 'ongoing') {
                throw new Exception('Only ongoing monitorings can be completed');
            }

            // Validate compliance score (0-100)
            if (isset($completionData['compliance_score'])) {
                if ($completionData['compliance_score'] < 0 || $completionData['compliance_score'] > 100) {
                    throw new Exception('Compliance score must be between 0 and 100');
                }
            }

            // Validate SWOT fields
            if (isset($completionData['strengths']) && empty($completionData['strengths'])) {
                throw new Exception('Strengths field cannot be empty when completing');
            }

            if (isset($completionData['weaknesses']) && empty($completionData['weaknesses'])) {
                throw new Exception('Weaknesses field cannot be empty when completing');
            }

            // Set status to completed
            $completionData['status'] = 'completed';

            $this->repository->update($monitoring, $completionData);

            DB::commit();
            Log::info('SPMI monitoring completed', ['id' => $id]);

            return $monitoring->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete SPMI monitoring', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate report (mark status as 'reported').
     */
    public function generateReport(int $id): SpmiMonitoring
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->findById($id);

            if (!$monitoring) {
                throw new Exception('SPMI monitoring not found');
            }

            // Can only report completed monitorings
            if ($monitoring->status !== 'completed') {
                throw new Exception('Only completed monitorings can be reported');
            }

            $this->repository->update($monitoring, ['status' => 'reported']);

            DB::commit();
            Log::info('SPMI monitoring report generated', ['id' => $id]);

            return $monitoring->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate SPMI monitoring report', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Upload report file.
     */
    public function uploadReport(int $id, $file): SpmiMonitoring
    {
        DB::beginTransaction();
        try {
            $monitoring = $this->repository->findById($id);

            if (!$monitoring) {
                throw new Exception('SPMI monitoring not found');
            }

            // Delete old file if exists
            if ($monitoring->report_file) {
                Storage::delete($monitoring->report_file);
            }

            // Store new file
            $filePath = $file->store('spmi-monitoring-reports', 'private');

            $this->repository->update($monitoring, ['report_file' => $filePath]);

            DB::commit();
            Log::info('SPMI monitoring report uploaded', ['id' => $id, 'file' => $filePath]);

            return $monitoring->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload SPMI monitoring report', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get completed monitorings.
     */
    public function getCompleted(): Collection
    {
        return $this->repository->getCompleted();
    }

    /**
     * Get pending monitorings.
     */
    public function getPending(): Collection
    {
        return $this->repository->getPending();
    }

    /**
     * Get monitorings by standard.
     */
    public function getByStandard(int $standardId): Collection
    {
        return $this->repository->getByStandard($standardId);
    }

    /**
     * Get monitorings by tahun akademik.
     */
    public function getByTahunAkademik(int $tahunAkademikId): Collection
    {
        return $this->repository->getByTahunAkademik($tahunAkademikId);
    }

    /**
     * Get monitorings by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return $this->repository->getByUnitKerja($unitKerjaId);
    }

    /**
     * Get monitorings by monitoring type.
     */
    public function getByMonitoringType(string $type): Collection
    {
        return $this->repository->getByMonitoringType($type);
    }

    /**
     * Get monitorings by compliance level.
     */
    public function getByComplianceLevel(string $level): Collection
    {
        return $this->repository->getByComplianceLevel($level);
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get dashboard data.
     */
    public function getDashboardData(): array
    {
        return $this->repository->getDashboardData();
    }

    /**
     * Validate monitoring date (must be today or earlier).
     */
    private function validateMonitoringDate(string $monitoringDate): void
    {
        if ($monitoringDate > now()->toDateString()) {
            throw new Exception('Monitoring date cannot be in the future');
        }
    }
}
