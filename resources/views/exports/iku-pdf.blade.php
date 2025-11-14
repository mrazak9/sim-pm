<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data IKU</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .meta-info {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Laporan Data Indikator Kinerja Utama (IKU)</h1>

    <div class="meta-info">
        <p><strong>Tanggal Export:</strong> {{ date('d/m/Y H:i') }}</p>
        <p><strong>Total IKU:</strong> {{ $ikus->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode IKU</th>
                <th>Nama IKU</th>
                <th>Kategori</th>
                <th>Unit Kerja</th>
                <th>Satuan</th>
                <th>Periode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ikus as $index => $iku)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $iku->kode_iku }}</td>
                <td>{{ $iku->nama_iku }}</td>
                <td>{{ $iku->kategori ?? '-' }}</td>
                <td>{{ $iku->unitKerja->nama ?? '-' }}</td>
                <td>{{ $iku->satuan }}</td>
                <td>{{ $iku->periode_pengukuran }}</td>
                <td>{{ $iku->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
