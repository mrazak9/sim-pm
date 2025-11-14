<?php

namespace App\Services;

use App\Models\IKUProgress;
use App\Repositories\IKUProgressRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class IKUProgressService
{
    protected IKUProgressRepository $repository;

    public function __construct(IKUProgressRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all IKU progress with filters
     */
    public function getAllProgress(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get IKU progress by ID
     */
    public function getProgressById(int $id): ?IKUProgress
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new IKU progress
     */
    public function createProgress(array $data, ?UploadedFile $file = null): IKUProgress
    {
        DB::beginTransaction();

        try {
            // Handle file upload
            if ($file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('iku_progress_documents', $filename, 'public');
                $data['bukti_dokumen'] = $path;
            }

            // Set created_by to authenticated user
            $data['created_by'] = auth()->id() ?? 1;

            $progress = $this->repository->create($data);

            DB::commit();
            Log::info('IKU Progress created successfully', ['progress_id' => $progress->id]);

            return $progress;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if transaction failed
            if (isset($data['bukti_dokumen']) && Storage::disk('public')->exists($data['bukti_dokumen'])) {
                Storage::disk('public')->delete($data['bukti_dokumen']);
            }

            Log::error('Failed to create IKU Progress', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update existing IKU progress
     */
    public function updateProgress(int $id, array $data, ?UploadedFile $file = null): IKUProgress
    {
        DB::beginTransaction();

        try {
            $progress = $this->repository->findById($id);

            if (!$progress) {
                throw new \Exception('IKU Progress tidak ditemukan');
            }

            $oldDocument = $progress->bukti_dokumen;

            // Handle file upload
            if ($file) {
                // Delete old file if exists
                if ($oldDocument && Storage::disk('public')->exists($oldDocument)) {
                    Storage::disk('public')->delete($oldDocument);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('iku_progress_documents', $filename, 'public');
                $data['bukti_dokumen'] = $path;
            }

            $progress = $this->repository->update($progress, $data);

            DB::commit();
            Log::info('IKU Progress updated successfully', ['progress_id' => $progress->id]);

            return $progress;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete newly uploaded file if transaction failed
            if (isset($data['bukti_dokumen']) && $data['bukti_dokumen'] !== $oldDocument) {
                if (Storage::disk('public')->exists($data['bukti_dokumen'])) {
                    Storage::disk('public')->delete($data['bukti_dokumen']);
                }
            }

            Log::error('Failed to update IKU Progress', ['progress_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete IKU progress
     */
    public function deleteProgress(int $id): bool
    {
        DB::beginTransaction();

        try {
            $progress = $this->repository->findById($id);

            if (!$progress) {
                throw new \Exception('IKU Progress tidak ditemukan');
            }

            $document = $progress->bukti_dokumen;

            $result = $this->repository->delete($progress);

            // Delete associated file if exists
            if ($document && Storage::disk('public')->exists($document)) {
                Storage::disk('public')->delete($document);
            }

            DB::commit();
            Log::info('IKU Progress deleted successfully', ['progress_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete IKU Progress', ['progress_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get progress summary by target
     */
    public function getProgressSummaryByTarget(int $targetId): array
    {
        return $this->repository->getSummaryByTarget($targetId);
    }

    /**
     * Get recent progress entries
     */
    public function getRecentProgress(int $days = 30, int $limit = 10): Collection
    {
        return $this->repository->getRecent($days, $limit);
    }

    /**
     * Get progress statistics for dashboard
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Download progress document
     */
    public function downloadDocument(int $id): array
    {
        $progress = $this->repository->findById($id);

        if (!$progress) {
            throw new \Exception('IKU Progress tidak ditemukan');
        }

        if (!$progress->bukti_dokumen) {
            throw new \Exception('Tidak ada dokumen yang tersedia');
        }

        if (!Storage::disk('public')->exists($progress->bukti_dokumen)) {
            throw new \Exception('File dokumen tidak ditemukan');
        }

        return [
            'path' => $progress->bukti_dokumen,
            'name' => basename($progress->bukti_dokumen),
        ];
    }

    /**
     * Get progress trend for a target (for charts)
     */
    public function getProgressTrend(int $targetId): array
    {
        $progressList = $this->repository->getSummaryByTarget($targetId)['progress_list'];

        return $progressList->map(function($progress) {
            return [
                'tanggal' => $progress->tanggal_capaian->format('Y-m-d'),
                'nilai' => $progress->nilai_capaian,
                'persentase' => $progress->persentase_capaian,
            ];
        })->toArray();
    }
}
