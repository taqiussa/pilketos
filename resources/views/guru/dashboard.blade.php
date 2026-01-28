<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Pemilihan Ketua OSIS</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-indigo-600">OSIS Dashboard</h1>
                <p class="text-sm text-gray-600">Tahun Ajaran {{ $tahunAjaran }}</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-700">{{ Auth::user()->name }}</span>
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
        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Siswa -->
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Siswa</p>
                        <p class="text-4xl font-bold text-indigo-600 mt-2">{{ $siswaTotal }}</p>
                    </div>
                    <div class="bg-indigo-100 p-4 rounded-lg">
                        <svg class="w-8 h-8 text-indigo-600 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8a4 4 0 0 0-4 4 2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-3Zm6.82-3.096a5.51 5.51 0 0 0-2.797-6.293 3.5 3.5 0 1 1 2.796 6.292ZM19.5 18h.5a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-1.1a5.503 5.503 0 0 1-.471.762A5.998 5.998 0 0 1 19.5 18ZM4 7.5a3.5 3.5 0 0 1 5.477-2.889 5.5 5.5 0 0 0-2.796 6.293A3.501 3.501 0 0 1 4 7.5ZM7.1 12H6a4 4 0 0 0-4 4 2 2 0 0 0 2 2h.5a5.998 5.998 0 0 1 3.071-5.238A5.505 5.505 0 0 1 7.1 12Z"
                                clip-rule="evenodd" />
                        </svg>

                    </div>
                </div>
            </div>

            <!-- Siswa Putra -->
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-gray-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Siswa Putra</p>
                        <p class="text-4xl font-bold text-gray-600 mt-2">{{ $siswaPutra }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ round(($siswaPutra / $siswaTotal) * 100, 1) }}%</p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <svg class="w-8 h-8 text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.4472 2.10557c-.2815-.14076-.6129-.14076-.8944 0L5.90482 4.92956l.37762.11119c.01131.00333.02257.00687.03376.0106L12 6.94594l5.6808-1.89361.3927-.13363-5.6263-2.81313ZM5 10V6.74803l.70053.20628L7 7.38747V10c0 .5523-.44772 1-1 1s-1-.4477-1-1Zm3-1c0-.42413.06601-.83285.18832-1.21643l3.49538 1.16514c.2053.06842.4272.06842.6325 0l3.4955-1.16514C15.934 8.16715 16 8.57587 16 9c0 2.2091-1.7909 4-4 4-2.20914 0-4-1.7909-4-4Z" />
                            <path
                                d="M14.2996 13.2767c.2332-.2289.5636-.3294.8847-.2692C17.379 13.4191 19 15.4884 19 17.6488v2.1525c0 1.2289-1.0315 2.1428-2.2 2.1428H7.2c-1.16849 0-2.2-.9139-2.2-2.1428v-2.1525c0-2.1409 1.59079-4.1893 3.75163-4.6288.32214-.0655.65589.0315.89274.2595l2.34883 2.2606 2.3064-2.2634Z" />
                        </svg>

                    </div>
                </div>
            </div>

            <!-- Siswa Putri -->
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-pink-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Siswa Putri</p>
                        <p class="text-4xl font-bold text-pink-600 mt-2">{{ $siswaPutri }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ round(($siswaPutri / $siswaTotal) * 100, 1) }}%</p>
                    </div>
                    <div class="bg-pink-100 p-4 rounded-lg">
                        <svg class="w-8 h-8 text-pink-600 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.6144 7.19994c.3479.48981.5999 1.15357.5999 1.80006 0 1.6569-1.3432 3-3 3-1.6569 0-3.00004-1.3431-3.00004-3 0-.67539.22319-1.29865.59983-1.80006M6.21426 6v4m0-4 6.00004-3 6 3-6 2-2.40021-.80006M6.21426 6l3.59983 1.19994M6.21426 19.8013v-2.1525c0-1.6825 1.27251-3.3075 2.95093-3.6488l3.04911 2.9345 3-2.9441c1.7026.3193 3 1.9596 3 3.6584v2.1525c0 .6312-.5373 1.1429-1.2 1.1429H7.41426c-.66274 0-1.2-.5117-1.2-1.1429Z" />
                        </svg>

                    </div>
                </div>
            </div>

            <!-- Progress Voting -->
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Progress Voting</p>
                        <p class="text-4xl font-bold text-amber-600 mt-2">
                            {{ round(($siswaSudahVoting / $siswaTotal) * 100, 0) }}%</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $siswaSudahVoting }} dari {{ $siswaTotal }}</p>
                    </div>
                    <div class="bg-amber-100 p-4 rounded-lg">
                        <svg class="w-8 h-8 text-amber-700 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3M3.22302 14C4.13247 18.008 7.71683 21 12 21c4.9706 0 9-4.0294 9-9 0-4.97056-4.0294-9-9-9-3.72916 0-6.92858 2.26806-8.29409 5.5M7 9H3V5" />
                        </svg>

                    </div>
                </div>
            </div>
        </div>

        <!-- Voting Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Sudah Voting -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Siswa yang Sudah Voting</h3>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-5xl font-bold text-green-600">{{ $siswaSudahVoting }}</p>
                        <p class="text-gray-600 mt-2">dari {{ $siswaTotal }} siswa</p>
                    </div>
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Belum Voting -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Siswa yang Belum Voting</h3>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-5xl font-bold text-red-600">{{ $siswaBelumVoting }}</p>
                        <p class="text-gray-600 mt-2">dari {{ $siswaTotal }} siswa</p>
                    </div>
                    <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Guru Sudah Voting -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Guru yang Sudah Voting</h3>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-5xl font-bold text-green-600">{{ $guruSudahVoting ?? 0 }}</p>
                        <p class="text-gray-600 mt-2">dari {{ $guruTotal ?? 0 }} guru</p>
                    </div>
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Guru Belum Voting -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Guru yang Belum Voting</h3>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-5xl font-bold text-red-600">{{ $guruBelumVoting ?? 0 }}</p>
                        <p class="text-gray-600 mt-2">dari {{ $guruTotal ?? 0 }} guru</p>
                    </div>
                    <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Statistics Button -->
        <div class="text-center">
            <a href="{{ route('guru.statistics') }}"
                class="inline-block px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition mr-3">
                Lihat Detail Hasil Voting →
            </a>

            <a href="{{ route('guru.vote') }}"
                class="inline-block px-8 py-3 bg-amber-600 text-white rounded-lg font-semibold hover:bg-amber-700 transition">
                Voting Sekarang →
            </a>
        </div>
    </div>
</body>

</html>
