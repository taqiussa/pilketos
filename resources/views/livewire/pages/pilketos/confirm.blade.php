<?php

use App\Models\PilketosPasangan;
use App\Models\PilketosToken;
use App\Services\PilketosCandidateProfileService;
use App\Services\PilketosVotingService;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public ?PilketosPasangan $pasangan = null;
    public array $profile = [];

    public function mount(PilketosVotingService $voting, PilketosCandidateProfileService $profiles): void
    {
        $vote = Session::get('pilketos.vote');
        $id = Session::get('pilketos.confirm');
        $token = is_array($vote) ? PilketosToken::query()->with('periode')->find($vote['token_id']) : null;
        $this->pasangan = $token && $id ? PilketosPasangan::query()->whereKey($id)->where('periode_id', $token->periode_id)->where('kategori', $vote['kategori'])->where('is_active', true)->first() : null;

        if ($token === null || ! $voting->isOpen($token->periode) || $this->pasangan === null) {
            Session::forget(['pilketos.vote', 'pilketos.confirm']);
            $this->redirectRoute('pilketos.enter-token', navigate: true);

            return;
        }

        $this->profile = $profiles->forPasangan(collect([$this->pasangan]), $token->periode->tahun)[$this->pasangan->id];
    }

    public function submit(PilketosVotingService $voting): void
    {
        $vote = Session::get('pilketos.vote');
        $confirmation = Session::get('pilketos.confirm');

        if (! is_array($vote) || $this->pasangan === null || (int) $confirmation !== $this->pasangan->id) {
            Session::forget(['pilketos.vote', 'pilketos.confirm']);
            $this->redirectRoute('pilketos.enter-token', navigate: true);

            return;
        }

        $voting->record((int) $vote['token_id'], $this->pasangan->id);
        Session::forget(['pilketos.vote', 'pilketos.confirm']);
        Session::regenerate();
        Session::regenerateToken();
        $this->redirectRoute('pilketos.enter-token', navigate: true);
    }
}; ?>

<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 sm:py-12"><section class="mx-auto max-w-2xl"><p class="text-center text-sm font-bold tracking-[.16em] text-emerald-700">KONFIRMASI PILIHAN</p><h1 class="mt-3 text-center text-3xl font-black tracking-tight text-slate-900">Pastikan pasangan pilihan Anda</h1><p class="mt-3 text-center text-slate-600">Pilihan tidak dapat diubah setelah suara dikirim.</p>
    @if ($pasangan)<x-pilketos.candidate-profile :pasangan="$pasangan" :profile="$profile" class="mt-7" :show-details="false"><div class="mt-6 rounded-2xl bg-amber-50 p-4 text-sm leading-6 text-amber-900">Periksa kembali identitas pasangan calon. Dengan mengirim suara, Anda menyetujui pilihan ini dan proses tidak dapat dibatalkan.</div><button wire:click="submit" wire:loading.attr="disabled" wire:target="submit" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 font-bold text-white transition hover:bg-emerald-800 disabled:bg-slate-300"><span wire:loading.remove wire:target="submit">Ya, Kirim Suara Saya</span><span wire:loading wire:target="submit">Mengirim suara...</span></button><a wire:navigate href="{{ route('pilketos.vote') }}" class="mt-5 block text-center text-sm font-bold text-slate-600 hover:text-emerald-700">Kembali Pilih Kandidat</a></x-pilketos.candidate-profile>@endif
</section></div>
