<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IKU;

class IKUSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ikus = [
            [
                'kode_iku' => 'IKU-001',
                'nama_iku' => 'Persentase Dosen Berpendidikan S3',
                'deskripsi' => 'Persentase dosen tetap yang memiliki pendidikan minimal S3/Doktor',
                'satuan' => 'persen',
                'target_type' => 'increase',
                'kategori' => 'Sumber Daya Manusia',
                'bobot' => 90,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-002',
                'nama_iku' => 'Persentase Dosen Bersertifikat Pendidik',
                'deskripsi' => 'Persentase dosen tetap yang memiliki sertifikat pendidik profesional',
                'satuan' => 'persen',
                'target_type' => 'increase',
                'kategori' => 'Sumber Daya Manusia',
                'bobot' => 85,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-003',
                'nama_iku' => 'Rasio Mahasiswa terhadap Dosen',
                'deskripsi' => 'Perbandingan jumlah mahasiswa aktif dengan jumlah dosen tetap',
                'satuan' => 'jumlah',
                'target_type' => 'decrease',
                'kategori' => 'Pendidikan',
                'bobot' => 80,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-004',
                'nama_iku' => 'Indeks Kepuasan Mahasiswa',
                'deskripsi' => 'Tingkat kepuasan mahasiswa terhadap layanan pendidikan (skala 1-5)',
                'satuan' => 'skor',
                'target_type' => 'increase',
                'kategori' => 'Layanan',
                'bobot' => 85,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-005',
                'nama_iku' => 'Persentase Lulusan Tepat Waktu',
                'deskripsi' => 'Persentase mahasiswa yang lulus tepat waktu (4 tahun untuk S1)',
                'satuan' => 'persen',
                'target_type' => 'increase',
                'kategori' => 'Pendidikan',
                'bobot' => 90,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-006',
                'nama_iku' => 'Persentase Lulusan Mendapat Pekerjaan < 6 Bulan',
                'deskripsi' => 'Persentase lulusan yang mendapatkan pekerjaan dalam waktu kurang dari 6 bulan',
                'satuan' => 'persen',
                'target_type' => 'increase',
                'kategori' => 'Lulusan',
                'bobot' => 95,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-007',
                'nama_iku' => 'Jumlah Publikasi Ilmiah Dosen',
                'deskripsi' => 'Jumlah publikasi ilmiah dosen di jurnal bereputasi per tahun',
                'satuan' => 'jumlah',
                'target_type' => 'increase',
                'kategori' => 'Penelitian',
                'bobot' => 90,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-008',
                'nama_iku' => 'Jumlah HKI yang Didaftarkan',
                'deskripsi' => 'Jumlah Hak Kekayaan Intelektual yang didaftarkan per tahun',
                'satuan' => 'jumlah',
                'target_type' => 'increase',
                'kategori' => 'Penelitian',
                'bobot' => 85,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-009',
                'nama_iku' => 'Jumlah Program PKM dengan Masyarakat',
                'deskripsi' => 'Jumlah program pengabdian kepada masyarakat yang dilaksanakan per tahun',
                'satuan' => 'jumlah',
                'target_type' => 'increase',
                'kategori' => 'Pengabdian Masyarakat',
                'bobot' => 80,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-010',
                'nama_iku' => 'Persentase Program Studi Terakreditasi Unggul',
                'deskripsi' => 'Persentase program studi dengan akreditasi minimal B/Baik Sekali',
                'satuan' => 'persen',
                'target_type' => 'increase',
                'kategori' => 'Akreditasi',
                'bobot' => 100,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-011',
                'nama_iku' => 'Indeks Sitasi per Dosen',
                'deskripsi' => 'Rata-rata jumlah sitasi publikasi ilmiah per dosen',
                'satuan' => 'jumlah',
                'target_type' => 'increase',
                'kategori' => 'Penelitian',
                'bobot' => 85,
                'is_active' => true,
            ],
            [
                'kode_iku' => 'IKU-012',
                'nama_iku' => 'Persentase Kerjasama Internasional Aktif',
                'deskripsi' => 'Persentase MoU kerjasama internasional yang aktif dilaksanakan',
                'satuan' => 'persen',
                'target_type' => 'increase',
                'kategori' => 'Kerjasama',
                'bobot' => 80,
                'is_active' => true,
            ],
        ];

        foreach ($ikus as $iku) {
            IKU::create($iku);
        }
    }
}
