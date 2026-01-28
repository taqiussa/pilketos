<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemilihan Ketua OSIS</title>
    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen bg-gradient-to-br from-indigo-600 to-purple-600">
        <!-- Navigation -->
        <nav class="bg-white/10 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="text-2xl font-bold text-white">OSIS Voting</div>
                <a href="{{ route('osis.voting') }}"
                    class="px-6 py-2 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Mulai Voting
                </a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="max-w-6xl mx-auto px-4 py-16">
            <div class="text-center mb-16">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">
                    Pemilihan Ketua OSIS
                </h1>
                <p class="text-xl text-white/80 mb-4">
                    Wujudkan visi Anda untuk sekolah yang lebih baik
                </p>
                <p class="text-lg text-white/70">
                    Pilih calon pemimpin yang akan mewakili dan membawa sekolah kita ke depan
                </p>
            </div>

            <!-- Calon Putra Section -->
            <div class="mb-20">
                <h2 class="text-4xl font-bold text-white mb-12 text-center">
                    Calon Ketua OSIS Putra
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @php
                        $calonsPutra = \App\Models\Calon::where('jenis_kelamin', 'putra')->orderBy('nomor_urut')->get();
                    @endphp

                    @forelse ($calonsPutra as $calon)
                        <div
                            class="bg-white rounded-xl shadow-2xl overflow-hidden hover:shadow-3xl hover:scale-105 transition transform duration-300">
                            <!-- Foto -->
                            <div
                                class="h-96 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden">
                                @if ($calon->foto_url)
                                    <img src="{{ $calon->foto_url }}" alt="{{ $calon->nama }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <span class="text-white text-8xl font-bold">{{ substr($calon->nama, 0, 1) }}</span>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="p-6">
                                <div
                                    class="inline-block px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold mb-3">
                                    No. Urut {{ $calon->nomor_urut }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $calon->nama }}</h3>
                                @if ($calon->deskripsi)
                                    <p class="text-gray-600 text-sm leading-relaxed">{{ $calon->deskripsi }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-12">
                            <p class="text-white text-lg">Belum ada calon ketua OSIS putra</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Calon Putri Section -->
            <div class="mb-20">
                <h2 class="text-4xl font-bold text-white mb-12 text-center">
                    Calon Ketua OSIS Putri
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @php
                        $calonsPutri = \App\Models\Calon::where('jenis_kelamin', 'putri')->orderBy('nomor_urut')->get();
                    @endphp

                    @forelse ($calonsPutri as $calon)
                        <div
                            class="bg-white rounded-xl shadow-2xl overflow-hidden hover:shadow-3xl hover:scale-105 transition transform duration-300">
                            <!-- Foto -->
                            <div
                                class="h-96 bg-gradient-to-br from-pink-400 to-pink-600 flex items-center justify-center overflow-hidden">
                                @if ($calon->foto_url)
                                    <img src="{{ $calon->foto_url }}" alt="{{ $calon->nama }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <span class="text-white text-8xl font-bold">{{ substr($calon->nama, 0, 1) }}</span>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="p-6">
                                <div
                                    class="inline-block px-4 py-2 bg-pink-100 text-pink-800 rounded-full text-sm font-semibold mb-3">
                                    No. Urut {{ $calon->nomor_urut }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $calon->nama }}</h3>
                                @if ($calon->deskripsi)
                                    <p class="text-gray-600 text-sm leading-relaxed">{{ $calon->deskripsi }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-12">
                            <p class="text-white text-lg">Belum ada calon ketua OSIS putri</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- CTA Section -->
            <div class="text-center py-16">
                <div class="bg-white rounded-2xl p-12 shadow-2xl">
                    <h3 class="text-3xl font-bold text-gray-800 mb-4">
                        Siap Untuk Memberikan Suara Anda?
                    </h3>
                    <p class="text-gray-600 mb-8 text-lg">
                        Setiap suara Anda sangat berharga. Mari bersama memilih pemimpin yang terbaik untuk sekolah
                        kita.
                    </p>
                    <a href="{{ route('osis.voting') }}"
                        class="inline-block px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-bold text-lg hover:shadow-lg transition transform hover:scale-105">
                        Mulai Voting Sekarang
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white/10 backdrop-blur-md border-t border-white/20 mt-16">
            <div class="max-w-6xl mx-auto px-4 py-8 text-center text-white/70">
                <p>&copy; {{ date('Y') }} Pemilihan Ketua OSIS. Semua hak dilindungi.</p>
            </div>
        </footer>
    </div>
</body>

</html>
