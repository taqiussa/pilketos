<?php

use App\Models\Kelas;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Services\PilketosTokenClassReportService;
use App\Services\PilketosTokenService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.pilketos')] class extends Component {
    public $periodes;
    public $kelas;
    public ?int $periodeId = null;
    public ?int $kelasId = null;
    public ?string $notice = null;

    public function mount(): void
    {
        $this->periodes = PilketosPeriode::query()->latest('tahun')->get();
        $this->kelas = Kelas::query()->orderBy('tingkat')->orderBy('nama')->get();
    }

    public function updatedPeriodeId(): void
    {
        $this->kelasId = null;
        $this->notice = null;
        $this->resetErrorBag();
    }

    public function updatedKelasId(): void
    {
        $this->notice = null;
        $this->resetErrorBag();
    }

    public function generate(PilketosTokenService $service): void
    {
        $data = $this->validate(['periodeId' => ['required', 'exists:pilketos_periodes,id'], 'kelasId' => ['required', 'exists:kelas,id']]);
        $created = $service->generateForClass(PilketosPeriode::query()->findOrFail($data['periodeId']), $data['kelasId'], auth()->id());
        $this->notice = count($created) > 0 ? count($created).' token baru berhasil dibuat.' : 'Tidak ada token baru yang perlu dibuat.';
    }

    public function replaceToken(int $id, PilketosTokenService $service): void
    {
        try {
            $belongsToCurrentClass = PilketosToken::query()
                ->whereKey($id)
                ->where('periode_id', $this->periodeId)
                ->where('kelas_id', $this->kelasId)
                ->exists();

            if (! $belongsToCurrentClass) {
                throw new RuntimeException('Token tidak ditemukan pada kelas yang dipilih.');
            }

            $service->replaceUnusedToken($id, auth()->id());
            $this->notice = 'Token pengganti berhasil dibuat dan siap dicetak.';
        } catch (RuntimeException $exception) {
            $this->addError('token', $exception->getMessage());
        }
    }

    public function with(PilketosTokenClassReportService $reports): array
    {
        return ['report' => $this->periodeId === null || $this->kelasId === null ? null : $reports->forClass($this->periodeId, $this->kelasId)];
    }
}; ?>

<div>
    <a wire:navigate href="{{ route('guru.dashboard') }}" class="font-bold text-emerald-700">← Dashboard</a>
    <h1 class="mt-4 text-3xl font-black">Generate Token Pemilih</h1>
    <p class="mt-1 text-slate-600">Pilih tahun akademik dan kelas sebelum melihat atau membuat token.</p>
    <form wire:submit="generate" class="mt-6 grid gap-4 rounded-2xl bg-white p-5 shadow md:grid-cols-3">
        <label class="text-sm font-semibold text-slate-700">Tahun Akademik<select wire:model.live="periodeId" class="mt-1 block w-full rounded-xl border-slate-300"><option value="">Pilih Tahun Akademik</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}">{{ $periode->tahun }} — {{ $periode->nama }}</option>@endforeach</select></label>
        <label class="text-sm font-semibold text-slate-700">Kelas<select wire:model.live="kelasId" class="mt-1 block w-full rounded-xl border-slate-300" @disabled($periodeId === null)><option value="">Pilih Kelas</option>@foreach ($kelas as $item)<option value="{{ $item->id }}">{{ $item->tingkat }} {{ $item->nama }}</option>@endforeach</select></label>
        <div class="flex items-end"><button wire:loading.attr="disabled" wire:target="generate" @disabled($periodeId === null || $kelasId === null) class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 font-bold text-white disabled:cursor-not-allowed disabled:bg-slate-300"><span wire:loading.remove wire:target="generate">Generate Token</span><span wire:loading wire:target="generate">Memproses...</span></button></div>
    </form>
    @if ($notice || session('success'))<p class="mt-4 rounded-xl bg-emerald-50 p-3 text-emerald-900">{{ $notice ?: session('success') }}</p>@endif
    <x-input-error :messages="$errors->get('token')" class="mt-3" />
    @if ($report === null)
        <section class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">Silakan pilih tahun akademik dan kelas untuk menampilkan data token siswa.</section>
    @else
        @php($total = count($report['rows'])) @php($available = collect($report['rows'])->where('status', 'tersedia')->count()) @php($unmade = collect($report['rows'])->where('status', 'belum_dibuat')->count()) @php($used = collect($report['rows'])->where('status', 'digunakan')->count()) @php($hasTokens = collect($report['rows'])->contains(fn (array $row): bool => $row['token_id'] !== null))
        <section class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">@foreach (['Total Siswa' => $total, 'Token Tersedia' => $available, 'Belum Dibuat' => $unmade, 'Sudah Digunakan' => $used] as $label => $value)<article class="rounded-2xl bg-white p-5 shadow"><p class="text-sm text-slate-600">{{ $label }}</p><p class="mt-1 text-3xl font-black">{{ $value }}</p></article>@endforeach</section>
        @if ($hasTokens)<div class="mt-5 flex flex-wrap gap-3"><a href="{{ route('pilketos.token.print', ['periode' => $periodeId, 'kelas' => $kelasId]) }}" target="_blank" rel="noopener" class="rounded-xl bg-slate-800 px-4 py-2 font-bold text-white">Print Token</a><a href="{{ route('pilketos.token.export', ['periode' => $periodeId, 'kelas' => $kelasId]) }}" class="rounded-xl bg-emerald-700 px-4 py-2 font-bold text-white">Export Excel</a></div>@endif
        <section class="mt-5 overflow-x-auto rounded-2xl bg-white shadow"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50"><tr><th class="p-3">No</th><th class="p-3">Nama Siswa</th><th class="p-3">Kelas</th><th class="p-3">Token</th><th class="p-3">Status</th><th class="p-3">Aksi</th></tr></thead><tbody>@forelse ($report['rows'] as $row)<tr class="border-t"><td class="p-3">{{ $row['no'] }}</td><td class="p-3 font-medium">{{ $row['name'] }}</td><td class="p-3">{{ $row['kelas'] }}</td><td class="p-3 font-mono font-bold">{{ $row['token_label'] }}</td><td class="p-3">{{ match ($row['status']) {'tersedia' => 'Tersedia', 'digunakan' => 'Sudah digunakan', 'dibatalkan' => 'Dibatalkan', default => 'Belum dibuat'} }}</td><td class="p-3">@if ($row['can_replace'])<button wire:click="replaceToken({{ $row['token_id'] }})" wire:confirm="Ganti token ini? Token sebelumnya langsung tidak berlaku." wire:loading.attr="disabled" class="font-bold text-amber-700">Ganti</button>@else<span class="text-slate-400">-</span>@endif</td></tr>@empty<tr><td colspan="6" class="p-5 text-center text-slate-500">Tidak ada siswa pada kelas ini.</td></tr>@endforelse</tbody></table></section>
    @endif
</div>
