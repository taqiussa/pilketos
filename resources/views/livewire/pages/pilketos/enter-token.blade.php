<?php

use App\Models\PilketosPeriode;
use App\Services\PilketosTokenVerificationService;
use App\Services\PilketosVotingService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public ?PilketosPeriode $periode = null;
    public string $token = '';
    public bool $votingOpen = false;
    public ?string $logoUrl = null;

    public function mount(PilketosVotingService $voting): void
    {
        $this->periode = PilketosPeriode::query()->where('status', 'aktif')->latest('id')->first();
        $this->votingOpen = $this->periode !== null && $voting->isOpen($this->periode);
        $this->logoUrl = 'https://is3.cloudhost.id/alfa/smp/public/logoalfa.png';
    }

    public function verify(PilketosTokenVerificationService $verification): void
    {
        $this->validate(['token' => ['required', 'string', 'size:6']]);

        try {
            $verification->verify($this->token, request()->ip());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->addError('token', $exception->errors()['token'][0]);

            return;
        }

        $this->redirectRoute('pilketos.vote', navigate: true);
    }
}; ?>

<div class="min-h-[calc(100vh-8rem)] bg-[radial-gradient(circle_at_top,_#d1fae5,_#f8fafc_44%,_#e2e8f0)] px-4 py-8 sm:px-6 sm:py-14">
    <section class="mx-auto max-w-lg overflow-hidden rounded-[2rem] border border-white/80 bg-white/90 shadow-[0_28px_70px_-34px_rgba(15,23,42,.55)] backdrop-blur">
        <div class="bg-slate-900 px-6 py-8 text-center text-white sm:px-10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-white/10 font-black">@if ($logoUrl)<img src="{{ $logoUrl }}" alt="Logo SMP Al Musyaffa" class="h-full w-full object-contain p-1">@else OSIS @endif</div>
            <p class="mt-4 text-sm font-bold tracking-[.15em] text-emerald-200">SMP AL MUSYAFFA</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight">{{ $periode?->judul_landing ?: ($periode?->nama ?: 'Pemilihan Ketua OSIS') }}</h1>
            <p class="mt-3 text-sm text-slate-300">Tahun Akademik {{ $periode?->tahun ?? 'belum tersedia' }}</p>
        </div>
        <div class="p-6 sm:p-10">
            <div class="rounded-2xl {{ $votingOpen ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }} px-4 py-3 text-sm font-semibold">{{ $votingOpen ? 'Voting sedang berlangsung. Masukkan token dari panitia untuk memulai.' : 'Voting belum tersedia atau sudah berakhir.' }}</div>
            <form wire:submit="verify" class="mt-7">
                <label for="token" class="text-sm font-bold text-slate-800">Masukkan Token Voting</label>
                <input id="token" wire:model="token" maxlength="6" autocomplete="one-time-code" inputmode="text" autocapitalize="characters" @disabled(! $votingOpen) class="mt-2 block w-full rounded-2xl border-slate-300 bg-slate-50 px-4 py-4 text-center text-2xl font-black uppercase tracking-[.3em] placeholder:text-slate-300 focus:border-emerald-600 focus:ring-emerald-600 disabled:cursor-not-allowed" placeholder="ABC123" required autofocus>
                <p class="mt-2 text-xs text-slate-500">Masukkan enam karakter token, tanpa spasi.</p>
                <x-input-error :messages="$errors->get('token')" class="mt-2" />
                <button type="submit" wire:loading.attr="disabled" wire:target="verify" @disabled(! $votingOpen) class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 font-bold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300"><span wire:loading.remove wire:target="verify">Mulai Memilih</span><span wire:loading wire:target="verify">Memverifikasi token...</span></button>
            </form>
            <a wire:navigate href="{{ route('pilketos.landing') }}" class="mt-6 block text-center text-sm font-bold text-slate-600 hover:text-emerald-700">← Kembali ke landing</a>
        </div>
    </section>
</div>
