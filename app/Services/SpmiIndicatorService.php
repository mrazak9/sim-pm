<?php

namespace App\Services;

use App\Models\SpmiIndicator;
use App\Models\SpmiIndicatorTarget;
use App\Repositories\SpmiIndicatorRepository;
use App\Repositories\SpmiStandardRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SpmiIndicatorService
{
    public function __construct(
        private SpmiIndicatorRepository $repository,
        private SpmiStandardRepository $standardRepository
    ) {}

    /**
     * Get all SPMI indicators with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get SPMI indicator by ID.
     */
    public function getById(int $id): ?SpmiIndicator
    {
        return $this->repository->findById($id);
    }

    /**
     * Create indicator with auto-code generation based on standard category.
     */
    public function create(array $data): SpmiIndicator
    {
        DB::beginTransaction();
        try {
            // Validate that standard exists and is active
            $standard = $this->standardRepository->findById($data['spmi_standard_id']);
            if (!$standard) {
                throw new Exception('SPMI standard not found');
            }

            if (!$standard->isActive()) {
                throw new Exception('SPMI standard must be active to add indicators');
            }

            // Generate code based on standard category if not provided
            if (empty($data['code'])) {
                $category = $standard->category ?? 'GEN';
                $data['code'] = $this->repository->generateIndicatorCode($category);
            } else {
                // Check if code already exists
                if ($this->repository->codeExists($data['code'])) {
                    throw new Exception('Indicator code already exists');
                }
            }

            // Set default is_active
            $data['is_active'] = $data['is_active'] ?? true;

            $indicator = $this->repository->create($data);

            DB::commit();
            Log::info('SPMI indicator created', ['id' => $indicator->id, 'code' => $indicator->code]);

            return $indicator;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create SPMI indicator', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update indicator.
     */
    public function update(int $id, array $data): SpmiIndicator
    {
        DB::beginTransaction();
        try {
            $indicator = $this->repository->findById($id);

            if (!$indicator) {
                throw new Exception('SPMI indicator not found');
            }

            // Validate code uniqueness if code is being changed
            if (isset($data['code']) && $data['code'] !== $indicator->code) {
                if ($this->repository->codeExists($data['code'], $id)) {
                    throw new Exception('Indicator code already exists');
                }
            }

            $this->repository->update($indicator, $data);

            DB::commit();
            Log::info('SPMI indicator updated', ['id' => $indicator->id]);

            return $indicator->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update SPMI indicator', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete indicator with validation (can't delete if has targets).
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $indicator = $this->repository->findById($id);

            if (!$indicator) {
                throw new Exception('SPMI indicator not found');
            }

            // Check if indicator has targets
            if ($indicator->targets()->exists()) {
                throw new Exception('Cannot delete indicator that has related targets');
            }

            $this->repository->delete($indicator);

            DB::commit();
            Log::info('SPMI indicator deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete SPMI indicator', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Activate indicator (set is_active = true).
     */
    public function activate(int $id): SpmiIndicator
    {
        DB::beginTransaction();
        try {
            $indicator = $this->repository->findById($id);

            if (!$indicator) {
                throw new Exception('SPMI indicator not found');
            }

            $this->repository->update($indicator, ['is_active' => true]);

            DB::commit();
            Log::info('SPMI indicator activated', ['id' => $id]);

            return $indicator->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to activate SPMI indicator', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Deactivate indicator (set is_active = false).
     */
    public function deactivate(int $id): SpmiIndicator
    {
        DB::beginTransaction();
        try {
            $indicator = $this->repository->findById($id);

            if (!$indicator) {
                throw new Exception('SPMI indicator not found');
            }

            $this->repository->update($indicator, ['is_active' => false]);

            DB::commit();
            Log::info('SPMI indicator deactivated', ['id' => $id]);

            return $indicator->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to deactivate SPMI indicator', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create indicator target.
     */
    public function createTarget(int $indicatorId, array $targetData): SpmiIndicatorTarget
    {
        DB::beginTransaction();
        try {
            $indicator = $this->repository->findById($indicatorId);

            if (!$indicator) {
                throw new Exception('SPMI indicator not found');
            }

            // Validate target_value > 0
            if (!isset($targetData['target_value']) || $targetData['target_value'] <= 0) {
                throw new Exception('Target value must be greater than 0');
            }

            // Set default achievement_value and status
            $targetData['spmi_indicator_id'] = $indicatorId;
            $targetData['achievement_value'] = $targetData['achievement_value'] ?? 0;
            $targetData['achievement_percentage'] = 0;
            $targetData['status'] = 'pending';

            $target = SpmiIndicatorTarget::create($targetData);

            DB::commit();
            Log::info('SPMI indicator target created', ['id' => $target->id, 'indicator_id' => $indicatorId]);

            return $target;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create SPMI indicator target', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update indicator target.
     */
    public function updateTarget(int $targetId, array $data): SpmiIndicatorTarget
    {
        DB::beginTransaction();
        try {
            $target = SpmiIndicatorTarget::find($targetId);

            if (!$target) {
                throw new Exception('SPMI indicator target not found');
            }

            // Validate target_value if being updated
            if (isset($data['target_value']) && $data['target_value'] <= 0) {
                throw new Exception('Target value must be greater than 0');
            }

            // Auto-calculate achievement_percentage if achievement_value is provided
            if (isset($data['achievement_value'])) {
                $targetValue = $data['target_value'] ?? $target->target_value;
                if ($targetValue > 0) {
                    $percentage = ($data['achievement_value'] / $targetValue) * 100;
                    $data['achievement_percentage'] = (int) round($percentage);

                    // Auto-update status based on achievement percentage
                    if ($data['achievement_percentage'] >= 100) {
                        $data['status'] = 'achieved';
                    } elseif ($data['achievement_percentage'] >= 70) {
                        $data['status'] = 'at_risk';
                    } else {
                        $data['status'] = 'not_achieved';
                    }
                }
            }

            $target->update($data);

            DB::commit();
            Log::info('SPMI indicator target updated', ['id' => $targetId]);

            return $target->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update SPMI indicator target', ['id' => $targetId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Record achievement and auto-calculate percentage.
     */
    public function recordAchievement(int $targetId, float $achievementValue): SpmiIndicatorTarget
    {
        DB::beginTransaction();
        try {
            $target = SpmiIndicatorTarget::find($targetId);

            if (!$target) {
                throw new Exception('SPMI indicator target not found');
            }

            // Calculate percentage
            $percentage = ($achievementValue / $target->target_value) * 100;
            $achievementPercentage = (int) round($percentage);

            // Determine status based on percentage
            if ($achievementPercentage >= 100) {
                $status = 'achieved';
            } elseif ($achievementPercentage >= 70) {
                $status = 'at_risk';
            } else {
                $status = 'not_achieved';
            }

            $data = [
                'achievement_value' => $achievementValue,
                'achievement_percentage' => $achievementPercentage,
                'status' => $status,
                'measured_by' => auth()->id(),
                'measurement_date' => now()->toDateString(),
            ];

            $target->update($data);

            DB::commit();
            Log::info('SPMI indicator achievement recorded', ['id' => $targetId, 'percentage' => $achievementPercentage]);

            return $target->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to record SPMI indicator achievement', ['id' => $targetId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get indicators by standard.
     */
    public function getByStandard(int $standardId): Collection
    {
        return $this->repository->getByStandard($standardId);
    }

    /**
     * Get active indicators.
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get indicators by measurement type.
     */
    public function getByMeasurementType(string $type): Collection
    {
        return $this->repository->getByMeasurementType($type);
    }

    /**
     * Get indicators by PIC.
     */
    public function getByPIC(int $picId): Collection
    {
        return $this->repository->getByPIC($picId);
    }

    /**
     * Get indicators by frequency.
     */
    public function getByFrequency(string $frequency): Collection
    {
        return $this->repository->getByFrequency($frequency);
    }

    /**
     * Get indicators with targets count.
     */
    public function getWithTargetsCounts(): Collection
    {
        return $this->repository->getWithTargetsCounts();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }
}
