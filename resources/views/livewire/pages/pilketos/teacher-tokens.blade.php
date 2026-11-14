<?php

use App\Models\PilketosPeriode;
use App\Services\PilketosTeacherTokenReportService;
use App\Services\PilketosTokenService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.pilketos')] class extends Component {
    use WithPagination;

    public ?int $periodeId = null;
    public string $status = '';
    public string $search = '';
    public ?string $notice = null;

    public function mount(): void
    {
        $this->periodeId = PilketosPeriode::query()->latest('tahun')->value('id');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['periodeId', 'status', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function generate(PilketosTokenService $tokens): void
    {
        $this->validate(['periodeId' => ['required', 'exists:pilketos_periodes,id']]);
        $result = $tokens->generateForTeachers(PilketosPeriode::query()->findOrFail($this->periodeId), auth()->id());
        $this->notice = "{$result['created']} token dibuat; {$result['skipped']} guru dilewati karena jenis kelamin tidak valid.";
    }

    public function with(PilketosTeacherTokenReportService $reports): array
    {
        $periodes = PilketosPeriode::query()->latest('tahun')->get();

        if ($this->periodeId === null) {
            return ['periodes' => $periodes, 'teachers' => collect(), 'periode' => null];
        }

        $report = $reports->forPeriod($this->periodeId, $this->status, $this->search);
        $rows = collect($report['rows']);
        $page = $this->getPage();
        $teachers = new LengthAwarePaginator(
            $rows->forPage($page, 25)->values(),
            $rows->count(),
            25,
            $page,
            ['path' => request()->url(), 'pageName' => 'page'],
        );

        return ['periodes' => $periodes, 'teachers' => $teachers, 'periode' => $report['periode']];
    }
}; ?>

<div>
    <a wire:navigate href="{{ route('guru.dashboard') }}" class="font-bold text-emerald-700">← Dashboard</a>
    <h1 class="mt-4 text-3xl font-black">Token Guru</h1>
    <p class="mt-1 text-slate-600">Pilih tahun akademik, lalu kelola dan cetak token guru yang memenuhi syarat.</p>

    <div class="mt-5 grid gap-3 rounded-2xl bg-white p-4 shadow md:grid-cols-2 xl:grid-cols-4">
        <label class="text-sm font-semibold text-slate-700">
            Tahun Akademik
            <select wire:model.live="periodeId" class="mt-1 block w-full rounded-xl border-slate-300">
                <option value="">Pilih tahun akademik</option>
                @foreach ($periodes as $item)
                    <option value="{{ $item->id }}">{{ $item->tahun }}{{ $item->nama ? ' — '.$item->nama : '' }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-semibold text-slate-700">
            Status Token
            <select wire:model.live="status" class="mt-1 block w-full rounded-xl border-slate-300">
                <option value="">Semua status</option>
                <option value="belum_dibuat">Belum dibuat</option>
                <option value="tersedia">Tersedia</option>
                <option value="digunakan">Digunakan</option>
                <option value="dibatalkan">Dibatalkan</option>
            </select>
        </label>
        <label class="text-sm font-semibold text-slate-700">
            Cari Guru
            <input wire:model.live.debounce.300ms="search" class="mt-1 block w-full rounded-xl border-slate-300" placeholder="Nama atau username">
        </label>
        <div class="flex items-end">
            <button wire:click="generate" wire:loading.attr="disabled" wire:target="generate" @disabled($periodeId === null) class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 font-bold text-white disabled:cursor-not-allowed disabled:bg-slate-300">
                <span wire:loading.remove wire:target="generate">Generate Token</span>
                <span wire:loading wire:target="generate">Memproses...</span>
            </button>
        </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-3">
        <a href="{{ $periodeId === null ? '#' : route('pilketos.token-guru.print', ['periode' => $periodeId, 'status' => $status ?: null, 'search' => $search ?: null]) }}" target="_blank" rel="noopener" @class(['pointer-events-none cursor-not-allowed bg-slate-300 text-slate-500' => $periodeId === null, 'bg-slate-800 text-white hover:bg-slate-700' => $periodeId !== null, 'inline-flex min-h-11 items-center justify-center rounded-xl px-4 py-2 font-bold']) aria-disabled="{{ $periodeId === null ? 'true' : 'false' }}">Print Token Guru</a>
        <a href="{{ $periodeId === null ? '#' : route('pilketos.token-guru.export', ['periode' => $periodeId, 'status' => $status ?: null, 'search' => $search ?: null]) }}" @class(['pointer-events-none cursor-not-allowed bg-slate-300 text-slate-500' => $periodeId === null, 'bg-emerald-700 text-white hover:bg-emerald-800' => $periodeId !== null, 'inline-flex min-h-11 items-center justify-center rounded-xl px-4 py-2 font-bold']) aria-disabled="{{ $periodeId === null ? 'true' : 'false' }}">Download Excel</a>
    </div>

    @if ($notice)
        <p class="mt-4 rounded-xl bg-amber-50 p-3 text-amber-900">{{ $notice }}</p>
    @endif

    <div wire:loading.delay><x-skeleton variant="table" /></div>
    <section wire:loading.remove class="mt-5 overflow-x-auto rounded-2xl bg-white shadow">
        <table class="min-w-full text-left text-sm">
            <thead class="sticky top-0 bg-slate-50">
                <tr><th class="p-3">No</th><th class="p-3">Nama</th><th class="p-3">Username</th><th class="p-3">Role</th><th class="p-3">Token</th><th class="p-3">Status</th></tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    <tr class="border-t" wire:key="teacher-{{ $teacher['no'] }}-{{ $teacher['username'] }}">
                        <td class="p-3">{{ $teacher['no'] }}</td><td class="p-3 font-medium">{{ $teacher['name'] }}</td><td class="p-3">{{ $teacher['username'] }}</td><td class="p-3">{{ $teacher['roles'] }}</td><td class="p-3 font-mono font-bold">{{ $teacher['token_label'] }}</td><td class="p-3">{{ PilketosTeacherTokenReportService::statusLabel($teacher['status']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-slate-500">Tidak ada guru eligible untuk filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    {{ $teachers->links() }}
</div>
