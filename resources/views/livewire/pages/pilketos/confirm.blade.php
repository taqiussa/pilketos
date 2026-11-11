<?php

use App\Models\PilketosPasangan;
use App\Services\PilketosVotingService;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public ?PilketosPasangan $pasangan = null;
    public function mount(): void { $id=Session::get('pilketos.confirm'); if (! $id || ! Session::has('pilketos.vote')) { $this->redirectRoute('pilketos.landing',navigate:true); return; } $this->pasangan=PilketosPasangan::query()->findOrFail($id); }
    public function submit(PilketosVotingService $voting): void { $session=Session::get('pilketos.vote'); if (!is_array($session)||!$this->pasangan) abort(419); $voting->record((int)$session['token_id'],$this->pasangan->id); Session::forget(['pilketos.vote','pilketos.confirm']); Session::regenerateToken(); $this->redirectRoute('pilketos.done',navigate:true); }
}; ?>
<div class="mx-auto max-w-xl rounded-3xl bg-white p-8 text-center shadow-2xl">@if($pasangan)@if($pasangan->photoUrl())<img src="{{$pasangan->photoUrl()}}" class="h-48 w-full rounded-xl object-cover" alt="Foto pasangan">@endif<h1 class="mt-4 text-3xl font-black">Pasangan nomor {{$pasangan->nomor_urut}}</h1><p class="mt-4 text-slate-600">Pilihan ini tidak dapat diubah setelah dikirim.</p><button wire:click="submit" wire:loading.attr="disabled" class="mt-6 w-full rounded-xl bg-emerald-700 px-4 py-3 font-bold text-white">Ya, rekam suara saya</button><a wire:navigate href="{{route('pilketos.vote')}}" class="mt-4 inline-block font-bold">Kembali</a>@endif</div>
