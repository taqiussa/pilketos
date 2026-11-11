<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Hasil PILKETOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900">
<main class="mx-auto max-w-6xl p-5 sm:p-10">
    <a class="font-bold text-emerald-700" href="{{ route('guru.dashboard') }}">← Dashboard</a>
    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
        <div><h1 class="text-3xl font-black">Hasil pemilihan</h1><p class="text-slate-600">Hanya suara sah; tanpa identitas pemilih.</p></div>
        <div class="flex gap-3">
            <form method="GET"><select name="periode" onchange="this.form.submit()" class="rounded border-slate-300">@foreach ($periodes as $item)<option value="{{ $item->id }}" @selected($periode?->id === $item->id)>{{ $item->tahun }} — {{ $item->nama }}</option>@endforeach</select></form>
            @if ($periode)<a class="rounded bg-slate-900 px-4 py-2 font-bold text-white" href="{{ route('pilketos.hasil.pdf', $periode) }}">Unduh PDF</a>@endif
        </div>
    </div>
    @if ($periode && ! $periode->hasil_publik && $periode->status !== 'ditutup')<p class="mt-4 rounded bg-amber-100 p-3 text-amber-900">Hasil belum dipublikasikan untuk umum.</p>@endif
    <section class="mt-6 grid gap-4 sm:grid-cols-3"><article class="rounded-2xl bg-white p-5 shadow"><p class="text-slate-600">Total suara sah</p><p class="text-3xl font-black">{{ $total }}</p></article></section>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">@foreach (['L' => 'Putra', 'P' => 'Putri'] as $kategori => $judul)<section class="rounded-2xl bg-white p-6 shadow"><h2 class="text-xl font-black">Hasil {{ $judul }}</h2><div class="mt-5 space-y-4">@forelse ($hasil->where('kategori', $kategori) as $item)<article><div class="flex justify-between gap-3 font-bold"><span>Nomor {{ $item->nomor_urut }}</span><span>{{ $item->jumlah_suara }} suara ({{ $item->persentase }}%)</span></div><div class="mt-2 h-3 overflow-hidden rounded bg-slate-200"><div class="h-full bg-emerald-600" style="width: {{ $item->persentase }}%"></div></div></article>@empty<p class="text-slate-500">Belum ada pasangan.</p>@endforelse</div></section>@endforeach</div>
</main>
</body>
</html>
