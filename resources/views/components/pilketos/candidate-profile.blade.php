@props(['pasangan', 'profile', 'showDetails' => true])

<article {{ $attributes->merge(['class' => 'overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_18px_45px_-30px_rgba(15,23,42,.42)]']) }}>
    <div class="relative aspect-[16/9] bg-slate-100">
        @if ($pasangan->photoUrl())
            <img src="{{ $pasangan->photoUrl() }}" alt="Foto pasangan calon nomor {{ $pasangan->nomor_urut }}" class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="flex h-full items-center justify-center bg-gradient-to-br from-emerald-50 to-slate-100 text-sm font-bold text-slate-500">Foto pasangan belum tersedia</div>
        @endif
        <span class="absolute left-5 top-5 inline-flex h-12 min-w-12 items-center justify-center rounded-2xl bg-emerald-700 px-3 text-lg font-black text-white shadow-lg">{{ $pasangan->nomor_urut }}</span>
    </div>
    <div class="p-6 sm:p-7">
        <p class="text-xs font-bold uppercase tracking-[.16em] text-emerald-700">Pasangan calon nomor {{ $pasangan->nomor_urut }}</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <section><p class="text-xs font-bold uppercase tracking-[.12em] text-slate-500">Calon Ketua</p><h2 class="mt-1 text-lg font-black text-slate-900">{{ $profile['ketua']['name'] }}</h2><p class="mt-1 text-sm text-slate-600">Kelas {{ $profile['ketua']['kelas'] }}</p></section>
            <section><p class="text-xs font-bold uppercase tracking-[.12em] text-slate-500">Calon Wakil</p><h2 class="mt-1 text-lg font-black text-slate-900">{{ $profile['wakil']['name'] }}</h2><p class="mt-1 text-sm text-slate-600">Kelas {{ $profile['wakil']['kelas'] }}</p></section>
        </div>
        @if ($showDetails)
            <div class="mt-6 space-y-5 border-t border-slate-100 pt-5"><section><h3 class="text-sm font-black text-slate-900">Visi</h3><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $pasangan->visi ?: 'Visi belum dicantumkan.' }}</p></section><section><h3 class="text-sm font-black text-slate-900">Misi</h3><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $pasangan->misi ?: 'Misi belum dicantumkan.' }}</p></section></div>
        @endif
        {{ $slot }}
    </div>
</article>
