<?php

use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Services\PilketosCandidateService;
use App\Services\PilketosPhotoUploadService;
use App\Services\PilketosStudentDirectory;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.pilketos')] class extends Component {
    use WithPagination;

    public $periodes;
    public ?int $periodeId = null;
    public string $kategori = 'L';
    public ?int $ketuaNis = null;
    public ?int $wakilNis = null;
    public int $nomorUrut = 1;
    public string $visi = '';
    public string $misi = '';
    public string $fotoPasangan = '';
    public array $students = [];

    public function mount(PilketosStudentDirectory $directory): void
    {
        $this->periodes = PilketosPeriode::query()->latest('tahun')->get();
        $this->periodeId = $this->periodes->first()?->id;
        $this->loadStudents($directory);
    }

    public function updatedKategori(PilketosStudentDirectory $directory): void
    {
        $this->resetStudentSelections();
        $this->loadStudents($directory);
    }

    public function updatedPeriodeId(PilketosStudentDirectory $directory): void
    {
        $this->resetStudentSelections();
        $this->resetPage();
        $this->loadStudents($directory);
    }

    public function loadStudents(PilketosStudentDirectory $directory): void
    {
        $periode = PilketosPeriode::query()->find($this->periodeId);
        $this->students = $periode === null ? [] : $directory->eligibleStudents($periode->tahun, $this->kategori);
    }

    public function save(PilketosCandidateService $service, PilketosPhotoUploadService $photos): void
    {
        $data = $this->validate([
            'periodeId' => ['required', 'exists:pilketos_periodes,id'],
            'kategori' => ['required', 'in:L,P'],
            'nomorUrut' => ['required', 'integer', 'min:1'],
            'ketuaNis' => ['required', 'integer', 'different:wakilNis'],
            'wakilNis' => ['required', 'integer'],
            'visi' => ['nullable', 'string'],
            'misi' => ['nullable', 'string'],
            'fotoPasangan' => ['nullable', 'string'],
        ]);

        $intent = $data['fotoPasangan'] ? $photos->verifyFor(auth()->user(), $data['fotoPasangan']) : null;
        $service->create([
            'periode_id' => $data['periodeId'],
            'kategori' => $data['kategori'],
            'nomor_urut' => $data['nomorUrut'],
            'ketua_nis' => $data['ketuaNis'],
            'wakil_nis' => $data['wakilNis'],
            'visi' => $data['visi'],
            'misi' => $data['misi'],
            'foto_pasangan' => $data['fotoPasangan'] ?: null,
        ]);

        if ($intent !== null) {
            $photos->consume($intent);
        }

        $this->reset(['ketuaNis', 'wakilNis', 'visi', 'misi', 'fotoPasangan']);
        $this->dispatch('candidate-saved');
    }

    public function remove(int $id): void
    {
        $pasangan = PilketosPasangan::query()->findOrFail($id);

        if ($pasangan->suara()->exists()) {
            $this->addError('candidate', 'Pasangan yang sudah memiliki suara tidak dapat dihapus.');

            return;
        }

        $pasangan->delete();
    }

    public function with(): array
    {
        return [
            'pasangan' => PilketosPasangan::query()
                ->leftJoin('pilketos_periodes', 'pilketos_periodes.id', '=', 'pilketos_pasangan.periode_id')
                ->leftJoin('user_siswa as ketua_siswa', 'ketua_siswa.nis', '=', 'pilketos_pasangan.ketua_nis')
                ->leftJoin('siswas as ketua_riwayat', function ($join): void {
                    $join->on('ketua_riwayat.nis', '=', 'pilketos_pasangan.ketua_nis')
                        ->on('ketua_riwayat.tahun', '=', 'pilketos_periodes.tahun');
                })
                ->leftJoin('kelas as ketua_kelas', 'ketua_kelas.id', '=', 'ketua_riwayat.kelas_id')
                ->leftJoin('user_siswa as wakil_siswa', 'wakil_siswa.nis', '=', 'pilketos_pasangan.wakil_nis')
                ->leftJoin('siswas as wakil_riwayat', function ($join): void {
                    $join->on('wakil_riwayat.nis', '=', 'pilketos_pasangan.wakil_nis')
                        ->on('wakil_riwayat.tahun', '=', 'pilketos_periodes.tahun');
                })
                ->leftJoin('kelas as wakil_kelas', 'wakil_kelas.id', '=', 'wakil_riwayat.kelas_id')
                ->where('pilketos_pasangan.periode_id', $this->periodeId)
                ->orderBy('pilketos_pasangan.kategori')
                ->orderBy('pilketos_pasangan.nomor_urut')
                ->select([
                    'pilketos_pasangan.*',
                    'ketua_siswa.name as ketua_name',
                    'ketua_kelas.tingkat as ketua_tingkat',
                    'ketua_kelas.nama as ketua_kelas_name',
                    'wakil_siswa.name as wakil_name',
                    'wakil_kelas.tingkat as wakil_tingkat',
                    'wakil_kelas.nama as wakil_kelas_name',
                ])
                ->paginate(25),
        ];
    }

    private function resetStudentSelections(): void
    {
        $this->ketuaNis = null;
        $this->wakilNis = null;
    }
}; ?>

