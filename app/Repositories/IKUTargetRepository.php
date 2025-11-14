<?php

namespace App\Repositories;

use App\Models\IKUTarget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class IKUTargetRepository
{
    /**
     * Get all IKU targets with optional filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = IKUTarget::with([
            'iku',
            'tahunAkademik',
            'unitKerja',
            'programStudi',
            'latestProgress'
        ]);

        // Apply filters
        if (isset($filters['iku_id'])) {
            $query->where('iku_id', $filters['iku_id']);
        }

        if (isset($filters['tahun_akademik_id'])) {
            $query->where('tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['program_studi_id'])) {
            $query->where('program_studi_id', $filters['program_studi_id']);
        }

        if (isset($filters['periode'])) {
            $query->where('periode', $filters['periode']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('iku', function($q) use ($search) {
                $q->where('nama_iku', 'LIKE', "%{$search}%")
                  ->orWhere('kode_iku', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('tahun_akademik_id', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    /**
     * Find IKU target by ID with relationships
     */
    public function findById(int $id): ?IKUTarget
    {
        return IKUTarget::with([
            'iku',
            'tahunAkademik',
            'unitKerja',
            'programStudi',
            'progress.creator'
        ])->find($id);
    }

    /**
     * Create new IKU target
     */
    public function create(array $data): IKUTarget
    {
        return IKUTarget::create($data);
    }

    /**
     * Update existing IKU target
     */
    public function update(IKUTarget $target, array $data): IKUTarget
    {
        $target->update($data);
        return $target->fresh([
            'iku',
            'tahunAkademik',
            'unitKerja',
            'programStudi'
        ]);
    }

    /**
     * Delete IKU target
     */
    public function delete(IKUTarget $target): bool
    {
        return $target->delete();
    }

    /**
     * Get target statistics
     */
    public function getStatistics(IKUTarget $target): array
    {
        return [
            'target_value' => $target->target_value,
            'total_capaian' => $target->total_capaian,
            'persentase_capaian' => $target->persentase_capaian,
            'jumlah_progress' => $target->progress()->count(),
            'progress_terakhir' => $target->latestProgress,
            'iku' => $target->iku,
            'status' => $this->getTargetStatus($target),
        ];
    }

    /**
     * Get target status based on achievement percentage
     */
    public function getTargetStatus(IKUTarget $target): string
    {
        $persentase = $target->persentase_capaian;

        if ($persentase >= 100) {
            return 'achieved'; // Tercapai
        } elseif ($persentase >= 75) {
            return 'on_track'; // Sesuai Target (Hijau)
        } elseif ($persentase >= 50) {
            return 'warning'; // Perlu Perhatian (Kuning)
        } else {
            return 'critical'; // Kritis (Merah)
        }
    }

    /**
     * Get targets by status
     */
    public function getByStatus(string $status): Collection
    {
        $targets = IKUTarget::with(['iku', 'progress'])->get();

        return $targets->filter(function($target) use ($status) {
            return $this->getTargetStatus($target) === $status;
        });
    }

    /**
     * Get dashboard statistics for all targets
     */
    public function getDashboardStatistics(): array
    {
        $targets = IKUTarget::with(['iku', 'progress'])->get();

        $statistics = [
            'total_targets' => $targets->count(),
            'achieved' => 0,
            'on_track' => 0,
            'warning' => 0,
            'critical' => 0,
            'avg_achievement' => 0,
        ];

        foreach ($targets as $target) {
            $status = $this->getTargetStatus($target);
            $statistics[$status]++;
        }

        $statistics['avg_achievement'] = $targets->avg(function($target) {
            return $target->persentase_capaian;
        });

        return $statistics;
    }

    /**
     * Get targets that need attention (warning or critical status)
     */
    public function getNeedAttention(): Collection
    {
        $targets = IKUTarget::with(['iku', 'tahunAkademik', 'unitKerja'])->get();

        return $targets->filter(function($target) {
            $status = $this->getTargetStatus($target);
            return in_array($status, ['warning', 'critical']);
        });
    }
}
