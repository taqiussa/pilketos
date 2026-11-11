<x-pilketos.public.layout title="Hasil Pemilihan">
    <section class="rounded-3xl bg-white p-6 shadow-2xl sm:p-10">
        <a class="font-bold text-emerald-700" href="{{ route('pilketos.landing') }}">← Beranda</a>
        <p class="mt-6 text-sm font-bold uppercase tracking-widest text-emerald-700">Hasil resmi</p>
        <h1 class="mt-2 text-3xl font-black">{{ $periode->nama }}</h1>
        <p class="mt-2 text-slate-600">Tahun akademik {{ $periode->tahun }} · Total suara sah {{ $total }}</p>
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            @foreach (['L' => 'Putra', 'P' => 'Putri'] as $kategori => $judul)
                <section class="rounded-2xl border border-slate-200 p-5"><h2 class="text-xl font-black">Hasil {{ $judul }}</h2><div class="mt-5 space-y-4">@forelse ($hasil->where('kategori', $kategori) as $item)<article><div class="flex justify-between gap-3 font-bold"><span>Nomor {{ $item->nomor_urut }}</span><span>{{ $item->jumlah_suara }} ({{ $item->persentase }}%)</span></div><div class="mt-2 h-3 overflow-hidden rounded bg-slate-200"><div class="h-full bg-emerald-600" style="width: {{ $item->persentase }}%"></div></div></article>@empty<p class="text-slate-500">Belum ada data hasil.</p>@endforelse</div></section>
            @endforeach
        </div>
    </section>
</x-pilketos.public.layout>
