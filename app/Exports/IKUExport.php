<?php

namespace App\Exports;

use App\Models\IKU;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IKUExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return IKU::with('unitKerja')->orderBy('kode_iku')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode IKU',
            'Nama IKU',
            'Kategori',
            'Deskripsi',
            'Unit Kerja',
            'Satuan',
            'Periode Pengukuran',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    /**
     * @param mixed $iku
     * @return array
     */
    public function map($iku): array
    {
        return [
            $iku->kode_iku,
            $iku->nama_iku,
            $iku->kategori ?? '-',
            $iku->deskripsi ?? '-',
            $iku->unitKerja->nama ?? '-',
            $this->getSatuanLabel($iku->satuan),
            $this->getPeriodeLabel($iku->periode_pengukuran),
            $iku->is_active ? 'Aktif' : 'Tidak Aktif',
            $iku->created_at->format('d/m/Y H:i'),
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
     * Get satuan label
     *
     * @param string $satuan
     * @return string
     */
    private function getSatuanLabel(string $satuan): string
    {
        $labels = [
            'persen' => 'Persen (%)',
            'jumlah' => 'Jumlah',
            'skor' => 'Skor',
            'nilai' => 'Nilai',
        ];

        return $labels[$satuan] ?? $satuan;
    }

    /**
     * Get periode label
     *
     * @param string $periode
     * @return string
     */
    private function getPeriodeLabel(string $periode): string
    {
        $labels = [
            'bulanan' => 'Bulanan',
            'triwulan' => 'Triwulanan',
            'semester' => 'Semesteran',
            'tahunan' => 'Tahunan',
        ];

        return $labels[$periode] ?? $periode;
    }
}
