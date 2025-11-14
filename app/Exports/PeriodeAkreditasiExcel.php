<?php

namespace App\Exports;

use App\Models\PeriodeAkreditasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PeriodeAkreditasiExcel implements WithMultipleSheets
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function sheets(): array
    {
        return [
            new PeriodeInfoSheet($this->periodeId),
            new ButirAkreditasiSheet($this->periodeId),
            new PengisianButirSheet($this->periodeId),
            new StatisticsSheet($this->periodeId),
        ];
    }
}

// Sheet 1: Informasi Periode
class PeriodeInfoSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection()
    {
        return collect([PeriodeAkreditasi::findOrFail($this->periodeId)]);
    }

    public function headings(): array
    {
        return [
            'Nama Periode',
            'Jenis Akreditasi',
            'Lembaga',
            'Instrumen',
            'Jenjang',
            'Status',
            'Tanggal Mulai',
            'Deadline Pengumpulan',
            'Jadwal Visitasi',
            'Tanggal Berakhir',
            'Keterangan',
        ];
    }

    public function map($periode): array
    {
        return [
            $periode->nama,
            ucfirst($periode->jenis_akreditasi),
            $periode->lembaga,
            $periode->instrumen ?? '-',
            $periode->jenjang ?? '-',
            ucfirst($periode->status),
            $periode->tanggal_mulai ?? '-',
            $periode->deadline_pengumpulan ?? '-',
            $periode->jadwal_visitasi ?? '-',
            $periode->tanggal_berakhir ?? '-',
            $periode->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563eb']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Informasi Periode';
    }
}

// Sheet 2: Butir Akreditasi
class ButirAkreditasiSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection()
    {
        $periode = PeriodeAkreditasi::with(['butirAkreditasis' => function ($query) {
            $query->orderBy('kode');
        }])->findOrFail($this->periodeId);

        return $periode->butirAkreditasis;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Butir',
            'Nama Butir',
            'Kategori',
            'Instrumen',
            'Bobot',
            'Wajib',
            'Parent',
        ];
    }

    public function map($butir): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $butir->kode,
            $butir->nama,
            $butir->kategori ?? '-',
            $butir->instrumen,
            $butir->bobot ?? '-',
            $butir->is_mandatory ? 'Ya' : 'Tidak',
            $butir->parent ? $butir->parent->kode : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Butir Akreditasi';
    }
}

// Sheet 3: Pengisian Butir
class PengisianButirSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection()
    {
        $periode = PeriodeAkreditasi::with([
            'pengisianButirs.butirAkreditasi',
            'pengisianButirs.picUser',
        ])->findOrFail($this->periodeId);

        return $periode->pengisianButirs;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Butir',
            'Nama Butir',
            'PIC',
            'Status',
            'Completion (%)',
            'Word Count',
            'Terakhir Update',
            'Review Notes',
        ];
    }

    public function map($pengisian): array
    {
        static $no = 0;
        $no++;

        $statusLabel = match($pengisian->status) {
            'approved' => 'Disetujui',
            'review' => 'Dalam Review',
            'submitted' => 'Diajukan',
            'revision' => 'Perlu Revisi',
            default => 'Draft'
        };

        return [
            $no,
            $pengisian->butirAkreditasi->kode ?? '-',
            $pengisian->butirAkreditasi->nama ?? '-',
            $pengisian->picUser->name ?? '-',
            $statusLabel,
            $pengisian->completion_percentage ?? 0,
            $pengisian->word_count ?? 0,
            $pengisian->updated_at->format('d/m/Y H:i'),
            $pengisian->review_notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Pengisian Butir';
    }
}

// Sheet 4: Statistics
class StatisticsSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection()
    {
        $periode = PeriodeAkreditasi::with([
            'butirAkreditasis',
            'pengisianButirs'
        ])->findOrFail($this->periodeId);

        $totalButir = $periode->butirAkreditasis->count();
        $totalPengisian = $periode->pengisianButirs->count();
        $pengisianApproved = $periode->pengisianButirs->where('status', 'approved')->count();
        $pengisianDraft = $periode->pengisianButirs->where('status', 'draft')->count();
        $pengisianSubmitted = $periode->pengisianButirs->where('status', 'submitted')->count();
        $pengisianReview = $periode->pengisianButirs->where('status', 'review')->count();
        $pengisianRevision = $periode->pengisianButirs->where('status', 'revision')->count();

        $completionPercentage = $totalButir > 0
            ? round(($pengisianApproved / $totalButir) * 100, 2)
            : 0;

        return collect([
            ['Metrik', 'Nilai'],
            ['Total Butir Akreditasi', $totalButir],
            ['Total Pengisian', $totalPengisian],
            ['Pengisian Disetujui', $pengisianApproved],
            ['Pengisian Dalam Review', $pengisianReview],
            ['Pengisian Diajukan', $pengisianSubmitted],
            ['Pengisian Perlu Revisi', $pengisianRevision],
            ['Pengisian Draft', $pengisianDraft],
            ['Progress Completion (%)', $completionPercentage],
            ['', ''],
            ['Kategori', 'Jumlah'],
            ...$this->getKategoriStats($periode)
        ]);
    }

    protected function getKategoriStats($periode)
    {
        $stats = [];
        $pengisianByKategori = $periode->pengisianButirs
            ->groupBy('butirAkreditasi.kategori');

        foreach ($pengisianByKategori as $kategori => $items) {
            $stats[] = [
                $kategori ?? 'Tanpa Kategori',
                $items->count()
            ];
        }

        return $stats;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dc2626']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            11 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563eb']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Statistik';
    }
}
