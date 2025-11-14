<?php

namespace App\Exports;

use App\Models\PeriodeAkreditasi;
use Barryvdh\DomPDF\Facade\Pdf;

class PeriodeAkreditasiPDF
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function generate()
    {
        $periode = PeriodeAkreditasi::with([
            'butirAkreditasis',
            'pengisianButirs.butirAkreditasi',
            'pengisianButirs.picUser',
            'dokumenAkreditasis'
        ])->findOrFail($this->periodeId);

        // Calculate statistics
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

        // Group pengisian by kategori
        $pengisianByKategori = $periode->pengisianButirs
            ->groupBy('butirAkreditasi.kategori')
            ->map(function ($items) {
                return [
                    'total' => $items->count(),
                    'approved' => $items->where('status', 'approved')->count(),
                    'items' => $items
                ];
            });

        $data = [
            'periode' => $periode,
            'totalButir' => $totalButir,
            'totalPengisian' => $totalPengisian,
            'pengisianApproved' => $pengisianApproved,
            'pengisianDraft' => $pengisianDraft,
            'pengisianSubmitted' => $pengisianSubmitted,
            'pengisianReview' => $pengisianReview,
            'pengisianRevision' => $pengisianRevision,
            'completionPercentage' => $completionPercentage,
            'pengisianByKategori' => $pengisianByKategori,
            'generatedAt' => now()->format('d F Y H:i:s')
        ];

        $pdf = Pdf::loadView('exports.periode-akreditasi-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function download()
    {
        $periode = PeriodeAkreditasi::findOrFail($this->periodeId);
        $filename = 'Laporan_Akreditasi_' . str_replace(' ', '_', $periode->nama) . '_' . date('Y-m-d') . '.pdf';

        return $this->generate()->download($filename);
    }

    public function stream()
    {
        return $this->generate()->stream();
    }
}
