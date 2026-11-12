<?php

use App\Models\PilketosPeriode;
use App\Services\PilketosTokenVerificationService;
use App\Services\PilketosVotingService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public ?PilketosPeriode $periode = null;
    public string $token = '';
    public bool $votingOpen = false;
    public string $statusLabel = 'Belum ada periode aktif';
    public string $statusDescription = 'Pemilihan belum dibuka oleh panitia.';
    public ?string $logoUrl = null;
    public ?string $bannerUrl = null;

    public function mount(PilketosVotingService $voting): void
    {
        $this->periode = PilketosPeriode::query()->where('status', 'aktif')->latest('id')->first();

        if ($this->periode === null) {
            return;
        }

        $this->votingOpen = $voting->isOpen($this->periode);
        $this->logoUrl = $this->assetUrl($this->periode->logo_path);
        $this->bannerUrl = $this->assetUrl($this->periode->banner_path);

        if ($this->votingOpen) {
            $this->statusLabel = 'Pemilihan berlangsung';
            $this->statusDescription = 'Akses voting tersedia untuk siswa yang memiliki token.';

            return;
        }

        if ($this->periode->voting_mulai?->isFuture()) {
            $this->statusLabel = 'Pemilihan belum dibuka';
            $this->statusDescription = 'Voting akan dibuka sesuai jadwal yang ditetapkan panitia.';

            return;
        }

        $this->statusLabel = 'Pemilihan telah berakhir';
        $this->statusDescription = 'Masa pemberian suara untuk periode ini telah selesai.';
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

    private function assetUrl(?string $path): ?string
    {
        return $path === null ? null : Storage::disk(config('pilketos.photo_disk'))->url($path);
    }
}; ?>

