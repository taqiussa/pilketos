<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Voting - Pemilihan Ketua OSIS</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-indigo-600">Statistik Voting</h1>
                <p class="text-sm text-gray-600">Detail Perolehan Suara Per Calon</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('guru.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    ← Kembali ke Dashboard
                </a>
                <form method="POST" action="{{ route('guru.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium">Total Guru</p>
                <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $guruTotal ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium">Guru Sudah Voting</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $guruSudahVoting ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium">Guru Belum Voting</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $guruBelumVoting ?? 0 }}</p>
            </div>
        </div>
        <!-- Calon Putra Section -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-blue-600 mb-6">Calon Ketua OSIS Putra</h2>
            <p class="text-gray-600 mb-4">Total Perolehan Suara: <strong>{{ $totalVotesPutra }}</strong></p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($calonsPutra as $calon)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                        <!-- Foto -->
                        <div
                            class="h-96 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden">
                            @if ($calon->foto_url)
                                <img src="{{ $calon->foto_url }}" alt="{{ $calon->nama }}"
                                    class="w-full h-full object-cover object-top">
                            @else
                                <span class="text-white text-8xl font-bold">{{ substr($calon->nama, 0, 1) }}</span>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="p-6">
                            <div
                                class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold mb-3">
                                No. Urut {{ $calon->nomor_urut }}
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ $calon->nama }}</h3>

                            <!-- Vote Stats -->
                            <div class="bg-blue-50 rounded-lg p-4 mb-4">
                                <p class="text-gray-600 text-sm mb-2">Perolehan Suara</p>
                                <p class="text-4xl font-bold text-blue-600">{{ $calon->votes_count }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ $totalVotesPutra > 0 ? round(($calon->votes_count / $totalVotesPutra) * 100, 1) : 0 }}%
                                    dari suara putra
                                </p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ $totalVotesPutra > 0 ? round(($calon->votes_count / $totalVotesPutra) * 100, 1) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-lg">
                        <p class="text-gray-600">Tidak ada calon putra</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Calon Putri Section -->
        <div>
            <h2 class="text-3xl font-bold text-pink-600 mb-6">Calon Ketua OSIS Putri</h2>
            <p class="text-gray-600 mb-4">Total Perolehan Suara: <strong>{{ $totalVotesPutri }}</strong></p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($calonsPutri as $calon)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                        <!-- Foto -->
                        <div
                            class="h-96 bg-gradient-to-br from-pink-400 to-pink-600 flex items-center justify-center overflow-hidden">
                            @if ($calon->foto_url)
                                <img src="{{ $calon->foto_url }}" alt="{{ $calon->nama }}"
                                    class="w-full h-full object-cover object-top">
                            @else
                                <span class="text-white text-8xl font-bold">{{ substr($calon->nama, 0, 1) }}</span>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="p-6">
                            <div
                                class="inline-block px-3 py-1 bg-pink-100 text-pink-800 rounded-full text-sm font-semibold mb-3">
                                No. Urut {{ $calon->nomor_urut }}
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ $calon->nama }}</h3>

                            <!-- Vote Stats -->
                            <div class="bg-pink-50 rounded-lg p-4 mb-4">
                                <p class="text-gray-600 text-sm mb-2">Perolehan Suara</p>
                                <p class="text-4xl font-bold text-pink-600">{{ $calon->votes_count }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ $totalVotesPutri > 0 ? round(($calon->votes_count / $totalVotesPutri) * 100, 1) : 0 }}%
                                    dari suara putri
                                </p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-pink-600 h-2 rounded-full"
                                    style="width: {{ $totalVotesPutri > 0 ? round(($calon->votes_count / $totalVotesPutri) * 100, 1) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-lg">
                        <p class="text-gray-600">Tidak ada calon putri</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Summary -->
        <div class="mt-12 bg-white rounded-lg shadow p-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan Voting</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <p class="text-gray-600 mb-2">Total Suara Putra</p>
                    <p class="text-4xl font-bold text-blue-600">{{ $totalVotesPutra }}</p>
                </div>
                <div>
                    <p class="text-gray-600 mb-2">Total Suara Putri</p>
                    <p class="text-4xl font-bold text-pink-600">{{ $totalVotesPutri }}</p>
                </div>
            </div>
            <div class="mt-6 pt-6 border-t">
                <p class="text-gray-600 mb-2">Total Seluruh Suara</p>
                <p class="text-5xl font-bold text-indigo-600">{{ $totalVotesPutra + $totalVotesPutri }}</p>
            </div>
        </div>
    </div>
</body>

</html>
