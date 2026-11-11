<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 11px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 22px; }
        .meta { color: #475569; }
        .total { font-size: 16px; font-weight: bold; margin: 18px 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; }
        th { background: #e2e8f0; }
    </style>
</head>
<body>
    <h1>Hasil Pemilihan Ketua OSIS</h1>
    <p class="meta">{{ $periode->nama }} — Tahun akademik {{ $periode->tahun }}</p>
    <p class="meta">Dicetak {{ now(config('app.timezone'))->format('d M Y H:i') }} WIB</p>
    <p class="total">Total suara sah: {{ $total }}</p>
    @foreach (['L' => 'Putra', 'P' => 'Putri'] as $kategori => $judul)
        <h2>Hasil {{ $judul }}</h2>
        <table>
            <thead><tr><th>Nomor urut</th><th>Suara sah</th><th>Persentase</th></tr></thead>
            <tbody>
            @forelse ($hasil->where('kategori', $kategori) as $item)
                <tr><td>{{ $item->nomor_urut }}</td><td>{{ $item->jumlah_suara }}</td><td>{{ $item->persentase }}%</td></tr>
            @empty
                <tr><td colspan="3">Belum ada pasangan.</td></tr>
            @endforelse
            </tbody>
        </table>
    @endforeach
    <p class="meta" style="margin-top:24px">Laporan ini tidak memuat identitas pemilih atau pilihan individual.</p>
</body>
</html>
