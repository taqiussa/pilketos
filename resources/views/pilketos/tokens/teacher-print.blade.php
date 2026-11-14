<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar Token Guru {{ $periode->tahun }}</title>
    <style>
        @page { size: A4 portrait; margin: 14mm; }
        body { color: #0f172a; font: 10px/1.35 Arial, sans-serif; }
        .actions { margin: 0 0 14px; } button { background: #047857; border: 0; border-radius: 5px; color: #fff; cursor: pointer; font-weight: bold; padding: 8px 12px; }
        .header { align-items: center; border-bottom: 2px solid #047857; display: flex; gap: 14px; padding-bottom: 10px; }
        .logo { height: 50px; max-width: 66px; object-fit: contain; } h1 { font-size: 14px; margin: 1px 0; } p { margin: 2px 0; }
        table { border-collapse: collapse; margin-top: 15px; table-layout: fixed; width: 100%; } th, td { border: 1px solid #94a3b8; padding: 5px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background: #d1fae5; font-weight: bold; } thead { display: table-header-group; } tr { break-inside: avoid; page-break-inside: avoid; }
        .number { text-align: center; width: 5%; } .name { width: 24%; } .username { width: 17%; } .roles { width: 21%; } .token { font-family: monospace; font-size: 10px; font-weight: bold; width: 19%; } .status { width: 14%; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Print Token Guru</button></div>
    <header class="header">
        @if ($logoUrl)<img class="logo" src="{{ $logoUrl }}" alt="Logo sekolah">@endif
        <div>
            <p><strong>SMP Al Musyaffa</strong></p>
            <h1>DAFTAR TOKEN GURU PEMILIHAN KETUA OSIS</h1>
            <p>Tahun Akademik: {{ $periode->tahun ?: '-' }} · Periode: {{ $periode->nama ?: '-' }} · Tanggal Cetak: {{ now()->translatedFormat('d F Y') }}</p>
            @if ($statusLabel)<p>Filter Status: {{ $statusLabel }}</p>@endif
        </div>
    </header>
    <table>
        <thead><tr><th class="number">No</th><th class="name">Nama Guru</th><th class="username">Username</th><th class="roles">Role</th><th class="token">Token</th><th class="status">Status</th></tr></thead>
        <tbody>
            @forelse ($rows as $row)
                <tr><td class="number">{{ $row['no'] }}</td><td>{{ $row['name'] }}</td><td>{{ $row['username'] }}</td><td>{{ $row['roles'] }}</td><td class="token">{{ $row['token_label'] }}</td><td>{{ \App\Services\PilketosTeacherTokenReportService::statusLabel($row['status']) }}</td></tr>
            @empty
                <tr><td colspan="6">Tidak ada data guru untuk filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
