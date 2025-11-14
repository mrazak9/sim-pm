<?php

namespace App\Exports;

use App\Models\IKUTarget;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IKUTargetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = IKUTarget::with(['iku', 'tahunAkademik', 'unitKerja']);

        // Apply filters
        if (!empty($this->filters['iku_id'])) {
            $query->where('iku_id', $this->filters['iku_id']);
        }

        if (!empty($this->filters['tahun_akademik_id'])) {
            $query->where('tahun_akademik_id', $this->filters['tahun_akademik_id']);
        }

        if (!empty($this->filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $this->filters['unit_kerja_id']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('periode')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'IKU',
            'Tahun Akademik',
            'Periode',
            'Unit Kerja',
            'Target',
            'Total Capaian',
            'Persentase Capaian (%)',
            'Status',
            'Keterangan',
            'Tanggal Dibuat',
        ];
    }

    /**
     * @param mixed $target
     * @return array
     */
    public function map($target): array
    {
        return [
            $target->iku->nama_iku ?? '-',
            $target->tahunAkademik->nama ?? '-',
            $target->periode,
            $target->unitKerja->nama ?? '-',
            $target->target_value,
            $target->total_capaian ?? 0,
            number_format($target->persentase_capaian ?? 0, 2),
            $this->getStatusLabel($target->status_label ?? ''),
            $target->keterangan ?? '-',
            $target->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Get status label
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'achieved' => 'Tercapai',
            'on_track' => 'Sesuai Target',
            'warning' => 'Perlu Perhatian',
            'critical' => 'Kritis',
            default => $status,
        };
    }
}
