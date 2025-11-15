<?php

namespace App\Services;

use App\Models\AuditSchedule;
use App\Repositories\AuditScheduleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditScheduleService
{
    public function __construct(
        private AuditScheduleRepository $repository
    ) {}

    /**
     * Get all audit schedules with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get audit schedule by ID.
     */
    public function getById(int $id): ?AuditSchedule
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new audit schedule.
     */
    public function create(array $data): AuditSchedule
    {
        DB::beginTransaction();
        try {
            // Validate schedule doesn't conflict with existing schedules
            $this->validateScheduleConflict(
                $data['auditor_lead_id'],
                $data['scheduled_date'],
                $data['estimated_duration'] ?? 120
            );

            $data['status'] = $data['status'] ?? 'scheduled';

            $auditSchedule = $this->repository->create($data);

            // Assign additional auditors if provided
            if (isset($data['auditor_ids']) && is_array($data['auditor_ids'])) {
                $auditors = [];
                foreach ($data['auditor_ids'] as $auditorId) {
                    $auditors[$auditorId] = ['role' => 'member'];
                }
                // Add lead auditor
                $auditors[$data['auditor_lead_id']] = ['role' => 'lead'];

                $this->repository->assignAuditors($auditSchedule, $auditors);
            }

            DB::commit();
            Log::info('Audit schedule created', ['id' => $auditSchedule->id]);

            return $auditSchedule->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create audit schedule', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update audit schedule.
     */
    public function update(int $id, array $data): AuditSchedule
    {
        DB::beginTransaction();
        try {
            $auditSchedule = $this->repository->findById($id);

            if (!$auditSchedule) {
                throw new Exception('Audit schedule not found');
            }

            // Check if schedule is editable
            if (!$auditSchedule->isEditable() && !auth()->user()->hasRole('admin')) {
                throw new Exception('Cannot edit completed or ongoing audit schedule');
            }

            // Validate schedule conflict if date or auditor changed
            if (isset($data['scheduled_date']) || isset($data['auditor_lead_id']) || isset($data['estimated_duration'])) {
                $this->validateScheduleConflict(
                    $data['auditor_lead_id'] ?? $auditSchedule->auditor_lead_id,
                    $data['scheduled_date'] ?? $auditSchedule->scheduled_date,
                    $data['estimated_duration'] ?? $auditSchedule->estimated_duration,
                    $id
                );
            }

            $this->repository->update($auditSchedule, $data);

            // Update auditors if provided
            if (isset($data['auditor_ids']) && is_array($data['auditor_ids'])) {
                $auditors = [];
                foreach ($data['auditor_ids'] as $auditorId) {
                    $auditors[$auditorId] = ['role' => 'member'];
                }
                $leadId = $data['auditor_lead_id'] ?? $auditSchedule->auditor_lead_id;
                $auditors[$leadId] = ['role' => 'lead'];

                $this->repository->assignAuditors($auditSchedule, $auditors);
            }

            DB::commit();
            Log::info('Audit schedule updated', ['id' => $id]);

            return $auditSchedule->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update audit schedule', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete audit schedule.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $auditSchedule = $this->repository->findById($id);

            if (!$auditSchedule) {
                throw new Exception('Audit schedule not found');
            }

            // Check if schedule can be deleted (no findings yet)
            if ($auditSchedule->findings()->exists()) {
                throw new Exception('Cannot delete schedule with existing findings');
            }

            $this->repository->delete($auditSchedule);

            DB::commit();
            Log::info('Audit schedule deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete audit schedule', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Start audit execution.
     */
    public function startAudit(int $id): AuditSchedule
    {
        DB::beginTransaction();
        try {
            $auditSchedule = $this->repository->findById($id);

            if (!$auditSchedule) {
                throw new Exception('Audit schedule not found');
            }

            if ($auditSchedule->status !== 'scheduled') {
                throw new Exception('Only scheduled audits can be started');
            }

            $data = [
                'status' => 'ongoing',
                'actual_start' => now(),
            ];

            $this->repository->update($auditSchedule, $data);

            DB::commit();
            Log::info('Audit started', ['id' => $id]);

            return $auditSchedule->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start audit', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Complete audit.
     */
    public function completeAudit(int $id, array $data): AuditSchedule
    {
        DB::beginTransaction();
        try {
            $auditSchedule = $this->repository->findById($id);

            if (!$auditSchedule) {
                throw new Exception('Audit schedule not found');
            }

            if ($auditSchedule->status !== 'ongoing') {
                throw new Exception('Only ongoing audits can be completed');
            }

            $updateData = [
                'status' => 'completed',
                'actual_end' => now(),
                'summary' => $data['summary'] ?? null,
            ];

            $this->repository->update($auditSchedule, $updateData);

            DB::commit();
            Log::info('Audit completed', ['id' => $id]);

            return $auditSchedule->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete audit', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reschedule audit.
     */
    public function reschedule(int $id, array $data): AuditSchedule
    {
        DB::beginTransaction();
        try {
            $auditSchedule = $this->repository->findById($id);

            if (!$auditSchedule) {
                throw new Exception('Audit schedule not found');
            }

            if (!in_array($auditSchedule->status, ['scheduled', 'ongoing'])) {
                throw new Exception('Only scheduled or ongoing audits can be rescheduled');
            }

            // Validate new schedule doesn't conflict
            if (isset($data['scheduled_date'])) {
                $this->validateScheduleConflict(
                    $auditSchedule->auditor_lead_id,
                    $data['scheduled_date'],
                    $data['estimated_duration'] ?? $auditSchedule->estimated_duration,
                    $id
                );
            }

            $updateData = array_merge($data, ['status' => 'rescheduled']);

            $this->repository->update($auditSchedule, $updateData);

            DB::commit();
            Log::info('Audit rescheduled', ['id' => $id]);

            return $auditSchedule->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reschedule audit', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get upcoming audits.
     */
    public function getUpcoming(int $days = 7): Collection
    {
        return $this->repository->getUpcoming($days);
    }

    /**
     * Get calendar events.
     */
    public function getCalendarEvents(?string $start = null, ?string $end = null): array
    {
        return $this->repository->getCalendarEvents($start, $end);
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Validate schedule doesn't conflict with existing schedules.
     */
    private function validateScheduleConflict(
        int $auditorId,
        string $scheduledDate,
        int $estimatedDuration,
        ?int $exceptId = null
    ): void {
        $scheduledDateTime = new \DateTime($scheduledDate);
        $endDateTime = clone $scheduledDateTime;
        $endDateTime->modify("+{$estimatedDuration} minutes");

        $existingSchedule = AuditSchedule::where('auditor_lead_id', $auditorId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($scheduledDateTime, $endDateTime) {
                $query->whereBetween('scheduled_date', [$scheduledDateTime, $endDateTime])
                    ->orWhere(function ($q) use ($scheduledDateTime, $endDateTime) {
                        $q->where('scheduled_date', '<=', $scheduledDateTime)
                          ->whereRaw('DATE_ADD(scheduled_date, INTERVAL estimated_duration MINUTE) >= ?', [$scheduledDateTime]);
                    });
            });

        if ($exceptId) {
            $existingSchedule->where('id', '!=', $exceptId);
        }

        if ($existingSchedule->exists()) {
            throw new Exception('Auditor already has a schedule at this time');
        }
    }
}
