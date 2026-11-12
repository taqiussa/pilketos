<?php

use App\Models\PilketosPasangan;
use App\Models\PilketosToken;
use App\Services\PilketosCandidateProfileService;
use App\Services\PilketosVotingService;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public array $candidates = [];
    public ?string $tahun = null;
    public ?string $judul = null;

    public function mount(PilketosVotingService $voting, PilketosCandidateProfileService $profiles): void
    {
        $session = Session::get('pilketos.vote', []);
        $token = isset($session['token_id']) ? PilketosToken::query()->with('periode')->find($session['token_id']) : null;

        if (! $token || $token->status !== 'tersedia' || ! $voting->isOpen($token->periode)) {
            Session::forget(['pilketos.vote', 'pilketos.confirm']);
            $this->redirectRoute('pilketos.enter-token', navigate: true);

            return;
        }

        $pasangan = PilketosPasangan::query()->where('periode_id', $token->periode_id)->where('kategori', $session['kategori'])->where('is_active', true)->orderBy('nomor_urut')->get();
        $identity = $profiles->forPasangan($pasangan, $token->periode->tahun);
        $this->candidates = $pasangan->map(fn (PilketosPasangan $item): array => ['pasangan' => $item, 'profile' => $identity[$item->id]])->all();
        $this->tahun = $token->periode->tahun;
        $this->judul = $token->periode->judul_landing ?: $token->periode->nama;
    }

    public function select(int $id): void
    {
        $vote = Session::get('pilketos.vote');
        $token = is_array($vote) ? PilketosToken::query()->with('periode')->find($vote['token_id']) : null;
        $valid = $token && $token->status === 'tersedia' && PilketosPasangan::query()->whereKey($id)->where('periode_id', $token->periode_id)->where('kategori', $vote['kategori'])->where('is_active', true)->exists();

        if (! $valid) {
            Session::forget(['pilketos.vote', 'pilketos.confirm']);
            $this->redirectRoute('pilketos.enter-token', navigate: true);

            return;
        }

        Session::put('pilketos.confirm', $id);
        $this->redirectRoute('pilketos.confirm', navigate: true);
    }
}; ?>

<div class="min-h-screen bg-slate-50 px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
    <header class="mx-auto max-w-7xl rounded-3xl bg-slate-900 px-6 py-7 text-white shadow-xl sm:px-9"><p class="text-sm font-bold tracking-[.16em] text-emerald-200">SMP AL MUSYAFFA · {{ $tahun }}</p><h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Silakan Pilih Pasangan Calon</h1><p class="mt-3 max-w-2xl text-slate-300">Pelajari profil, visi, dan misi setiap pasangan sebelum menentukan pilihan.</p></header>
    <section class="mx-auto mt-7 max-w-7xl"><div class="mb-5 flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm font-bold text-emerald-700">{{ $judul }}</p><p class="mt-1 text-sm text-slate-600">Pilihan hanya akan direkam setelah Anda mengonfirmasi.</p></div><a wire:navigate href="{{ route('pilketos.enter-token') }}" class="text-sm font-bold text-slate-600 hover:text-emerald-700">Batalkan sesi</a></div>
        <div class="grid gap-6 lg:grid-cols-2">@forelse ($candidates as $candidate)<x-pilketos.candidate-profile :pasangan="$candidate['pasangan']" :profile="$candidate['profile']" wire:key="candidate-{{ $candidate['pasangan']->id }}"><button wire:click="select({{ $candidate['pasangan']->id }})" wire:loading.attr="disabled" class="mt-7 inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Pilih Pasangan Ini</button></x-pilketos.candidate-profile>@empty<div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600 lg:col-span-2">Belum ada pasangan calon aktif untuk kategori ini.</div>@endforelse</div>
    </section>
</div>
