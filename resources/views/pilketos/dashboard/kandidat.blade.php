<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kandidat PILKETOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900">
<main class="mx-auto max-w-6xl p-5 sm:p-10">
    <a class="font-bold text-emerald-700" href="{{ route('guru.dashboard') }}">← Dashboard</a>
    <h1 class="mt-3 text-3xl font-black">Pasangan kandidat</h1>
    @if (session('success'))<p class="mt-4 rounded bg-emerald-100 p-3">{{ session('success') }}</p>@endif
    @if ($errors->any())<div class="mt-4 rounded bg-red-100 p-3 text-red-800">{{ $errors->first() }}</div>@endif
    <form method="GET" class="mt-6"><select name="periode" onchange="this.form.submit()" class="rounded border-slate-300">@foreach ($periodes as $item)<option value="{{ $item->id }}" @selected($periode?->id === $item->id)>{{ $item->tahun }} — {{ $item->nama }}</option>@endforeach</select></form>
    @if ($periode)
        <form method="POST" enctype="multipart/form-data" action="{{ route('pilketos.kandidat.store') }}" class="mt-6 grid gap-4 rounded-2xl bg-white p-5 shadow md:grid-cols-2" x-data="candidateForm()">
            @csrf
            <input type="hidden" name="periode_id" value="{{ $periode->id }}">
            <label class="grid gap-1"><span class="font-bold">Kategori</span><select name="kategori" x-model="kategori" @change="lookup('ketua'); lookup('wakil')" required class="rounded border-slate-300"><option value="L">Putra</option><option value="P">Putri</option></select></label>
            <label class="grid gap-1"><span class="font-bold">Nomor urut</span><input name="nomor_urut" type="number" min="1" max="999" required class="rounded border-slate-300"></label>
            <label class="grid gap-1"><span class="font-bold">NIS ketua</span><input name="ketua_nis" x-model="ketuaNis" @input.debounce.450ms="lookup('ketua')" type="number" inputmode="numeric" required class="rounded border-slate-300"><span x-show="loading.ketua" class="text-sm text-slate-500">Memeriksa siswa…</span><span x-show="ketua.name" x-text="ketua.name + ' — ' + ketua.kelas" class="text-sm font-semibold text-emerald-700"></span><span x-show="errors.ketua" x-text="errors.ketua" class="text-sm text-red-700"></span></label>
            <label class="grid gap-1"><span class="font-bold">NIS wakil</span><input name="wakil_nis" x-model="wakilNis" @input.debounce.450ms="lookup('wakil')" type="number" inputmode="numeric" required class="rounded border-slate-300"><span x-show="loading.wakil" class="text-sm text-slate-500">Memeriksa siswa…</span><span x-show="wakil.name" x-text="wakil.name + ' — ' + wakil.kelas" class="text-sm font-semibold text-emerald-700"></span><span x-show="errors.wakil" x-text="errors.wakil" class="text-sm text-red-700"></span></label>
            <label class="grid gap-1"><span class="font-bold">Visi</span><textarea name="visi" class="rounded border-slate-300"></textarea></label>
            <label class="grid gap-1"><span class="font-bold">Misi</span><textarea name="misi" class="rounded border-slate-300"></textarea></label>
            <label class="grid gap-1"><span class="font-bold">Foto ketua</span><input name="foto_ketua" type="file" accept="image/*"></label>
            <label class="grid gap-1"><span class="font-bold">Foto wakil</span><input name="foto_wakil" type="file" accept="image/*"></label>
            <label class="grid gap-1"><span class="font-bold">Foto pasangan</span><input name="foto_pasangan" type="file" accept="image/*"></label>
            <button :disabled="loading.ketua || loading.wakil" class="rounded bg-emerald-700 px-4 py-2 font-bold text-white disabled:cursor-not-allowed disabled:opacity-50 md:col-span-2">Tambah pasangan</button>
        </form>
    @endif
    <section class="mt-6 overflow-x-auto rounded-2xl bg-white shadow"><table class="w-full text-left"><thead class="bg-slate-50"><tr><th class="p-3">Kategori</th><th>Nomor</th><th>Ketua NIS</th><th>Wakil NIS</th><th>Status</th><th class="print:hidden">Aksi</th></tr></thead><tbody>@forelse ($pasangan as $item)<tr class="border-t"><td class="p-3">{{ $item->kategori === 'L' ? 'Putra' : 'Putri' }}</td><td>{{ $item->nomor_urut }}</td><td>{{ $item->ketua_nis }}</td><td>{{ $item->wakil_nis }}</td><td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="flex gap-3 p-3 print:hidden"><form method="POST" action="{{ route('pilketos.kandidat.toggle', $item) }}">@csrf @method('PATCH')<button class="font-bold text-amber-700">{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form><form method="POST" action="{{ route('pilketos.kandidat.destroy', $item) }}" onsubmit="return confirm('Hapus pasangan ini?')">@csrf @method('DELETE')<button class="font-bold text-red-700">Hapus</button></form></td></tr>@empty<tr><td class="p-4 text-slate-500" colspan="6">Belum ada pasangan kandidat.</td></tr>@endforelse</tbody></table></section>
    @if ($pasangan instanceof \Illuminate\Pagination\LengthAwarePaginator)<div class="mt-4">{{ $pasangan->links() }}</div>@endif
</main>
<script>
function candidateForm() {
    return { kategori: 'L', ketuaNis: '', wakilNis: '', ketua: {}, wakil: {}, loading: { ketua: false, wakil: false }, errors: { ketua: '', wakil: '' }, async lookup(role) { const nis = role === 'ketua' ? this.ketuaNis : this.wakilNis; this[role] = {}; this.errors[role] = ''; if (!nis || nis.length < 4) return; this.loading[role] = true; try { const response = await fetch(`{{ route('pilketos.kandidat.lookup') }}?periode_id={{ $periode?->id }}&kategori=${this.kategori}&nis=${encodeURIComponent(nis)}`, { headers: { Accept: 'application/json' } }); const payload = await response.json(); if (!response.ok) throw new Error(payload.message); this[role] = payload; } catch (error) { this.errors[role] = error.message || 'Siswa tidak dapat diverifikasi.'; } finally { this.loading[role] = false; } } };
}
</script>
</body>
</html>
