<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Target IKU</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .meta-info {
            margin-bottom: 15px;
        }
        .status-achieved { color: #1e40af; }
        .status-on-track { color: #047857; }
        .status-warning { color: #b45309; }
        .status-critical { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Laporan Data Target IKU</h1>

    <div class="meta-info">
        <p><strong>Tanggal Export:</strong> {{ date('d/m/Y H:i') }}</p>
        <p><strong>Total Target:</strong> {{ $targets->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>IKU</th>
                <th>Tahun Akademik</th>
                <th>Periode</th>
                <th>Unit Kerja</th>
                <th>Target</th>
                <th>Capaian</th>
                <th>%</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($targets as $index => $target)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $target->iku->nama_iku ?? '-' }}</td>
                <td>{{ $target->tahunAkademik->nama ?? '-' }}</td>
                <td>{{ $target->periode }}</td>
                <td>{{ $target->unitKerja->nama ?? '-' }}</td>
                <td>{{ $target->target_value }}</td>
                <td>{{ $target->total_capaian ?? 0 }}</td>
                <td>{{ number_format($target->persentase_capaian ?? 0, 1) }}%</td>
                <td class="status-{{ strtolower(str_replace(' ', '-', $target->status_label ?? '')) }}">
                    @switch($target->status_label)
                        @case('achieved')
                            Tercapai
                            @break
                        @case('on_track')
                            Sesuai Target
                            @break
                        @case('warning')
                            Perlu Perhatian
                            @break
                        @case('critical')
                            Kritis
                            @break
                        @default
                            {{ $target->status_label }}
                    @endswitch
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
