<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting - Guru</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-indigo-600">Voting Guru</h1>
                <p class="text-sm text-gray-600">Pilih calon yang Anda dukung</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('guru.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    ← Kembali
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

    <div class="max-w-6xl mx-auto px-4 py-8">
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        @if (!empty($hasVotedGuru) && $hasVotedGuru)
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
                <p class="font-semibold">Anda sudah melakukan voting.</p>
                <p class="text-sm mt-1">Setiap guru hanya dapat memilih satu kali dan tidak dapat mengubah pilihan.</p>
            </div>
        @endif

        @if (empty($hasVotedGuru))
            <form action="{{ route('guru.vote.post') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Pilih Calon Ketua OSIS</h2>
                    <p class="text-gray-600">Klik kartu calon untuk memilih. Guru bebas memilih putra atau putri.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @forelse($calons as $calon)
                        <label
                            class="relative cursor-pointer rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all transform hover:scale-105">
                            <input type="radio" name="calon_id" value="{{ $calon->id }}" class="peer sr-only"
                                required>

                            <!-- Foto -->
                            <div
                                class="h-80 bg-gray-300 flex items-center justify-center overflow-hidden transition-all peer-checked:scale-105 peer-checked:ring-4 peer-checked:ring-amber-500">
                                @if ($calon->foto_url)
                                    <img src="{{ $calon->foto_url }}" alt="{{ $calon->nama }}"
                                        class="w-full h-full object-cover object-top">
                                @else
                                    <div
                                        class="w-full h-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center">
                                        <span
                                            class="text-white text-8xl font-bold">{{ substr($calon->nama, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="p-6 bg-white peer-checked:bg-amber-50">
                                <div
                                    class="inline-block px-3 py-1 rounded-full text-sm font-semibold mb-3 bg-gray-100 text-gray-800">
                                    No. Urut {{ $calon->nomor_urut }}
                                </div>
                                <h4 class="text-2xl font-bold text-gray-800 mb-2">{{ $calon->nama }}</h4>
                                @if ($calon->deskripsi)
                                    <p class="text-gray-600 text-sm leading-relaxed">{{ $calon->deskripsi }}</p>
                                @endif
                            </div>

                            <div
                                class="absolute top-4 right-4 hidden peer-checked:flex items-center justify-center w-10 h-10 bg-amber-600 text-white rounded-full">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-3 text-center py-12">
                            <p class="text-gray-600 text-lg">Tidak ada calon.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 text-center">
                    <button type="submit"
                        class="px-6 py-3 bg-amber-600 text-white rounded-lg font-semibold hover:bg-amber-700 transition">Kirim
                        Vote</button>
                </div>
            </form>
        @endif
    </div>
</body>

</html>
