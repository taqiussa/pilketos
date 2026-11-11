<?php

use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Services\PilketosStudentDirectory;
use App\Services\PilketosTokenService;
use App\Services\PilketosVotingService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public ?PilketosPeriode $periode = null;
    public string $token = '';
    public function mount(): void { $this->periode = PilketosPeriode::query()->where('status', 'aktif')->latest('id')->first(); }
    public function verify(PilketosTokenService $tokens, PilketosStudentDirectory $students, PilketosVotingService $voting): void {
        $this->validate(['token' => ['required', 'string', 'size:6']]); $key = 'pilketos-failed-token:'.request()->ip().'|'.Session::getId();
        if (RateLimiter::tooManyAttempts($key, 5)) { $this->addError('token','Coba kembali beberapa saat lagi.'); return; }
        $plain=$tokens->normalize($this->token); $token=PilketosToken::query()->with('periode')->where('token_hash',$tokens->hash($plain))->first();
        if (! $tokens->isValidFormat($plain) || ! $token || $token->status !== 'tersedia' || ! $voting->isOpen($token->periode)) { RateLimiter::hit($key,120); $this->addError('token','Token tidak dapat diverifikasi.'); return; }
        try { $student=$students->eligibleStudent((int)$token->nis,$token->periode->tahun); } catch (Throwable) { RateLimiter::hit($key,120); $this->addError('token','Token tidak dapat diverifikasi.'); return; }
        RateLimiter::clear($key); Session::regenerate(); Session::put('pilketos.vote',['token_id'=>$token->id,'kategori'=>$student['kategori'],'nonce'=>str()->random(40)]); $this->redirectRoute('pilketos.vote', navigate: true);
    }
}; ?>
<div><div wire:loading.delay><x-skeleton variant="candidate-card" :cards="2" /></div><section wire:loading.remove class="rounded-3xl bg-white p-6 shadow-2xl sm:p-10"><p class="text-sm font-bold uppercase tracking-widest text-emerald-700">SMP Al Musyaffa</p><h1 class="mt-2 text-3xl font-black">{{ $periode?->judul_landing ?: ($periode?->nama ?: 'Pemilihan Ketua OSIS') }}</h1><p class="mt-3 text-slate-600">Tahun akademik {{ $periode?->tahun ?? 'belum dibuka' }}</p><form wire:submit="verify" class="mt-6 max-w-md rounded-2xl bg-slate-100 p-5"><label class="font-bold">Token voting</label><input wire:model="token" maxlength="6" class="mt-3 w-full rounded-xl border-slate-300 text-center text-2xl font-black uppercase" required><x-input-error :messages="$errors->get('token')" class="mt-2"/><button wire:loading.attr="disabled" class="mt-4 w-full rounded-xl bg-emerald-700 px-4 py-3 font-bold text-white" @disabled(!$periode)>Mulai memilih</button></form></section></div>
