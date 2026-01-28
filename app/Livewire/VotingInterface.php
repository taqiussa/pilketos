<?php

namespace App\Livewire;

use App\Models\Calon;
use App\Models\Siswa;
use App\Models\Vote;
use Livewire\Attributes\Validate;
use Livewire\Component;

class VotingInterface extends Component
{
    #[Validate('required|string|max:30')]
    public string $nis = '';

    public ?Siswa $siswa = null;

    public bool $siswaVerified = false;

    public ?string $errorMessage = null;

    public array $calonsTersedia = [];

    public ?string $siswaJenisKelamin = null;

    public ?int $selectedCalonId = null;

    public bool $hasVoted = false;

    public function mount(): void
    {
        // Tidak perlu load semua calon di mount, akan di-load sesuai jenis kelamin
    }

    public function checkNis(): void
    {
        $this->validate();

        $this->errorMessage = null;
        $this->siswaVerified = false;
        $this->calonsTersedia = [];

        // Check apakah siswa sudah voting
        $existingVote = Vote::where('nis', $this->nis)->first();
        if ($existingVote) {
            $this->hasVoted = true;
            $this->errorMessage = 'Anda sudah melakukan voting. Setiap siswa hanya bisa voting 1 kali.';

            return;
        }

        // Ambil data siswa terbaru berdasarkan created_at dengan relasi biodata
        $siswa = Siswa::where('nis', $this->nis)
            ->latest('created_at')
            ->with(['kelas', 'biodata'])
            ->first();

        if (! $siswa) {
            $this->errorMessage = 'NIS tidak ditemukan dalam sistem.';

            return;
        }

        // Cek jenis kelamin dari biodata
        $jenisKelamin = $siswa->biodata?->jenis_kelamin;

        if (! $jenisKelamin) {
            $this->errorMessage = 'Data jenis kelamin tidak ditemukan. Silakan hubungi admin.';

            return;
        }

        // Map jenis kelamin: P = putri, L = putra
        $siswaJenisKelamin = strtolower($jenisKelamin) === 'p' ? 'putri' : 'putra';

        // Load calon sesuai jenis kelamin siswa
        $this->calonsTersedia = Calon::where('jenis_kelamin', $siswaJenisKelamin)
            ->orderBy('nomor_urut')
            ->get()
            ->toArray();

        $this->siswa = $siswa;
        $this->siswaJenisKelamin = $siswaJenisKelamin;
        $this->siswaVerified = true;
        $this->hasVoted = false;
    }

    public function resetForm(): void
    {
        $this->reset(['nis', 'siswa', 'siswaVerified', 'errorMessage', 'selectedCalonId', 'hasVoted', 'calonsTersedia', 'siswaJenisKelamin']);
    }

    public function selectCalon(int $calonId): void
    {
        $this->selectedCalonId = $calonId;
    }

    public function submitVote(): void
    {
        if (! $this->siswaVerified || ! $this->selectedCalonId) {
            $this->errorMessage = 'Silakan verifikasi NIS dan pilih calon terlebih dahulu.';

            return;
        }

        try {
            Vote::create([
                'nis' => $this->nis,
                'calon_id' => $this->selectedCalonId,
            ]);

            $this->hasVoted = true;
            $this->selectedCalonId = null;
            session()->flash('success', 'Vote Anda telah berhasil disimpan. Terima kasih telah berpartisipasi!');

            // Reset form setelah voting sukses
            $this->reset(['nis', 'siswa', 'siswaVerified', 'errorMessage', 'calonsTersedia', 'siswaJenisKelamin']);
        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan saat menyimpan vote. Silakan coba lagi.';
        }
    }

    public function render()
    {
        return view('livewire.voting-interface');
    }
}
