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
     * Get periode akreditasi by ID
     */
    public function findById(int $id): ?PeriodeAkreditasi
    {
        return $this->repository->findById($id);
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

        $totalButir = $periode->butirAkreditasis()->count();
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
            'butirAkreditasis',
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
                DB::raw('SUM(CASE WHEN pengisian_butirs.status = "approved" THEN 1 ELSE 0 END) as approved')
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
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_butir'),
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

            $totalButir = $periode->butirAkreditasis()->count();
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
