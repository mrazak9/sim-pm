<?php

namespace App\Services;

use App\Models\PeriodeAkreditasi;
use App\Repositories\PeriodeAkreditasiRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PeriodeAkreditasiService
{
    protected PeriodeAkreditasiRepository $repository;

    public function __construct(PeriodeAkreditasiRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all periode akreditasi
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get all periode akreditasi with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get all periode akreditasi with filters, pagination and sorting (alias for paginate)
     */
    public function getAllPeriodeAkreditasi(
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortOrder = 'desc'
    ): LengthAwarePaginator {
        // Add sorting to filters
        $filters['sort_by'] = $sortBy;
        $filters['sort_order'] = $sortOrder;

        return $this->paginate($filters, $perPage);
    }

    /**
     * Get periode akreditasi by ID
     */
    public function findById(int $id): ?PeriodeAkreditasi
    {
        return $this->repository->findById($id);
    }

    /**
     * Get periode akreditasi by ID (alias for findById)
     */
    public function getPeriodeAkreditasiById(int $id): ?PeriodeAkreditasi
    {
        return $this->findById($id);
    }

    /**
     * Create new periode akreditasi
     */
    public function create(array $data): PeriodeAkreditasi
    {
        DB::beginTransaction();

        try {
            // Validate dates
            $this->validateDates($data);

            // Check for overlapping periods
            if ($this->checkOverlap($data)) {
                throw new \Exception('Periode akreditasi bertumpang tindih dengan periode lain');
            }

            // Validate program_studi_id for prodi akreditasi
            if ($data['jenis_akreditasi'] === 'prodi' && !isset($data['program_studi_id'])) {
                throw new \Exception('Program Studi harus dipilih untuk akreditasi prodi');
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'persiapan';
            }

            $periodeAkreditasi = $this->repository->create($data);

            DB::commit();
            Log::info('Periode Akreditasi created successfully', [
                'periode_id' => $periodeAkreditasi->id,
                'nama' => $periodeAkreditasi->nama
            ]);

            return $periodeAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Periode Akreditasi', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Create new periode akreditasi (alias for create)
     */
    public function createPeriodeAkreditasi(array $data): PeriodeAkreditasi
    {
        return $this->create($data);
    }

    /**
     * Update periode akreditasi
     */
    public function update(int $id, array $data): PeriodeAkreditasi
    {
        DB::beginTransaction();

        try {
            $periodeAkreditasi = $this->repository->findById($id);

            if (!$periodeAkreditasi) {
                throw new \Exception('Periode Akreditasi tidak ditemukan');
            }

            // Validate dates if provided
            if (isset($data['tanggal_mulai']) || isset($data['tanggal_selesai'])) {
                $this->validateDates($data, $periodeAkreditasi);
            }

            // Check for overlapping periods (excluding current)
            if ($this->checkOverlap($data, $id)) {
                throw new \Exception('Periode akreditasi bertumpang tindih dengan periode lain');
            }

            // Validate status transitions
            if (isset($data['status'])) {
                $this->validateStatusTransition($periodeAkreditasi->status, $data['status']);
            }

            $periodeAkreditasi = $this->repository->update($periodeAkreditasi, $data);

            DB::commit();
            Log::info('Periode Akreditasi updated successfully', [
                'periode_id' => $id,
                'nama' => $periodeAkreditasi->nama
            ]);

            return $periodeAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Periode Akreditasi', [
                'error' => $e->getMessage(),
                'periode_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Update periode akreditasi (alias for update)
     */
    public function updatePeriodeAkreditasi(int $id, array $data): PeriodeAkreditasi
    {
        return $this->update($id, $data);
    }

    /**
     * Delete periode akreditasi
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $periodeAkreditasi = $this->repository->findById($id);

            if (!$periodeAkreditasi) {
                throw new \Exception('Periode Akreditasi tidak ditemukan');
            }

            // Check if has associated pengisian butir
            if ($periodeAkreditasi->pengisianButirs()->count() > 0) {
                throw new \Exception('Periode Akreditasi tidak dapat dihapus karena memiliki pengisian butir');
            }

            // Check if has associated dokumen
            if ($periodeAkreditasi->dokumenAkreditasis()->count() > 0) {
                throw new \Exception('Periode Akreditasi tidak dapat dihapus karena memiliki dokumen');
            }

            $result = $this->repository->delete($periodeAkreditasi);

            DB::commit();
            Log::info('Periode Akreditasi deleted successfully', ['periode_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Periode Akreditasi', [
                'error' => $e->getMessage(),
                'periode_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete periode akreditasi (alias for delete)
     */
    public function deletePeriodeAkreditasi(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Get active periode akreditasi
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get periode akreditasi statistics
     */
    public function getStatistics(int $id): array
    {
        $periode = $this->findById($id);

        if (!$periode) {
            throw new \Exception('Periode Akreditasi tidak ditemukan');
        }

        // Get total butir from pengisian butir (unique butir_akreditasi_id)
        $totalButir = $periode->pengisianButirs()->distinct('butir_akreditasi_id')->count('butir_akreditasi_id');
        $totalPengisian = $periode->pengisianButirs()->count();

        // Count by status
        $statusCounts = $periode->pengisianButirs()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Calculate completion percentage
        $approvedCount = $statusCounts['approved'] ?? 0;
        $completionPercentage = $totalButir > 0
            ? round(($approvedCount / $totalButir) * 100, 2)
            : 0;

        return [
            'total_butir' => $totalButir,
            'total_pengisian' => $totalPengisian,
            'pengisian_approved' => $approvedCount,
            'pengisian_draft' => $statusCounts['draft'] ?? 0,
            'pengisian_submitted' => $statusCounts['submitted'] ?? 0,
            'pengisian_review' => $statusCounts['review'] ?? 0,
            'pengisian_revision' => $statusCounts['revision'] ?? 0,
            'completion_percentage' => $completionPercentage,
        ];
    }

    /**
     * Get comprehensive dashboard data for periode akreditasi
     */
    public function getDashboardData(int $id): array
    {
        $periode = $this->findById($id);

        if (!$periode) {
            throw new \Exception('Periode Akreditasi tidak ditemukan');
        }

        // Load relationships
        $periode->load([
            'pengisianButirs.butirAkreditasi',
            'pengisianButirs.picUser',
            'dokumenAkreditasis'
        ]);

        // Basic statistics
        $basicStats = $this->getStatistics($id);

        // Progress by kategori
        $kategoriProgress = $periode->pengisianButirs()
            ->join('butir_akreditasis', 'pengisian_butirs.butir_akreditasi_id', '=', 'butir_akreditasis.id')
            ->select(
                'butir_akreditasis.kategori',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN pengisian_butirs.status = 'approved' THEN 1 ELSE 0 END) as approved")
            )
            ->groupBy('butir_akreditasis.kategori')
            ->get()
            ->map(function ($item) {
                return [
                    'kategori' => $item->kategori ?? 'Tanpa Kategori',
                    'total' => $item->total,
                    'approved' => $item->approved,
                    'percentage' => $item->total > 0 ? round(($item->approved / $item->total) * 100, 2) : 0
                ];
            })
            ->toArray();

        // Recent activities (last 10 updates)
        $recentActivities = $periode->pengisianButirs()
            ->with(['butirAkreditasi', 'picUser'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($pengisian) {
                return [
                    'id' => $pengisian->id,
                    'butir_kode' => $pengisian->butirAkreditasi->kode ?? '-',
                    'butir_nama' => $pengisian->butirAkreditasi->nama ?? '-',
                    'pic_name' => $pengisian->picUser->name ?? '-',
                    'status' => $pengisian->status,
                    'completion' => $pengisian->completion_percentage ?? 0,
                    'updated_at' => $pengisian->updated_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();

        // PIC performance (top contributors)
        $picPerformance = $periode->pengisianButirs()
            ->with('picUser')
            ->select(
                'pic_user_id',
                DB::raw('COUNT(*) as total_butir'),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_butir"),
                DB::raw('AVG(completion_percentage) as avg_completion')
            )
            ->whereNotNull('pic_user_id')
            ->groupBy('pic_user_id')
            ->orderByDesc('approved_butir')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'pic_name' => $item->picUser->name ?? 'Unknown',
                    'total_butir' => $item->total_butir,
                    'approved_butir' => $item->approved_butir,
                    'avg_completion' => round($item->avg_completion ?? 0, 2),
                ];
            })
            ->toArray();

        // Deadline alerts (pengisian approaching deadline)
        $now = Carbon::now();
        $deadlineThreshold = $periode->deadline_pengumpulan
            ? Carbon::parse($periode->deadline_pengumpulan)->subDays(7)
            : null;

        $deadlineAlerts = [];
        if ($deadlineThreshold && $now->greaterThanOrEqualTo($deadlineThreshold)) {
            $deadlineAlerts = $periode->pengisianButirs()
                ->with(['butirAkreditasi', 'picUser'])
                ->whereIn('status', ['draft', 'revision'])
                ->orderBy('updated_at', 'asc')
                ->limit(10)
                ->get()
                ->map(function ($pengisian) use ($periode) {
                    $daysLeft = Carbon::now()->diffInDays(Carbon::parse($periode->deadline_pengumpulan), false);
                    return [
                        'id' => $pengisian->id,
                        'butir_kode' => $pengisian->butirAkreditasi->kode ?? '-',
                        'butir_nama' => $pengisian->butirAkreditasi->nama ?? '-',
                        'pic_name' => $pengisian->picUser->name ?? '-',
                        'status' => $pengisian->status,
                        'completion' => $pengisian->completion_percentage ?? 0,
                        'days_left' => $daysLeft,
                        'is_overdue' => $daysLeft < 0,
                    ];
                })
                ->toArray();
        }

        // Progress trend (last 30 days)
        $trendData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $approvedCount = $periode->pengisianButirs()
                ->where('status', 'approved')
                ->where('updated_at', '<=', $date->endOfDay())
                ->count();

            $totalButir = $periode->pengisianButirs()->distinct('butir_akreditasi_id')->count('butir_akreditasi_id');
            $percentage = $totalButir > 0 ? round(($approvedCount / $totalButir) * 100, 2) : 0;

            $trendData[] = [
                'date' => $date->format('Y-m-d'),
                'approved_count' => $approvedCount,
                'percentage' => $percentage,
            ];
        }

        return [
            'basic_stats' => $basicStats,
            'kategori_progress' => $kategoriProgress,
            'recent_activities' => $recentActivities,
            'pic_performance' => $picPerformance,
            'deadline_alerts' => $deadlineAlerts,
            'trend_data' => $trendData,
            'periode_info' => [
                'nama' => $periode->nama,
                'status' => $periode->status,
                'deadline_pengumpulan' => $periode->deadline_pengumpulan,
                'tanggal_mulai' => $periode->tanggal_mulai,
                'tanggal_selesai' => $periode->tanggal_selesai,
            ],
        ];
    }

    /**
     * Get gap analysis for periode akreditasi
     */
    public function getGapAnalysis(int $id): array
    {
        $periode = $this->findById($id);

        if (!$periode) {
            throw new \Exception('Periode Akreditasi tidak ditemukan');
        }

        // Load relationships
        $periode->load([
            'butirAkreditasis',
            'pengisianButirs.butirAkreditasi',
            'pengisianButirs.picUser'
        ]);

        $allButir = $periode->butirAkreditasis;
        $pengisianButirs = $periode->pengisianButirs->keyBy('butir_akreditasi_id');

        // 1. Missing Butir (belum ada pengisian sama sekali)
        $missingButir = $allButir->filter(function ($butir) use ($pengisianButirs) {
            return !isset($pengisianButirs[$butir->id]);
        })->map(function ($butir) {
            return [
                'id' => $butir->id,
                'kode' => $butir->kode,
                'nama' => $butir->nama,
                'kategori' => $butir->kategori ?? 'Tanpa Kategori',
                'is_mandatory' => $butir->is_mandatory ?? false,
                'bobot' => $butir->bobot ?? 0,
                'reason' => 'Belum ada pengisian',
                'severity' => $butir->is_mandatory ? 'critical' : 'high',
            ];
        })->values()->toArray();

        // 2. Incomplete Butir (draft atau completion < 100%)
        $incompleteButir = $pengisianButirs->filter(function ($pengisian) {
            return $pengisian->status === 'draft' || ($pengisian->completion_percentage ?? 0) < 100;
        })->map(function ($pengisian) {
            $isMandatory = $pengisian->butirAkreditasi->is_mandatory ?? false;
            return [
                'id' => $pengisian->butir_akreditasi_id,
                'kode' => $pengisian->butirAkreditasi->kode ?? '-',
                'nama' => $pengisian->butirAkreditasi->nama ?? '-',
                'kategori' => $pengisian->butirAkreditasi->kategori ?? 'Tanpa Kategori',
                'is_mandatory' => $isMandatory,
                'bobot' => $pengisian->butirAkreditasi->bobot ?? 0,
                'status' => $pengisian->status,
                'completion' => $pengisian->completion_percentage ?? 0,
                'pic_name' => $pengisian->picUser->name ?? '-',
                'reason' => 'Pengisian belum lengkap',
                'severity' => $isMandatory ? 'critical' : 'medium',
            ];
        })->values()->toArray();

        // 3. Butir Perlu Revisi
        $needsRevisionButir = $pengisianButirs->filter(function ($pengisian) {
            return $pengisian->status === 'revision';
        })->map(function ($pengisian) {
            $isMandatory = $pengisian->butirAkreditasi->is_mandatory ?? false;
            return [
                'id' => $pengisian->butir_akreditasi_id,
                'kode' => $pengisian->butirAkreditasi->kode ?? '-',
                'nama' => $pengisian->butirAkreditasi->nama ?? '-',
                'kategori' => $pengisian->butirAkreditasi->kategori ?? 'Tanpa Kategori',
                'is_mandatory' => $isMandatory,
                'bobot' => $pengisian->butirAkreditasi->bobot ?? 0,
                'status' => $pengisian->status,
                'completion' => $pengisian->completion_percentage ?? 0,
                'pic_name' => $pengisian->picUser->name ?? '-',
                'review_notes' => $pengisian->review_notes,
                'reason' => 'Memerlukan perbaikan',
                'severity' => $isMandatory ? 'high' : 'medium',
            ];
        })->values()->toArray();

        // 4. Mandatory Butir yang belum approved
        $mandatoryNotApproved = $allButir->filter(function ($butir) use ($pengisianButirs) {
            if (!($butir->is_mandatory ?? false)) {
                return false;
            }
            $pengisian = $pengisianButirs[$butir->id] ?? null;
            return !$pengisian || $pengisian->status !== 'approved';
        })->map(function ($butir) use ($pengisianButirs) {
            $pengisian = $pengisianButirs[$butir->id] ?? null;
            return [
                'id' => $butir->id,
                'kode' => $butir->kode,
                'nama' => $butir->nama,
                'kategori' => $butir->kategori ?? 'Tanpa Kategori',
                'is_mandatory' => true,
                'bobot' => $butir->bobot ?? 0,
                'status' => $pengisian ? $pengisian->status : 'not_started',
                'completion' => $pengisian ? ($pengisian->completion_percentage ?? 0) : 0,
                'pic_name' => $pengisian ? ($pengisian->picUser->name ?? '-') : '-',
                'reason' => $pengisian ? 'Butir wajib belum disetujui' : 'Butir wajib belum dikerjakan',
                'severity' => 'critical',
            ];
        })->values()->toArray();

        // 5. Gap Analysis per Kategori
        $kategoriGaps = $allButir->groupBy(function ($butir) {
            return $butir->kategori ?? 'Tanpa Kategori';
        })->map(function ($butirs, $kategori) use ($pengisianButirs) {
            $totalButir = $butirs->count();
            $completedButir = $butirs->filter(function ($butir) use ($pengisianButirs) {
                $pengisian = $pengisianButirs[$butir->id] ?? null;
                return $pengisian && $pengisian->status === 'approved';
            })->count();

            $missingCount = $butirs->filter(function ($butir) use ($pengisianButirs) {
                return !isset($pengisianButirs[$butir->id]);
            })->count();

            $incompleteCount = $butirs->filter(function ($butir) use ($pengisianButirs) {
                $pengisian = $pengisianButirs[$butir->id] ?? null;
                return $pengisian && $pengisian->status !== 'approved';
            })->count();

            $gap = $totalButir - $completedButir;
            $gapPercentage = $totalButir > 0 ? round(($gap / $totalButir) * 100, 2) : 0;

            return [
                'kategori' => $kategori,
                'total_butir' => $totalButir,
                'completed' => $completedButir,
                'missing' => $missingCount,
                'incomplete' => $incompleteCount,
                'gap' => $gap,
                'gap_percentage' => $gapPercentage,
                'completion_percentage' => $totalButir > 0 ? round(($completedButir / $totalButir) * 100, 2) : 0,
            ];
        })->values()->toArray();

        // 6. Overall Summary
        $totalButir = $allButir->count();
        $totalMandatory = $allButir->where('is_mandatory', true)->count();
        $completedButir = $pengisianButirs->where('status', 'approved')->count();
        $totalGap = $totalButir - $completedButir;
        $criticalGap = count($missingButir) + count($mandatoryNotApproved);

        // 7. Recommendations
        $recommendations = [];

        if (count($missingButir) > 0) {
            $recommendations[] = [
                'priority' => 'critical',
                'title' => 'Butir Belum Dikerjakan',
                'description' => count($missingButir) . ' butir belum memiliki pengisian. Segera assign PIC dan mulai pengisian.',
                'action' => 'Assign PIC untuk butir yang missing',
                'count' => count($missingButir),
            ];
        }

        if (count($mandatoryNotApproved) > 0) {
            $recommendations[] = [
                'priority' => 'critical',
                'title' => 'Butir Wajib Belum Selesai',
                'description' => count($mandatoryNotApproved) . ' butir wajib belum disetujui. Ini akan menghambat proses akreditasi.',
                'action' => 'Prioritaskan penyelesaian butir wajib',
                'count' => count($mandatoryNotApproved),
            ];
        }

        if (count($needsRevisionButir) > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Butir Perlu Revisi',
                'description' => count($needsRevisionButir) . ' butir memerlukan perbaikan berdasarkan review.',
                'action' => 'Review catatan revisi dan perbaiki secepatnya',
                'count' => count($needsRevisionButir),
            ];
        }

        if (count($incompleteButir) > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Pengisian Belum Lengkap',
                'description' => count($incompleteButir) . ' butir masih dalam status draft atau belum 100% lengkap.',
                'action' => 'Lanjutkan pengisian hingga selesai',
                'count' => count($incompleteButir),
            ];
        }

        // Calculate readiness score
        $readinessScore = $totalButir > 0 ? round(($completedButir / $totalButir) * 100, 2) : 0;
        $mandatoryScore = $totalMandatory > 0
            ? round((($totalMandatory - count($mandatoryNotApproved)) / $totalMandatory) * 100, 2)
            : 100;

        return [
            'summary' => [
                'total_butir' => $totalButir,
                'total_mandatory' => $totalMandatory,
                'completed_butir' => $completedButir,
                'total_gap' => $totalGap,
                'critical_gap' => $criticalGap,
                'readiness_score' => $readinessScore,
                'mandatory_score' => $mandatoryScore,
                'readiness_status' => $this->getReadinessStatus($readinessScore, $mandatoryScore),
            ],
            'missing_butir' => $missingButir,
            'incomplete_butir' => $incompleteButir,
            'needs_revision' => $needsRevisionButir,
            'mandatory_not_approved' => $mandatoryNotApproved,
            'kategori_gaps' => $kategoriGaps,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Determine readiness status based on scores
     */
    protected function getReadinessStatus(float $readinessScore, float $mandatoryScore): string
    {
        if ($mandatoryScore < 100) {
            return 'not_ready'; // Butir wajib belum selesai
        }

        if ($readinessScore >= 90) {
            return 'ready';
        } elseif ($readinessScore >= 70) {
            return 'almost_ready';
        } elseif ($readinessScore >= 50) {
            return 'in_progress';
        } else {
            return 'at_risk';
        }
    }

    /**
     * Get periode by program studi
     */
    public function getByProgram(int $programStudiId): Collection
    {
        return $this->repository->getByProgram($programStudiId);
    }

    /**
     * Get periode by lembaga
     */
    public function getByLembaga(string $lembaga): Collection
    {
        return $this->repository->getByLembaga($lembaga);
    }

    /**
     * Get periode institusi
     */
    public function getInstitusi(): Collection
    {
        return $this->repository->getInstitusi();
    }

    /**
     * Get periode prodi
     */
    public function getProdi(): Collection
    {
        return $this->repository->getProdi();
    }

    /**
     * Get expired periode
     */
    public function getExpired(): Collection
    {
        return $this->repository->getExpired();
    }

    /**
     * Get upcoming periode
     */
    public function getUpcoming(int $days = 30): Collection
    {
        return $this->repository->getUpcoming($days);
    }

    /**
     * Validate dates
     */
    protected function validateDates(array $data, ?PeriodeAkreditasi $existing = null): void
    {
        $tanggalMulai = isset($data['tanggal_mulai'])
            ? Carbon::parse($data['tanggal_mulai'])
            : ($existing ? Carbon::parse($existing->tanggal_mulai) : null);

        $tanggalSelesai = isset($data['tanggal_selesai'])
            ? Carbon::parse($data['tanggal_selesai'])
            : ($existing ? Carbon::parse($existing->tanggal_selesai) : null);

        $deadlinePengumpulan = isset($data['deadline_pengumpulan'])
            ? Carbon::parse($data['deadline_pengumpulan'])
            : ($existing && $existing->deadline_pengumpulan ? Carbon::parse($existing->deadline_pengumpulan) : null);

        if ($tanggalMulai && $tanggalSelesai && $tanggalSelesai->lt($tanggalMulai)) {
            throw new \Exception('Tanggal selesai harus lebih besar dari tanggal mulai');
        }

        if ($deadlinePengumpulan && $tanggalSelesai && $deadlinePengumpulan->gt($tanggalSelesai)) {
            throw new \Exception('Deadline pengumpulan tidak boleh melewati tanggal selesai');
        }

        if ($deadlinePengumpulan && $tanggalMulai && $deadlinePengumpulan->lt($tanggalMulai)) {
            throw new \Exception('Deadline pengumpulan harus setelah tanggal mulai');
        }
    }

    /**
     * Check for overlapping periods
     */
    protected function checkOverlap(array $data, ?int $exceptId = null): bool
    {
        if (!isset($data['tanggal_mulai']) || !isset($data['tanggal_selesai'])) {
            return false;
        }

        $query = PeriodeAkreditasi::query()
            ->where(function($q) use ($data) {
                $q->whereBetween('tanggal_mulai', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                  ->orWhereBetween('tanggal_selesai', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                  ->orWhere(function($q2) use ($data) {
                      $q2->where('tanggal_mulai', '<=', $data['tanggal_mulai'])
                         ->where('tanggal_selesai', '>=', $data['tanggal_selesai']);
                  });
            });

        // Filter by same jenis_akreditasi
        if (isset($data['jenis_akreditasi'])) {
            $query->where('jenis_akreditasi', $data['jenis_akreditasi']);
        }

        // For prodi, check overlap only within same program_studi_id
        if (isset($data['program_studi_id'])) {
            $query->where('program_studi_id', $data['program_studi_id']);
        }

        // Exclude current record when updating
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Validate status transitions
     */
    protected function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $validTransitions = [
            'persiapan' => ['pengisian', 'ditunda'],
            'pengisian' => ['review', 'ditunda'],
            'review' => ['selesai', 'pengisian'],
            'selesai' => [],
            'ditunda' => ['persiapan', 'pengisian'],
        ];

        if (!isset($validTransitions[$currentStatus])) {
            throw new \Exception('Status saat ini tidak valid');
        }

        if ($currentStatus === $newStatus) {
            return; // Allow same status
        }

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new \Exception("Transisi status dari '{$currentStatus}' ke '{$newStatus}' tidak diizinkan");
        }
    }
}
