<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Periode Akreditasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #1e40af;
        }
        .header h2 {
            font-size: 14px;
            margin: 0;
            font-weight: normal;
            color: #64748b;
        }
        .meta-box {
            background-color: #f1f5f9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #2563eb;
        }
        .meta-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-box td {
            padding: 5px 0;
            border: none;
        }
        .meta-box td:first-child {
            width: 35%;
            font-weight: bold;
            color: #1e40af;
        }
        .stats-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            margin: 0 5px;
        }
        .stat-box .number {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .stat-box .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin: 25px 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #2563eb;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #1e40af;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .status-draft { background-color: #e2e8f0; color: #475569; }
        .status-submitted { background-color: #dbeafe; color: #1e40af; }
        .status-review { background-color: #fef3c7; color: #92400e; }
        .status-revision { background-color: #fed7aa; color: #9a3412; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background-color: #2563eb;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN PERIODE AKREDITASI</h1>
        <h2>{{ $periode->nama }}</h2>
    </div>

    <!-- Meta Information -->
    <div class="meta-box">
        <table>
            <tr>
                <td>Jenis Akreditasi</td>
                <td>: {{ ucfirst($periode->jenis_akreditasi) }}</td>
            </tr>
            <tr>
                <td>Lembaga</td>
                <td>: {{ $periode->lembaga }}</td>
            </tr>
            <tr>
                <td>Instrumen</td>
                <td>: {{ $periode->instrumen ?? '-' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: {{ ucfirst($periode->status) }}</td>
            </tr>
            <tr>
                <td>Tanggal Mulai</td>
                <td>: {{ $periode->tanggal_mulai ? \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Deadline Pengumpulan</td>
                <td>: {{ $periode->deadline_pengumpulan ? \Carbon\Carbon::parse($periode->deadline_pengumpulan)->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Generate</td>
                <td>: {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    <!-- Statistics -->
    <div class="section-title">RINGKASAN STATISTIK</div>
    <div class="stats-container">
        <div class="stat-box">
            <div class="number">{{ $totalButir }}</div>
            <div class="label">Total Butir</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $totalPengisian }}</div>
            <div class="label">Total Pengisian</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $pengisianApproved }}</div>
            <div class="label">Disetujui</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ number_format($completionPercentage, 1) }}%</div>
            <div class="label">Progress</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-bar">
        <div class="progress-fill" style="width: {{ $completionPercentage }}%">
            {{ number_format($completionPercentage, 1) }}%
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="section-title">BREAKDOWN STATUS PENGISIAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Status</th>
                <th style="text-align: center;">Jumlah</th>
                <th style="text-align: center;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="status-badge status-approved">Disetujui</span></td>
                <td style="text-align: center;">{{ $pengisianApproved }}</td>
                <td style="text-align: center;">{{ $totalPengisian > 0 ? number_format(($pengisianApproved / $totalPengisian) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><span class="status-badge status-review">Dalam Review</span></td>
                <td style="text-align: center;">{{ $pengisianReview }}</td>
                <td style="text-align: center;">{{ $totalPengisian > 0 ? number_format(($pengisianReview / $totalPengisian) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><span class="status-badge status-submitted">Diajukan</span></td>
                <td style="text-align: center;">{{ $pengisianSubmitted }}</td>
                <td style="text-align: center;">{{ $totalPengisian > 0 ? number_format(($pengisianSubmitted / $totalPengisian) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><span class="status-badge status-revision">Perlu Revisi</span></td>
                <td style="text-align: center;">{{ $pengisianRevision }}</td>
                <td style="text-align: center;">{{ $totalPengisian > 0 ? number_format(($pengisianRevision / $totalPengisian) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><span class="status-badge status-draft">Draft</span></td>
                <td style="text-align: center;">{{ $pengisianDraft }}</td>
                <td style="text-align: center;">{{ $totalPengisian > 0 ? number_format(($pengisianDraft / $totalPengisian) * 100, 1) : 0 }}%</td>
            </tr>
        </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Pengisian by Kategori -->
    @if($pengisianByKategori->count() > 0)
    <div class="section-title">PENGISIAN PER KATEGORI</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Kategori</th>
                <th style="text-align: center;">Total</th>
                <th style="text-align: center;">Disetujui</th>
                <th style="text-align: center;">Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengisianByKategori as $kategori => $data)
            <tr>
                <td>{{ $kategori ?? 'Tanpa Kategori' }}</td>
                <td style="text-align: center;">{{ $data['total'] }}</td>
                <td style="text-align: center;">{{ $data['approved'] }}</td>
                <td style="text-align: center;">
                    {{ $data['total'] > 0 ? number_format(($data['approved'] / $data['total']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Detail Pengisian -->
    @if($periode->pengisianButirs->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">DETAIL PENGISIAN BUTIR</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">No</th>
                <th style="width: 35%;">Butir Akreditasi</th>
                <th style="width: 20%;">PIC</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 20%;">Terakhir Diupdate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periode->pengisianButirs->sortBy('butirAkreditasi.kode') as $index => $pengisian)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $pengisian->butirAkreditasi->kode ?? '-' }}</strong><br>
                    <small>{{ $pengisian->butirAkreditasi->nama ?? '-' }}</small>
                </td>
                <td>{{ $pengisian->picUser->name ?? '-' }}</td>
                <td>
                    @php
                        $statusClass = match($pengisian->status) {
                            'approved' => 'status-approved',
                            'review' => 'status-review',
                            'submitted' => 'status-submitted',
                            'revision' => 'status-revision',
                            default => 'status-draft'
                        };
                        $statusLabel = match($pengisian->status) {
                            'approved' => 'Disetujui',
                            'review' => 'Dalam Review',
                            'submitted' => 'Diajukan',
                            'revision' => 'Perlu Revisi',
                            default => 'Draft'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td>{{ $pengisian->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Informasi Manajemen Penjaminan Mutu</p>
        <p>© {{ date('Y') }} - Dokumen Rahasia</p>
    </div>
</body>
</html>
