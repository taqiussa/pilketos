<?php

use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Services\PilketosTeacherDirectory;
use App\Services\PilketosTokenService;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.pilketos')] class extends Component {
    use WithPagination;

    public ?int $periodeId = null;
    public string $status = '';
    public string $search = '';
    public ?string $notice = null;

    public function mount(): void { $this->periodeId = PilketosPeriode::query()->latest('tahun')->value('id'); }
    public function updated($property): void { if (in_array($property, ['periodeId', 'status', 'search'], true)) $this->resetPage(); }
    public function generate(PilketosTokenService $tokens): void { $this->validate(['periodeId' => ['required', 'exists:pilketos_periodes,id']]); $result = $tokens->generateForTeachers(PilketosPeriode::query()->findOrFail($this->periodeId), auth()->id()); $this->notice = "{$result['created']} token dibuat; {$result['skipped']} guru dilewati karena jenis kelamin tidak valid."; }
    public function with(PilketosTeacherDirectory $teachers): array { $periode = $this->periodeId ? PilketosPeriode::query()->find($this->periodeId) : null; $query = $teachers->eligibleQuery()->with('roles:id,name')->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('username', 'like', "%{$this->search}%"))); if ($periode) $query->leftJoin('pilketos_tokens as pt', fn ($join) => $join->on('pt.user_id','=','users.id')->where('pt.periode_id','=',$periode->id))->select('users.*','pt.id as token_id','pt.status as token_status','pt.token_encrypted')->when($this->status, fn ($q) => $this->status === 'belum_dibuat' ? $q->whereNull('pt.id') : $q->where('pt.status',$this->status)); return ['periodes' => PilketosPeriode::query()->latest('tahun')->get(), 'teachers' => $periode ? $query->orderBy('users.name')->paginate(25) : collect(), 'periode' => $periode]; }
}; ?>
<div><a wire:navigate href="{{ route('guru.dashboard') }}" class="font-bold text-emerald-700">← Dashboard</a><h1 class="mt-4 text-3xl font-black">Token Guru</h1><div class="mt-5 grid gap-3 rounded-2xl bg-white p-4 shadow md:grid-cols-4"><select wire:model.live="periodeId"><option value="">Pilih tahun</option>@foreach($periodes as $item)<option value="{{ $item->id }}">{{ $item->tahun }}</option>@endforeach</select><select wire:model.live="status"><option value="">Semua status</option><option value="belum_dibuat">Belum dibuat</option><option value="tersedia">Tersedia</option><option value="digunakan">Digunakan</option></select><input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau username"><button wire:click="generate" wire:loading.attr="disabled" class="rounded-xl bg-emerald-700 px-4 py-2 font-bold text-white">Generate token</button></div>@if($notice)<p class="mt-4 rounded-xl bg-amber-50 p-3 text-amber-900">{{ $notice }}</p>@endif<div wire:loading.delay><x-skeleton variant="table" /></div><section wire:loading.remove class="mt-5 overflow-x-auto rounded-2xl bg-white shadow"><table class="min-w-full text-left text-sm"><thead class="sticky top-0 bg-slate-50"><tr><th class="p-3">Nama</th><th class="p-3">Username</th><th class="p-3">Role</th><th class="p-3">Gender</th><th class="p-3">Token</th><th class="p-3">Status</th></tr></thead><tbody>@forelse($teachers as $teacher)<tr class="border-t" wire:key="teacher-{{ $teacher->id }}"><td class="p-3">{{ $teacher->name }}</td><td class="p-3">{{ $teacher->username }}</td><td class="p-3">{{ $teacher->roles->pluck('name')->join(', ') ?: '-' }}</td><td class="p-3">{{ $teacher->jenis_kelamin }}</td><td class="p-3 font-mono">{{ $teacher->token_encrypted ?? '-' }}</td><td class="p-3">{{ $teacher->token_status ?? 'belum_dibuat' }}</td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-slate-500">Tidak ada guru eligible untuk filter ini.</td></tr>@endforelse</tbody></table></section>@if($teachers instanceof \Illuminate\Contracts\Pagination\Paginator){{ $teachers->links() }}@endif</div>
