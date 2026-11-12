<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar Token {{ $kelas->tingkat }} {{ $kelas->nama }}</title>
    <style>
        @page { size: A4 portrait; margin: 16mm; }
        body { color: #0f172a; font: 12px/1.4 Arial, sans-serif; }
        .header { display: flex; align-items: center; gap: 16px; border-bottom: 2px solid #047857; padding-bottom: 12px; }
        .logo { height: 52px; max-width: 70px; object-fit: contain; }
        h1 { font-size: 16px; margin: 0; } p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #94a3b8; padding: 7px; vertical-align: top; text-align: left; }
        th { background: #ecfdf5; font-weight: bold; } thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .number { width: 7%; text-align: center; } .token { font-family: monospace; font-size: 13px; letter-spacing: .12em; }
        .actions { margin: 16px 0; } button { background: #047857; border: 0; border-radius: 5px; color: white; cursor: pointer; font-weight: bold; padding: 8px 12px; }
        .notice { color: #64748b; font-size: 10px; margin-top: 12px; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Print Token</button></div>
    <header class="header">
        @if ($logoUrl)<img class="logo" src="{{ $logoUrl }}" alt="Logo sekolah">@endif
        <div><p><strong>SMP Al Musyaffa</strong></p><h1>DAFTAR TOKEN PEMILIHAN KETUA OSIS</h1><p>Tahun Akademik: {{ $periode->tahun }} · Kelas: {{ $kelas->tingkat }} {{ $kelas->nama }} · Tanggal Cetak: {{ now()->format('d M Y') }}</p></div>
    </header>
    <table>
        <thead><tr><th class="number">No</th><th>Nama Siswa</th><th>Kelas</th><th>Token</th></tr></thead>
        <tbody>@forelse ($rows as $row)<tr><td class="number">{{ $row['no'] }}</td><td>{{ $row['name'] }}</td><td>{{ $row['kelas'] }}</td><td class="token">{{ $row['token'] ?? '-' }}</td></tr>@empty<tr><td colspan="4">Tidak ada siswa pada kelas ini.</td></tr>@endforelse</tbody>
    </table>
    @if (collect($rows)->contains(fn (array $row): bool => $row['token_label'] === 'Token lama tidak tersedia'))<p class="notice">Sebagian token lama tidak dapat dipulihkan dan ditampilkan sebagai tanda minus.</p>@endif
</body>
</html>