<div class="overflow-hidden">
    <header class="border-b border-slate-200 bg-white/95">
        <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a wire:navigate href="{{ route('pilketos.landing') }}" class="flex min-w-0 items-center gap-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-800 text-sm font-black text-white shadow-sm">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo SMP Al Musyaffa" class="h-full w-full object-contain p-1">
                    @else
                        OSIS
                    @endif
                </span>
                <span class="min-w-0"><span class="block truncate text-sm font-black tracking-tight text-slate-900 sm:text-base">SMP Al Musyaffa</span><span class="block truncate text-xs font-medium text-slate-500">Portal Pemilihan OSIS</span></span>
            </a>
            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                <span class="hidden rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 sm:inline-flex">{{ $statusLabel }}</span>
                @guest
                    <a wire:navigate href="{{ route('guru.login') }}" class="rounded-xl px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 sm:px-4">Login panitia</a>
                @endguest
                @auth
                    <a wire:navigate href="{{ route('guru.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 sm:px-4">Dashboard</a>
                @endauth
            </div>
        </div>
    </header>
    <section class="mx-auto w-full max-w-7xl px-4 py-6 pb-10 sm:px-6 sm:py-10 sm:pb-14 lg:px-8">
        <div class="relative isolate overflow-hidden rounded-[2rem] bg-slate-900 shadow-[0_24px_70px_-32px_rgba(15,23,42,0.75)]">
            @if ($bannerUrl)
                <img src="{{ $bannerUrl }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" fetchpriority="high">
            @endif
            <div class="absolute inset-0 -z-10 bg-slate-950/70"></div>
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,0.35),_transparent_42%)]"></div>
            <div class="min-h-[28rem] px-6 py-8 sm:min-h-[31rem] sm:px-10 sm:py-12 lg:flex lg:min-h-[34rem] lg:items-end lg:px-14 lg:py-14">
                <div class="max-w-3xl text-white">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold tracking-[0.16em] text-emerald-100"><span class="h-2 w-2 rounded-full {{ $votingOpen ? 'bg-emerald-400' : 'bg-amber-300' }}"></span>PEMILIHAN KETUA OSIS</div>
                    <h1 class="mt-5 text-3xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-[3.45rem]">{{ $periode?->judul_landing ?: ($periode?->nama ?: 'Pemilihan Ketua OSIS') }}</h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-100 sm:text-lg">{{ $periode?->deskripsi_landing ?: 'Gunakan hak pilihmu secara aman untuk menentukan kepemimpinan OSIS berikutnya.' }}</p>
                    <div class="mt-7 flex flex-wrap gap-3 text-sm font-semibold"><span class="rounded-full border border-white/15 bg-slate-950/35 px-4 py-2">Tahun akademik {{ $periode?->tahun ?? 'belum dibuka' }}</span><span class="rounded-full border border-emerald-300/30 bg-emerald-400/15 px-4 py-2 text-emerald-50">{{ $statusLabel }}</span></div>
                </div>
            </div>
        </div>

        <section class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_25rem] lg:items-start">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-emerald-700">Akses pemilihan</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Suaramu membentuk masa depan sekolah.</h2>
                <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ $statusDescription }}</p>
                <dl class="mt-7 grid gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2"><div><dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Periode pemilihan</dt><dd class="mt-1 font-bold text-slate-800">{{ $periode?->nama ?? 'Menunggu pengaturan panitia' }}</dd></div><div><dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Status saat ini</dt><dd class="mt-1 font-bold text-slate-800">{{ $statusLabel }}</dd></div></dl>
            </div>

            <section aria-labelledby="token-heading" class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-[0_18px_45px_-28px_rgba(5,150,105,0.65)] sm:p-7">
                <div class="flex items-start justify-between gap-4"><div><p class="text-sm font-bold uppercase tracking-[0.15em] text-emerald-700">Akses aman</p><h2 id="token-heading" class="mt-2 text-2xl font-black tracking-tight text-slate-900">Gunakan Hak Pilihmu</h2></div><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M7 11V7a5 5 0 0 1 10 0v4M6 11h12v9H6z"/><path d="M12 15v2"/></svg></span></div>
                <p class="mt-3 text-sm leading-6 text-slate-600">Masukkan token pemilihan yang diberikan oleh panitia.</p>
                <form wire:submit="verify" class="mt-6">
                    <label for="voting-token" class="text-sm font-bold text-slate-800">Token voting</label>
                    <input id="voting-token" wire:model="token" maxlength="6" autocomplete="one-time-code" inputmode="text" autocapitalize="characters" aria-describedby="token-help token-error" @disabled(! $votingOpen) class="mt-2 block w-full rounded-2xl border-slate-300 bg-slate-50 px-4 py-3 text-center text-2xl font-black uppercase tracking-[0.28em] text-slate-900 placeholder:tracking-[0.28em] placeholder:text-slate-300 focus:border-emerald-600 focus:ring-emerald-600 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" placeholder="ABC123" required>
                    <p id="token-help" class="mt-2 text-xs leading-5 text-slate-500">Enam karakter, tanpa spasi.</p>
                    <x-input-error id="token-error" :messages="$errors->get('token')" class="mt-2" />
                    <button type="submit" wire:loading.attr="disabled" wire:target="verify" @disabled(! $votingOpen) class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 py-3 font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300"><span wire:loading.remove wire:target="verify">Mulai Memilih</span><span wire:loading wire:target="verify" class="inline-flex items-center gap-2"><svg class="h-4 w-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-90" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>Memverifikasi token...</span></button>
                </form>
            </section>
        </section>

        <section aria-labelledby="steps-heading" class="mt-12 sm:mt-16">
            <div class="max-w-2xl"><p class="text-sm font-bold uppercase tracking-[0.16em] text-emerald-700">Panduan singkat</p><h2 id="steps-heading" class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Cara memilih</h2><p class="mt-3 leading-7 text-slate-600">Tiga langkah sederhana untuk menyampaikan pilihanmu dengan yakin.</p></div>
            <ol class="mt-7 grid gap-4 md:grid-cols-3">
                @foreach ([['01', 'Masukkan Token', 'Gunakan token unik yang diberikan panitia.'], ['02', 'Pelajari Kandidat', 'Lihat pasangan calon sebelum menentukan pilihan.'], ['03', 'Konfirmasi Pilihan', 'Periksa pilihan dan kirim suara.']] as [$number, $title, $description])
                    <li class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><span class="inline-flex rounded-xl bg-emerald-50 px-3 py-1.5 text-sm font-black tracking-wider text-emerald-700">{{ $number }}</span><h3 class="mt-5 text-lg font-black text-slate-900">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }}</p></li>
                @endforeach
            </ol>
        </section>
    </section>
    <div wire:loading.delay wire:target="verify" class="sr-only" role="status">Memverifikasi token</div>
</div>
