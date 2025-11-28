<?php

namespace App\Services;

use App\Models\RTM;
use App\Repositories\RTMRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class RTMService
{
    public function __construct(
        private RTMRepository $repository
    ) {}

    /**
     * Get all RTMs with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get RTM by ID.
     */
    public function getById(int $id): ?RTM
    {
        return $this->repository->findById($id);
    }

    /**
     * Create RTM with auto-code generation.
     */
    public function create(array $data): RTM
    {
        DB::beginTransaction();
        try {
            // Validate meeting date
            if (isset($data['meeting_date'])) {
                $this->validateMeetingDate($data['meeting_date']);
            }

            // Validate chairman and secretary are different users
            if (isset($data['chairman_id']) && isset($data['secretary_id'])) {
                if ($data['chairman_id'] === $data['secretary_id']) {
                    throw new Exception('Chairman and secretary must be different users');
                }
            }

            // Generate code if not provided
            if (empty($data['rtm_code'])) {
                $data['rtm_code'] = $this->repository->generateRTMCode();
            } else {
                // Check if code already exists
                if ($this->repository->codeExists($data['rtm_code'])) {
                    throw new Exception('RTM code already exists');
                }
            }

            // Set status
            $data['status'] = $data['status'] ?? 'planned';

            // Extract participants before creating RTM
            $participants = $data['participants'] ?? [];
            Log::info('RTM Create - Participants data', ['participants' => $participants]);
            unset($data['participants']);

            $rtm = $this->repository->create($data);

            // Sync participants if provided
            if (!empty($participants)) {
                $this->syncParticipants($rtm->id, $participants);
            }

            DB::commit();
            Log::info('RTM created', ['id' => $rtm->id, 'code' => $rtm->rtm_code]);

            return $rtm->fresh(['participants', 'chairman', 'secretary', 'tahunAkademik']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create RTM', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update RTM.
     */
    public function update(int $id, array $data): RTM
    {
        DB::beginTransaction();
        try {
            $rtm = $this->repository->findById($id);

            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Cannot update if status is 'completed'
            if ($rtm->status === 'completed') {
                throw new Exception('Cannot update completed RTMs');
            }

            // Validate meeting date if being updated
            if (isset($data['meeting_date'])) {
                $this->validateMeetingDate($data['meeting_date']);
            }

            // Validate chairman and secretary are different if both are being changed
            if ((isset($data['chairman_id']) || isset($data['secretary_id']))) {
                $chairmanId = $data['chairman_id'] ?? $rtm->chairman_id;
                $secretaryId = $data['secretary_id'] ?? $rtm->secretary_id;
                if ($chairmanId === $secretaryId) {
                    throw new Exception('Chairman and secretary must be different users');
                }
            }

            // Validate code uniqueness if code is being changed
            if (isset($data['rtm_code']) && $data['rtm_code'] !== $rtm->rtm_code) {
                if ($this->repository->codeExists($data['rtm_code'], $id)) {
                    throw new Exception('RTM code already exists');
                }
            }

            // Extract participants before updating RTM
            $participants = $data['participants'] ?? null;
            unset($data['participants']);

            $this->repository->update($rtm, $data);

            // Sync participants if provided
            if ($participants !== null) {
                $this->syncParticipants($rtm->id, $participants);
            }

            DB::commit();
            Log::info('RTM updated', ['id' => $rtm->id]);

            return $rtm->fresh(['participants', 'chairman', 'secretary', 'tahunAkademik']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update RTM', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete RTM (can't delete if completed).
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $rtm = $this->repository->findById($id);

            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Cannot delete if completed
            if ($rtm->status === 'completed') {
                throw new Exception('Cannot delete completed RTMs');
            }

            // Clean up files if exist
            if ($rtm->minutes_file) {
                Storage::delete($rtm->minutes_file);
            }
            if ($rtm->attendance_file) {
                Storage::delete($rtm->attendance_file);
            }

            $this->repository->delete($rtm);

            DB::commit();
            Log::info('RTM deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete RTM', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Start RTM (change status to 'ongoing').
     */
    public function start(int $id): RTM
    {
        DB::beginTransaction();
        try {
            $rtm = $this->repository->findById($id);

            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Only 'planned' status can be started
            if ($rtm->status !== 'planned') {
                throw new Exception('Only planned RTMs can be started');
            }

            $this->repository->update($rtm, ['status' => 'ongoing']);

            DB::commit();
            Log::info('RTM started', ['id' => $id]);

            return $rtm->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start RTM', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Complete RTM with decisions, minutes, and change status to 'completed'.
     */
    public function complete(int $id, array $completionData): RTM
    {
        DB::beginTransaction();
        try {
            $rtm = $this->repository->findById($id);

            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Only 'ongoing' status can be completed
            if ($rtm->status !== 'ongoing') {
                throw new Exception('Only ongoing RTMs can be completed');
            }

            // Must have decisions and minutes when completing
            if (empty($completionData['decisions'])) {
                throw new Exception('Decisions must be provided when completing RTM');
            }

            if (empty($completionData['minutes'])) {
                throw new Exception('Minutes must be provided when completing RTM');
            }

            // Set status to completed
            $completionData['status'] = 'completed';

            $this->repository->update($rtm, $completionData);

            DB::commit();
            Log::info('RTM completed', ['id' => $id]);

            return $rtm->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete RTM', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cancel RTM with reason.
     */
    public function cancel(int $id, string $reason): RTM
    {
        DB::beginTransaction();
        try {
            $rtm = $this->repository->findById($id);

            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Cannot cancel completed RTMs
            if ($rtm->status === 'completed') {
                throw new Exception('Cannot cancel completed RTMs');
            }

            // Add reason to follow_up_plan (if not empty, prepend reason)
            $followUpPlan = $rtm->follow_up_plan ? "CANCELLED: {$reason}\n\n{$rtm->follow_up_plan}" : "CANCELLED: {$reason}";

            $this->repository->update($rtm, [
                'status' => 'cancelled',
                'follow_up_plan' => $followUpPlan,
            ]);

            DB::commit();
            Log::info('RTM cancelled', ['id' => $id, 'reason' => $reason]);

            return $rtm->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel RTM', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Add participant to RTM.
     */
    public function addParticipant(int $rtmId, int $userId, string $role = 'participant'): void
    {
        DB::beginTransaction();
        try {
            $this->repository->addParticipant($rtmId, $userId, $role);
            DB::commit();
            Log::info('RTM participant added', ['rtm_id' => $rtmId, 'user_id' => $userId]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to add RTM participant', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Remove participant from RTM.
     */
    public function removeParticipant(int $rtmId, int $userId): void
    {
        DB::beginTransaction();
        try {
            $this->repository->removeParticipant($rtmId, $userId);
            DB::commit();
            Log::info('RTM participant removed', ['rtm_id' => $rtmId, 'user_id' => $userId]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove RTM participant', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Mark attendance for a participant.
     */
    public function markAttendance(int $rtmId, int $userId, bool $isPresent = true): void
    {
        DB::beginTransaction();
        try {
            $this->repository->markAttendance($rtmId, $userId, $isPresent);
            DB::commit();
            Log::info('RTM participant attendance marked', ['rtm_id' => $rtmId, 'user_id' => $userId, 'is_present' => $isPresent]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark RTM attendance', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Upload minutes file.
     */
    public function uploadMinutes(int $id, $file): RTM
    {
        DB::beginTransaction();
        try {
            $rtm = $this->repository->findById($id);

            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Delete old file if exists
            if ($rtm->minutes_file) {
                Storage::delete($rtm->minutes_file);
            }

            // Store new file
            $filePath = $file->store('rtm-minutes', 'private');

            $this->repository->update($rtm, ['minutes_file' => $filePath]);

            DB::commit();
            Log::info('RTM minutes uploaded', ['id' => $id, 'file' => $filePath]);

            return $rtm->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload RTM minutes', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Upload attendance file.
     */
    public function uploadAttendance(int $id, $file): RTM
    {
        DB::beginTransaction();
        try {
            $rtm = $this->repository->findById($id);

            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Delete old file if exists
            if ($rtm->attendance_file) {
                Storage::delete($rtm->attendance_file);
            }

            // Store new file
            $filePath = $file->store('rtm-attendance', 'private');

            $this->repository->update($rtm, ['attendance_file' => $filePath]);

            DB::commit();
            Log::info('RTM attendance uploaded', ['id' => $id, 'file' => $filePath]);

            return $rtm->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload RTM attendance', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get completed RTMs.
     */
    public function getCompleted(): Collection
    {
        return $this->repository->getCompleted();
    }

    /**
     * Get draft RTMs.
     */
    public function getDraft(): Collection
    {
        return $this->repository->getDraft();
    }

    /**
     * Get upcoming RTMs.
     */
    public function getUpcoming(int $days = 30): Collection
    {
        return $this->repository->getUpcoming($days);
    }

    /**
     * Get RTMs by tahun akademik.
     */
    public function getByTahunAkademik(int $tahunAkademikId): Collection
    {
        return $this->repository->getByTahunAkademik($tahunAkademikId);
    }

    /**
     * Get RTMs by chairman.
     */
    public function getByChairman(int $chairmanId): Collection
    {
        return $this->repository->getByChairman($chairmanId);
    }

    /**
     * Get RTM with participant details.
     */
    public function getWithParticipants(int $id): ?RTM
    {
        return $this->repository->findByIdWithParticipants($id);
    }

    /**
     * Get attendance summary.
     */
    public function getAttendanceSummary(int $rtmId): array
    {
        return $this->repository->getAttendanceSummary($rtmId);
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
     * Validate meeting date (must be valid).
     */
    private function validateMeetingDate(string $meetingDate): void
    {
        try {
            \DateTime::createFromFormat('Y-m-d', $meetingDate);
        } catch (Exception $e) {
            throw new Exception('Invalid meeting date format');
        }
    }

    /**
     * Sync participants for RTM.
     */
    private function syncParticipants(int $rtmId, array $participants): void
    {
        $rtm = $this->repository->findById($rtmId);

        if (!$rtm) {
            throw new Exception('RTM not found');
        }

        // Prepare sync data
        $syncData = [];
        foreach ($participants as $participant) {
            if (isset($participant['user_id'])) {
                $syncData[$participant['user_id']] = [
                    'role' => $participant['role'] ?? 'Peserta',
                    'is_present' => $participant['attended'] ?? false,
                    'notes' => $participant['notes'] ?? null,
                ];
            }
        }

        Log::info('RTM Sync Participants', [
            'rtm_id' => $rtmId,
            'sync_data' => $syncData,
            'participants_count' => count($syncData)
        ]);

        // Sync participants (will add new ones, update existing, and remove ones not in the array)
        $rtm->participants()->sync($syncData);

        Log::info('RTM Participants synced successfully', ['rtm_id' => $rtmId]);
    }
}