<div
    x-data="{
        uploading: false,
        photoStatus: '',
        photoError: '',
        previewUrl: '',
        async uploadPhoto(event) {
            const file = event.target.files[0];
            this.photoError = '';
            this.photoStatus = '';
            if (! file) return;
            if (! ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.photoError = 'Gunakan foto JPG, PNG, atau WebP.';
                event.target.value = '';
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                this.photoError = 'Ukuran foto maksimal 10 MB.';
                event.target.value = '';
                return;
            }
            this.uploading = true;
            this.photoStatus = 'Menyiapkan foto...';
            try {
                const blob = await this.toWebp(file);
                this.previewUrl = URL.createObjectURL(blob);
                this.photoStatus = 'Mempersiapkan upload...';
                const response = await fetch(@js(route('pilketos.kandidat.photo.presign')), {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token())},
                    body: JSON.stringify({content_type: 'image/webp', size: blob.size}),
                });
                const presign = await response.json();
                if (! response.ok) throw new Error(Object.values(presign.errors ?? {}).flat().join(' ') || presign.message || 'Upload tidak dapat dipersiapkan.');
                this.photoStatus = 'Mengunggah foto...';
                const upload = await fetch(presign.upload_url, {method: 'PUT', headers: {'Content-Type': presign.content_type}, body: blob});
                if (! upload.ok) throw new Error('Foto tidak dapat diunggah ke penyimpanan.');
                await $wire.set('fotoPasangan', presign.object_key);
                this.photoStatus = 'Foto siap disimpan.';
            } catch (error) {
                this.photoError = error.message || 'Upload foto gagal.';
                this.previewUrl = '';
                await $wire.set('fotoPasangan', '');
                event.target.value = '';
            } finally {
                this.uploading = false;
            }
        },
        async toWebp(file) {
            if (file.type === 'image/webp') return file;
            const image = await createImageBitmap(file);
            const ratio = Math.min(1, 1600 / Math.max(image.width, image.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(image.width * ratio);
            canvas.height = Math.round(image.height * ratio);
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
            return await new Promise((resolve, reject) => canvas.toBlob((blob) => blob ? resolve(blob) : reject(new Error('Foto tidak dapat diproses.')), 'image/webp', 0.9));
        },
        resetPhoto() {
            this.uploading = false;
            this.photoStatus = '';
            this.photoError = '';
            this.previewUrl = '';
            this.$refs.photoInput.value = '';
        },
    }"
    x-on:candidate-saved.window="resetPhoto()"
>
    <a wire:navigate href="{{ route('guru.dashboard') }}" class="font-bold text-emerald-700">← Dashboard</a>
    <h1 class="mt-4 text-3xl font-black">Pasangan kandidat</h1>

    <form wire:submit="save" class="mt-6 grid gap-4 rounded-2xl bg-white p-5 shadow md:grid-cols-2">
        <label class="text-sm font-semibold text-slate-700">Periode<select wire:model.live="periodeId" class="mt-1 block w-full rounded-xl border-slate-300"><option value="">Pilih periode</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}">{{ $periode->tahun }} — {{ $periode->nama }}</option>@endforeach</select></label>
        <label class="text-sm font-semibold text-slate-700">Kategori<select wire:model.live="kategori" class="mt-1 block w-full rounded-xl border-slate-300"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></label>
        <label class="text-sm font-semibold text-slate-700">Nomor urut<input wire:model="nomorUrut" type="number" min="1" class="mt-1 block w-full rounded-xl border-slate-300"></label>
        <div class="hidden md:block"></div>
        <x-pilketos.searchable-select wire:key="ketua-{{ $periodeId }}-{{ $kategori }}" wire:model.live="ketuaNis" label="Ketua" placeholder="Pilih ketua" :options="collect($students)->map(fn (array $student): array => ['value' => $student['nis'], 'label' => $student['name'].' — '.$student['kelas'].' — '.$student['nis']])->all()" />
        <x-pilketos.searchable-select wire:key="wakil-{{ $periodeId }}-{{ $kategori }}" wire:model.live="wakilNis" label="Wakil" placeholder="Pilih wakil" :options="collect($students)->reject(fn (array $student): bool => $student['nis'] === $ketuaNis)->map(fn (array $student): array => ['value' => $student['nis'], 'label' => $student['name'].' — '.$student['kelas'].' — '.$student['nis']])->all()" />
        <label class="text-sm font-semibold text-slate-700">Visi<textarea wire:model="visi" class="mt-1 block w-full rounded-xl border-slate-300"></textarea></label>
        <label class="text-sm font-semibold text-slate-700">Misi<textarea wire:model="misi" class="mt-1 block w-full rounded-xl border-slate-300"></textarea></label>
        <div class="md:col-span-2">
            <label for="foto-pasangan" class="text-sm font-semibold text-slate-700">Foto pasangan <span class="font-normal text-slate-500">(opsional)</span></label>
            <input x-ref="photoInput" id="foto-pasangan" type="file" accept="image/jpeg,image/png,image/webp" @change="uploadPhoto($event)" :disabled="uploading" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:font-semibold file:text-emerald-800">
            <p class="mt-2 text-xs text-slate-500">JPG, PNG, atau WebP. File dikonversi ke WebP dan diunggah langsung ke penyimpanan.</p>
            <p x-show="uploading || photoStatus" x-cloak class="mt-2 text-sm font-medium text-emerald-700" x-text="photoStatus"></p>
            <p x-show="photoError" x-cloak class="mt-2 text-sm font-medium text-red-600" x-text="photoError"></p>
            <img x-show="previewUrl" x-cloak :src="previewUrl" alt="Pratinjau foto pasangan" class="mt-3 h-32 rounded-xl object-cover">
            <x-input-error :messages="$errors->get('fotoPasangan')" class="mt-2" />
        </div>
        <button type="submit" wire:loading.attr="disabled" wire:target="save" :disabled="uploading" class="rounded-xl bg-emerald-700 px-4 py-3 font-bold text-white disabled:cursor-not-allowed disabled:opacity-60 md:col-span-2">Simpan kandidat</button>
    </form>

    <x-input-error :messages="$errors->all()" class="mt-3" />
    <section class="mt-6 overflow-x-auto rounded-2xl bg-white shadow"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th class="p-3">No</th><th class="p-3">Ketua</th><th class="p-3">Wakil</th><th class="p-3">Aksi</th></tr></thead><tbody>@forelse ($pasangan as $candidate)<tr wire:key="candidate-{{ $candidate->id }}" class="border-t"><td class="p-3 font-bold">{{ $candidate->nomor_urut }}</td><td class="p-3"><p class="font-semibold text-slate-900">{{ $candidate->ketua_name ?? 'Identitas tidak ditemukan' }}</p><p class="text-slate-500">{{ trim(($candidate->ketua_tingkat ?? '').' '.($candidate->ketua_kelas_name ?? '')) ?: 'Kelas tidak ditemukan' }} · {{ $candidate->ketua_nis }}</p></td><td class="p-3"><p class="font-semibold text-slate-900">{{ $candidate->wakil_name ?? 'Identitas tidak ditemukan' }}</p><p class="text-slate-500">{{ trim(($candidate->wakil_tingkat ?? '').' '.($candidate->wakil_kelas_name ?? '')) ?: 'Kelas tidak ditemukan' }} · {{ $candidate->wakil_nis }}</p></td><td class="p-3"><button wire:click="remove({{ $candidate->id }})" wire:confirm="Hapus pasangan kandidat ini?" class="font-bold text-red-700">Hapus</button></td></tr>@empty<tr><td colspan="4" class="p-5 text-center text-slate-500">Belum ada kandidat.</td></tr>@endforelse</tbody></table></section>
    <div class="mt-4">{{ $pasangan->links() }}</div>
</div>
