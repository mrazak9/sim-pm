<?php

namespace App\Services;

use App\Models\PengisianButir;
use App\Models\PengisianButirLock;
use App\Repositories\PengisianButirRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PengisianButirService
{
    protected PengisianButirRepository $repository;

    public function __construct(PengisianButirRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all pengisian butir
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get all pengisian butir with filters and pagination
     */
    public function paginate(array $filters = [], int|string $perPage = 15): mixed
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get all pengisian butir with filters, pagination, and sorting
     * Alias for backward compatibility with controller
     */
    public function getAllPengisianButir(
        array $filters = [],
        int|string $perPage = 15,
        string $sortBy = 'created_at',
        string $sortOrder = 'desc'
    ): mixed {
        // Add sorting to filters
        $filters['sort_by'] = $sortBy;
        $filters['sort_order'] = $sortOrder;

        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get pengisian butir by ID
     */
    public function findById(int $id): ?PengisianButir
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new pengisian butir
     */
    public function create(array $data): PengisianButir
    {
        DB::beginTransaction();

        try {
            // Check if pengisian already exists for this periode and butir
            if (isset($data['periode_akreditasi_id']) && isset($data['butir_akreditasi_id'])) {
                if ($this->repository->existsForPeriodeAndButir(
                    $data['periode_akreditasi_id'],
                    $data['butir_akreditasi_id']
                )) {
                    throw new \Exception('Pengisian butir untuk periode dan butir ini sudah ada');
                }
            }

            // Set default status
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }

            // Set default is_complete
            if (!isset($data['is_complete'])) {
                $data['is_complete'] = false;
            }

            // Calculate initial completion percentage
            $data['completion_percentage'] = $this->calculateCompletionPercentage($data);

            $pengisianButir = $this->repository->create($data);

            DB::commit();
            Log::info('Pengisian Butir created successfully', [
                'pengisian_id' => $pengisianButir->id,
                'periode_id' => $pengisianButir->periode_akreditasi_id,
                'butir_id' => $pengisianButir->butir_akreditasi_id
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Pengisian Butir', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update pengisian butir
     */
    public function update(int $id, array $data): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Check if periode is locked due to deadline
            $this->validateDeadline($pengisianButir);

            // Only allow updates if status is draft or revision
            if (!in_array($pengisianButir->status, ['draft', 'revision'])) {
                throw new \Exception('Pengisian butir hanya dapat diubah jika statusnya draft atau revision');
            }

            // Recalculate completion percentage
            $mergedData = array_merge($pengisianButir->toArray(), $data);
            $data['completion_percentage'] = $this->calculateCompletionPercentage($mergedData);

            // Auto-mark as complete if completion is 100%
            if ($data['completion_percentage'] >= 100) {
                $data['is_complete'] = true;
            }

            $pengisianButir = $this->repository->update($pengisianButir, $data);

            DB::commit();
            Log::info('Pengisian Butir updated successfully', [
                'pengisian_id' => $id,
                'completion' => $pengisianButir->completion_percentage
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete pengisian butir
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Check if periode is locked due to deadline
            $this->validateDeadline($pengisianButir);

            // Only allow deletion if status is draft
            if ($pengisianButir->status !== 'draft') {
                throw new \Exception('Hanya pengisian butir dengan status draft yang dapat dihapus');
            }

            $result = $this->repository->delete($pengisianButir);

            DB::commit();
            Log::info('Pengisian Butir deleted successfully', ['pengisian_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Create pengisian butir (alias for backward compatibility)
     */
    public function createPengisianButir(array $data): PengisianButir
    {
        return $this->create($data);
    }

    /**
     * Update pengisian butir (alias for backward compatibility)
     */
    public function updatePengisianButir(int $id, array $data): PengisianButir
    {
        return $this->update($id, $data);
    }

    /**
     * Delete pengisian butir (alias for backward compatibility)
     */
    public function deletePengisianButir(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Submit pengisian butir for review
     */
    public function submit(int $id): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Check if periode is locked due to deadline
            $this->validateDeadline($pengisianButir);

            // Validate current status
            if (!in_array($pengisianButir->status, ['draft', 'revision'])) {
                throw new \Exception('Hanya pengisian dengan status draft atau revision yang dapat disubmit');
            }

            // Validate completion
            if (!$pengisianButir->is_complete) {
                throw new \Exception('Pengisian butir belum lengkap. Harap lengkapi semua field yang diperlukan');
            }

            // Validate required fields
            if (empty($pengisianButir->konten)) {
                throw new \Exception('Konten pengisian tidak boleh kosong');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'submitted',
                'submitted_at' => now()
            ]);

            DB::commit();
            Log::info('Pengisian Butir submitted successfully', [
                'pengisian_id' => $id,
                'submitted_by' => $pengisianButir->pic_user_id
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Approve pengisian butir
     */
    public function approve(int $id, ?string $notes = null): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Validate current status
            if (!in_array($pengisianButir->status, ['submitted', 'review'])) {
                throw new \Exception('Hanya pengisian dengan status submitted atau review yang dapat diapprove');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $notes
            ]);

            DB::commit();
            Log::info('Pengisian Butir approved successfully', [
                'pengisian_id' => $id,
                'reviewed_by' => Auth::id()
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Reject pengisian butir
     */
    public function reject(int $id, string $notes): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Validate current status
            if (!in_array($pengisianButir->status, ['submitted', 'review'])) {
                throw new \Exception('Hanya pengisian dengan status submitted atau review yang dapat direject');
            }

            // Notes are required for rejection
            if (empty($notes)) {
                throw new \Exception('Catatan review harus diisi saat melakukan reject');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'revision',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $notes
            ]);

            DB::commit();
            Log::info('Pengisian Butir rejected successfully', [
                'pengisian_id' => $id,
                'reviewed_by' => Auth::id()
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Mark pengisian for review
     */
    public function revise(int $id): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Only submitted pengisian can be moved to review
            if ($pengisianButir->status !== 'submitted') {
                throw new \Exception('Hanya pengisian dengan status submitted yang dapat masuk tahap review');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'review',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now()
            ]);

            DB::commit();
            Log::info('Pengisian Butir moved to review', [
                'pengisian_id' => $id,
                'reviewed_by' => Auth::id()
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to move Pengisian Butir to review', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get summary for a periode
     */
    public function getSummary(int $periodeId): array
    {
        return $this->repository->getSummary($periodeId);
    }

    /**
     * Get pengisian by periode
     */
    public function getByPeriode(int $periodeId): Collection
    {
        return $this->repository->getByPeriode($periodeId);
    }

    /**
     * Get pengisian by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->repository->getByStatus($status);
    }

    /**
     * Get pengisian for review
     */
    public function getForReview(): Collection
    {
        return $this->repository->getForReview();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Calculate completion percentage
     */
    protected function calculateCompletionPercentage(array $data): float
    {
        $requiredFields = ['konten', 'konten_plain'];
        $filledFields = 0;
        $totalFields = count($requiredFields);

        foreach ($requiredFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $filledFields++;
            }
        }

        // If is_complete is explicitly set to true, return 100%
        if (isset($data['is_complete']) && $data['is_complete'] === true) {
            return 100.0;
        }

        return $totalFields > 0 ? round(($filledFields / $totalFields) * 100, 2) : 0.0;
    }

    /**
     * Validate if periode is still within deadline
     *
     * @param PengisianButir $pengisianButir
     * @throws \Exception if deadline has passed
     */
    protected function validateDeadline(PengisianButir $pengisianButir): void
    {
        $periode = $pengisianButir->periodeAkreditasi;

        if (!$periode) {
            throw new \Exception('Periode Akreditasi tidak ditemukan');
        }

        // Check if deadline has passed
        if ($periode->is_expired) {
            $deadlineFormatted = $periode->deadline_pengumpulan->format('d M Y');
            throw new \Exception(
                "Periode akreditasi sudah melewati deadline pengumpulan ({$deadlineFormatted}). " .
                "Pengisian butir tidak dapat diubah lagi."
            );
        }

        // Check if periode is already completed
        if ($periode->status === 'selesai') {
            throw new \Exception(
                "Periode akreditasi sudah selesai. Pengisian butir tidak dapat diubah lagi."
            );
        }
    }

    /**
     * Check if pengisian butir is locked
     *
     * @param int $id
     * @return array
     */
    public function checkLockStatus(int $id): array
    {
        $pengisianButir = $this->repository->findById($id);

        if (!$pengisianButir) {
            throw new \Exception('Pengisian Butir tidak ditemukan');
        }

        $periode = $pengisianButir->periodeAkreditasi;
        $isLocked = false;
        $reason = null;

        // Check deadline
        if ($periode && $periode->is_expired) {
            $isLocked = true;
            $reason = 'Deadline pengumpulan telah terlewati';
        }

        // Check periode status
        if ($periode && $periode->status === 'selesai') {
            $isLocked = true;
            $reason = 'Periode akreditasi sudah selesai';
        }

        // Check pengisian status
        if ($pengisianButir->status === 'approved') {
            $isLocked = true;
            $reason = 'Pengisian sudah disetujui';
        }

        return [
            'is_locked' => $isLocked,
            'reason' => $reason,
            'deadline' => $periode ? $periode->deadline_pengumpulan : null,
            'is_expired' => $periode ? $periode->is_expired : false,
            'sisa_hari' => $periode ? $periode->sisa_hari : null,
            'status' => $pengisianButir->status,
        ];
    }

    /**
     * Save a version snapshot of pengisian butir
     *
     * @param PengisianButir $pengisianButir
     * @param string $changeType
     * @param string|null $changeSummary
     * @return \App\Models\PengisianButirVersion
     */
    protected function saveVersion(
        PengisianButir $pengisianButir,
        string $changeType = 'updated',
        ?string $changeSummary = null
    ): \App\Models\PengisianButirVersion {
        // Get the latest version number
        $latestVersion = \App\Models\PengisianButirVersion::where('pengisian_butir_id', $pengisianButir->id)
            ->max('version_number');

        $versionNumber = ($latestVersion ?? 0) + 1;

        // Create version snapshot
        $version = \App\Models\PengisianButirVersion::create([
            'pengisian_butir_id' => $pengisianButir->id,
            'version_number' => $versionNumber,
            'user_id' => Auth::id(),
            'konten' => $pengisianButir->konten,
            'konten_plain' => $pengisianButir->konten_plain,
            'files' => $pengisianButir->files,
            'status' => $pengisianButir->status,
            'notes' => $pengisianButir->notes,
            'completion_percentage' => $pengisianButir->completion_percentage,
            'is_complete' => $pengisianButir->is_complete,
            'change_type' => $changeType,
            'change_summary' => $changeSummary,
            'metadata' => [
                'reviewed_by' => $pengisianButir->reviewed_by,
                'reviewed_at' => $pengisianButir->reviewed_at,
                'review_notes' => $pengisianButir->review_notes,
            ],
        ]);

        Log::info('Version snapshot created', [
            'pengisian_id' => $pengisianButir->id,
            'version' => $versionNumber,
            'change_type' => $changeType,
        ]);

        return $version;
    }

    /**
     * Get version history for pengisian butir
     *
     * @param int $id
     * @return Collection
     */
    public function getVersionHistory(int $id): Collection
    {
        $pengisianButir = $this->repository->findById($id);

        if (!$pengisianButir) {
            throw new \Exception('Pengisian Butir tidak ditemukan');
        }

        return $pengisianButir->versions()->with('user')->get();
    }

    /**
     * Get specific version of pengisian butir
     *
     * @param int $pengisianId
     * @param int $versionNumber
     * @return \App\Models\PengisianButirVersion|null
     */
    public function getVersion(int $pengisianId, int $versionNumber): ?\App\Models\PengisianButirVersion
    {
        return \App\Models\PengisianButirVersion::where('pengisian_butir_id', $pengisianId)
            ->where('version_number', $versionNumber)
            ->with('user')
            ->first();
    }

    /**
     * Restore pengisian butir from a specific version
     *
     * @param int $pengisianId
     * @param int $versionNumber
     * @return PengisianButir
     */
    public function restoreFromVersion(int $pengisianId, int $versionNumber): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($pengisianId);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Check if periode is locked
            $this->validateDeadline($pengisianButir);

            // Check if status allows restoration
            if (!in_array($pengisianButir->status, ['draft', 'revision'])) {
                throw new \Exception('Pengisian butir hanya dapat direstore jika statusnya draft atau revision');
            }

            // Get the version to restore
            $version = $this->getVersion($pengisianId, $versionNumber);

            if (!$version) {
                throw new \Exception("Version {$versionNumber} tidak ditemukan");
            }

            // Save current state before restoring
            $this->saveVersion($pengisianButir, 'before_restore', "Sebelum restore ke versi {$versionNumber}");

            // Restore data from version
            $pengisianButir = $this->repository->update($pengisianButir, [
                'konten' => $version->konten,
                'konten_plain' => $version->konten_plain,
                'files' => $version->files,
                'notes' => $version->notes,
                'completion_percentage' => $version->completion_percentage,
                'is_complete' => $version->is_complete,
            ]);

            // Save new version after restore
            $this->saveVersion($pengisianButir, 'restored', "Direstore dari versi {$versionNumber}");

            DB::commit();
            Log::info('Pengisian Butir restored from version', [
                'pengisian_id' => $pengisianId,
                'version_number' => $versionNumber,
                'restored_by' => Auth::id(),
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to restore Pengisian Butir from version', [
                'error' => $e->getMessage(),
                'pengisian_id' => $pengisianId,
                'version_number' => $versionNumber,
            ]);
            throw $e;
        }
    }

    /**
     * Compare two versions of pengisian butir
     *
     * @param int $pengisianId
     * @param int $version1
     * @param int $version2
     * @return array
     */
    public function compareVersions(int $pengisianId, int $version1, int $version2): array
    {
        $v1 = $this->getVersion($pengisianId, $version1);
        $v2 = $this->getVersion($pengisianId, $version2);

        if (!$v1 || !$v2) {
            throw new \Exception('Salah satu atau kedua version tidak ditemukan');
        }

        return [
            'version_1' => [
                'version_number' => $v1->version_number,
                'created_at' => $v1->created_at,
                'user' => $v1->user ? $v1->user->name : '-',
                'change_type' => $v1->change_type,
                'konten' => $v1->konten,
                'status' => $v1->status,
                'completion_percentage' => $v1->completion_percentage,
            ],
            'version_2' => [
                'version_number' => $v2->version_number,
                'created_at' => $v2->created_at,
                'user' => $v2->user ? $v2->user->name : '-',
                'change_type' => $v2->change_type,
                'konten' => $v2->konten,
                'status' => $v2->status,
                'completion_percentage' => $v2->completion_percentage,
            ],
            'differences' => [
                'konten_changed' => $v1->konten !== $v2->konten,
                'status_changed' => $v1->status !== $v2->status,
                'completion_changed' => $v1->completion_percentage !== $v2->completion_percentage,
                'files_changed' => json_encode($v1->files) !== json_encode($v2->files),
            ],
        ];
    }

    /**
     * Acquire edit lock for pengisian butir
     *
     * @param int $pengisianId
     * @param int $durationMinutes
     * @return PengisianButirLock
     * @throws \Exception
     */
    public function acquireLock(int $pengisianId, int $durationMinutes = 30): PengisianButirLock
    {
        DB::beginTransaction();

        try {
            // Clean up expired locks first
            $this->cleanupExpiredLocks();

            $pengisianButir = $this->repository->findById($pengisianId);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Check if there's an active lock by another user
            $existingLock = PengisianButirLock::where('pengisian_butir_id', $pengisianId)
                ->active()
                ->first();

            if ($existingLock && $existingLock->user_id !== Auth::id()) {
                throw new \Exception(
                    "Pengisian butir sedang diedit oleh {$existingLock->user->name}. " .
                    "Silakan coba lagi dalam {$existingLock->getRemainingMinutes()} menit."
                );
            }

            // Create or update lock
            $lock = PengisianButirLock::updateOrCreate(
                ['pengisian_butir_id' => $pengisianId],
                [
                    'user_id' => Auth::id(),
                    'locked_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addMinutes($durationMinutes),
                ]
            );

            DB::commit();

            Log::info('Edit lock acquired', [
                'pengisian_butir_id' => $pengisianId,
                'user_id' => Auth::id(),
                'expires_at' => $lock->expires_at,
            ]);

            return $lock->load('user');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to acquire lock', [
                'error' => $e->getMessage(),
                'pengisian_id' => $pengisianId,
            ]);
            throw $e;
        }
    }

    /**
     * Release edit lock
     *
     * @param int $pengisianId
     * @return bool
     */
    public function releaseLock(int $pengisianId): bool
    {
        DB::beginTransaction();

        try {
            $lock = PengisianButirLock::where('pengisian_butir_id', $pengisianId)
                ->where('user_id', Auth::id())
                ->first();

            if ($lock) {
                $lock->delete();

                Log::info('Edit lock released', [
                    'pengisian_butir_id' => $pengisianId,
                    'user_id' => Auth::id(),
                ]);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to release lock', [
                'error' => $e->getMessage(),
                'pengisian_id' => $pengisianId,
            ]);
            throw $e;
        }
    }

    /**
     * Extend edit lock duration
     *
     * @param int $pengisianId
     * @param int $additionalMinutes
     * @return PengisianButirLock
     */
    public function extendLock(int $pengisianId, int $additionalMinutes = 30): PengisianButirLock
    {
        DB::beginTransaction();

        try {
            $lock = PengisianButirLock::where('pengisian_butir_id', $pengisianId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $lock->update([
                'expires_at' => Carbon::now()->addMinutes($additionalMinutes),
            ]);

            DB::commit();

            Log::info('Edit lock extended', [
                'pengisian_butir_id' => $pengisianId,
                'user_id' => Auth::id(),
                'new_expires_at' => $lock->expires_at,
            ]);

            return $lock->load('user');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to extend lock', [
                'error' => $e->getMessage(),
                'pengisian_id' => $pengisianId,
            ]);
            throw $e;
        }
    }

    /**
     * Check lock status
     *
     * @param int $pengisianId
     * @return array
     */
    public function checkEditLock(int $pengisianId): array
    {
        $lock = PengisianButirLock::where('pengisian_butir_id', $pengisianId)
            ->active()
            ->with('user')
            ->first();

        if (!$lock) {
            return [
                'is_locked' => false,
                'locked_by' => null,
                'locked_by_current_user' => false,
                'expires_at' => null,
                'remaining_minutes' => 0,
            ];
        }

        return [
            'is_locked' => true,
            'locked_by' => [
                'id' => $lock->user->id,
                'name' => $lock->user->name,
            ],
            'locked_by_current_user' => $lock->user_id === Auth::id(),
            'expires_at' => $lock->expires_at,
            'remaining_minutes' => $lock->getRemainingMinutes(),
        ];
    }

    /**
     * Clean up expired locks
     *
     * @return int Number of locks cleaned up
     */
    protected function cleanupExpiredLocks(): int
    {
        return PengisianButirLock::expired()->delete();
    }
}
