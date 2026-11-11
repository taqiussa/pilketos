<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Token PILKETOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900">
<main class="mx-auto max-w-6xl p-6">
    <a class="font-bold text-emerald-700" href="{{ route('guru.dashboard') }}">← Dashboard</a>
    <h1 class="mt-4 text-3xl font-black">Token pemilih</h1>
    <p class="mt-1 text-slate-600">Token plaintext hanya tersedia sesaat setelah dibuat atau diganti.</p>
    @if (session('success'))
        <p class="mt-4 rounded bg-emerald-100 p-3">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <p class="mt-4 rounded bg-red-100 p-3 text-red-800">{{ $errors->first() }}</p>
    @endif
    <form method="POST" action="{{ route('pilketos.token.generate') }}" class="mt-6 flex flex-wrap gap-3 rounded-2xl bg-white p-5 shadow">
        @csrf
        <select name="periode_id" class="rounded border-slate-300">
            @foreach ($periodes as $item)
                <option value="{{ $item->id }}" @selected($periode?->id === $item->id)>{{ $item->tahun }}</option>
            @endforeach
        </select>
        <select name="kelas_id" class="rounded border-slate-300">
            @foreach ($kelas as $item)
                <option value="{{ $item->id }}">{{ $item->tingkat }} {{ $item->nama }}</option>
            @endforeach
        </select>
        <button class="rounded bg-emerald-700 px-4 py-2 font-bold text-white">Generate token yang belum ada</button>
    </form>
    @if (session('pilketos.tokens.print'))
        <section class="mt-6 rounded-2xl bg-white p-6 shadow print:shadow-none">
            <button onclick="window.print()" class="mb-4 rounded bg-slate-900 px-3 py-2 text-white print:hidden">Cetak distribusi</button>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach (session('pilketos.tokens.print') as $item)
                    <article class="border border-dashed border-slate-400 p-4">
                        <p class="font-black">{{ $item['name'] }}</p>
                        <p>NIS: {{ $item['nis'] }}</p>
                        <p>Kelas: {{ $item['kelas'] }}</p>
                        <p class="mt-2 text-2xl font-black tracking-[.25em]">{{ $item['token'] }}</p>
                        <p class="mt-2 text-xs">Rahasiakan token ini. Hanya berlaku satu kali.</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    <section class="mt-6 overflow-x-auto rounded-2xl bg-white shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3">NIS</th><th>Kelas</th><th>Status</th><th>Dibuat</th><th class="print:hidden">Aksi</th></tr></thead>
            <tbody>
            @forelse ($tokens as $token)
                <tr class="border-t">
                    <td class="p-3">{{ $token->nis }}</td>
                    <td>{{ $token->kelas_id }}</td>
                    <td>{{ ucfirst($token->status) }}</td>
                    <td>{{ $token->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                    <td class="print:hidden">
                        @if ($token->status === 'tersedia')
                            <form method="POST" action="{{ route('pilketos.token.replace', $token) }}" onsubmit="return confirm('Ganti token ini? Token lama langsung tidak berlaku.')">
                                @csrf
                                <button class="font-bold text-amber-700">Ganti token hilang</button>
                            </form>
                        @else
                            <span class="text-slate-500">Tidak dapat diganti</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td class="p-4 text-slate-500" colspan="5">Belum ada token pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>
    @if ($tokens instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">{{ $tokens->links() }}</div>
    @endif
</main>
</body>
</html>
