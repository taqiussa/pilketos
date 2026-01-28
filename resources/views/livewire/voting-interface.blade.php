<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Pemilihan Ketua OSIS</h1>
            <p class="text-gray-600">Pilih calon pemimpin yang Anda dukung</p>
        </div>

        <!-- Flash Message -->
        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <!-- Check NIS Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Verifikasi NIS</h2>

            @if ($hasVoted)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800 mb-4">
                    <p class="font-semibold">Anda telah melakukan voting</p>
                    <p class="text-sm mt-1">Setiap siswa hanya dapat memilih satu calon dan tidak dapat diubah setelah
                        voting.</p>
                    <button wire:click="resetForm"
                        class="w-full px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        Reset
                    </button>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="nis" class="block text-sm font-medium text-gray-700 mb-2">Nomor Induk Siswa
                        (NIS)</label>
                    <div class="flex gap-2">
                        <input type="text" id="nis" wire:model="nis" wire:keydown.enter="checkNis"
                            placeholder="Masukkan NIS Anda"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            @if ($siswaVerified || $hasVoted) disabled @endif>
                        <button wire:click="checkNis" @if ($siswaVerified || $hasVoted) disabled @endif
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                            Cek NIS
                        </button>
                    </div>
                    @error('nis')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Error Message -->
                @if ($errorMessage)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">
                        {{ $errorMessage }}
                    </div>
                @endif

                <!-- Siswa Info -->
                @if ($siswaVerified && $siswa)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1"><span class="font-semibold">Nama:</span>
                            {{ $siswa->user?->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600"><span class="font-semibold">Kelas:</span>
                            {{ $siswa->kelas?->nama ?? 'N/A' }}</p>
                    </div>
                @endif

                <!-- Reset Button -->
                @if ($siswaVerified)
                    <button wire:click="resetForm"
                        class="w-full px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        Gunakan NIS Lain
                    </button>
                @endif
            </div>
        </div>

        <!-- Voting Section -->
        @if ($siswaVerified && !$hasVoted)
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">
                            Pilih Calon Ketua OSIS
                            @if ($siswaJenisKelamin === 'putra')
                                <span class="text-blue-600">(Putra)</span>
                            @else
                                <span class="text-pink-600">(Putri)</span>
                            @endif
                        </h3>
                        <p class="text-gray-600">Klik untuk memilih calon pilihan Anda</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        @forelse ($calonsTersedia as $calon)
                            <div wire:click="selectCalon({{ $calon['id'] }})"
                                class="cursor-pointer rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all transform hover:scale-105 {{ $selectedCalonId === $calon['id'] ? 'ring-4 ring-offset-2' : '' }}"
                                :class="{ 'ring-blue-500': '{{ $siswaJenisKelamin }}'
                                    === 'putra', 'ring-pink-500': '{{ $siswaJenisKelamin }}'
                                    === 'putri' }">
                                <!-- Foto Calon -->
                                <div class="h-80 bg-gray-300 flex items-center justify-center overflow-hidden">
                                    @if ($calon['foto_url'])
                                        <img src="{{ $calon['foto_url'] }}" alt="{{ $calon['nama'] }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div
                                            class="w-full h-full bg-gradient-to-br {{ $siswaJenisKelamin === 'putra' ? 'from-blue-400 to-blue-600' : 'from-pink-400 to-pink-600' }} flex items-center justify-center">
                                            <span
                                                class="text-white text-6xl font-bold">{{ substr($calon['nama'], 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Info Calon -->
                                <div class="p-6 bg-white">
                                    <div class="inline-block px-3 py-1 rounded-full text-sm font-semibold mb-3"
                                        :class="{ 'bg-blue-100 text-blue-800': '{{ $siswaJenisKelamin }}'
                                            === 'putra', 'bg-pink-100 text-pink-800': '{{ $siswaJenisKelamin }}'
                                            === 'putri' }">
                                        No. Urut {{ $calon['nomor_urut'] }}
                                    </div>
                                    <h4 class="text-2xl font-bold text-gray-800 mb-2">{{ $calon['nama'] }}</h4>
                                    @if ($calon['deskripsi'])
                                        <p class="text-gray-600 text-sm leading-relaxed">{{ $calon['deskripsi'] }}</p>
                                    @endif
                                </div>

                                <!-- Check Mark -->
                                @if ($selectedCalonId === $calon['id'])
                                    <div
                                        class="absolute top-4 right-4 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-12">
                                <p class="text-gray-600 text-lg">Tidak ada calon untuk kategori ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Submit Vote Button -->
            @if ($selectedCalonId)
                <div class="flex gap-4 justify-center sticky bottom-0 bg-white py-4 rounded-lg shadow-lg border-t">
                    <button wire:click="resetForm"
                        class="px-8 py-3 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition font-semibold">
                        Batalkan
                    </button>
                    <button wire:click="submitVote"
                        class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold shadow-md hover:shadow-lg">
                        ✓ Konfirmasi Pilihan
                    </button>
                </div>
            @endif
        @endif
    </div>
</div>
