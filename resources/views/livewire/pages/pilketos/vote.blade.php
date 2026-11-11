<?php

use App\Models\PilketosPasangan;
use App\Models\PilketosToken;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public array $session = [];
    public $pasangan;
    public function mount(): void { $this->session=Session::get('pilketos.vote',[]); $token=isset($this->session['token_id']) ? PilketosToken::query()->find($this->session['token_id']) : null; if (! $token || $token->status !== 'tersedia') { Session::forget('pilketos.vote'); $this->redirectRoute('pilketos.landing',navigate:true); return; } $this->pasangan=PilketosPasangan::query()->where('periode_id',$token->periode_id)->where('kategori',$this->session['kategori'])->where('is_active',true)->orderBy('nomor_urut')->get(); }
    public function select(int $id): void { Session::put('pilketos.confirm',$id); $this->redirectRoute('pilketos.confirm',navigate:true); }
}; ?>
<div class="rounded-3xl bg-white p-6 shadow-2xl sm:p-10"><h1 class="text-3xl font-black">Pilih pasangan calon</h1><div class="mt-8 grid gap-5 md:grid-cols-2">@forelse($pasangan as $item)<article wire:key="candidate-{{$item->id}}" class="rounded-2xl border p-5">@if($item->photoUrl())<img src="{{$item->photoUrl()}}" class="mb-4 h-48 w-full rounded-xl object-cover" alt="Foto pasangan">@endif<h2 class="text-xl font-black">Pasangan {{$item->nomor_urut}}</h2><p class="mt-2 text-slate-600">{{$item->visi}}</p><button wire:click="select({{$item->id}})" wire:loading.attr="disabled" class="mt-5 rounded-xl bg-slate-900 px-4 py-2 font-bold text-white">Pilih pasangan ini</button></article>@empty<p>Belum ada pasangan aktif.</p>@endforelse</div></div>
