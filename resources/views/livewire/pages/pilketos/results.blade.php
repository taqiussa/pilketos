<?php

use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosSuara;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.pilketos')] class extends Component {
    public $periodes; public ?int $periodeId=null; public $periode=null; public $hasil; public int $total=0;
    public function mount(): void { $this->periodes=PilketosPeriode::query()->latest('tahun')->get(); $this->periodeId=$this->periodes->first()?->id; $this->loadResults(); }
    public function updatedPeriodeId(): void { $this->loadResults(); }
    public function loadResults(): void { $this->periode=$this->periodeId ? PilketosPeriode::query()->find($this->periodeId) : null; $this->total=0; $this->hasil=collect(); if(!$this->periode)return; $this->total=PilketosSuara::query()->where('periode_id',$this->periode->id)->count();$counts=PilketosSuara::query()->where('periode_id',$this->periode->id)->selectRaw('pasangan_id,count(*) jumlah')->groupBy('pasangan_id')->pluck('jumlah','pasangan_id');$this->hasil=PilketosPasangan::query()->where('periode_id',$this->periode->id)->orderBy('kategori')->orderBy('nomor_urut')->get()->map(function($p)use($counts){$p->jumlah_suara=(int)($counts[$p->id]??0);$p->persentase=$this->total?round($p->jumlah_suara/$this->total*100,2):0;return $p;}); }
}; ?>
<div><a wire:navigate href="{{route('guru.dashboard')}}" class="font-bold text-emerald-700">← Dashboard</a><div class="mt-4 flex flex-wrap gap-3"><h1 class="text-3xl font-black">Hasil pemilihan</h1><select wire:model.live="periodeId" class="rounded border-slate-300">@foreach($periodes as $p)<option value="{{$p->id}}">{{$p->tahun}} — {{$p->nama}}</option>@endforeach</select>@if($periode)<a href="{{route('pilketos.hasil.pdf',$periode)}}" class="rounded bg-slate-900 px-4 py-2 font-bold text-white">Unduh PDF</a>@endif</div><p class="mt-4 text-slate-600">Total suara sah: {{$total}}</p><div wire:loading.delay><x-skeleton variant="candidate-card" :cards="2" /></div><div wire:loading.remove class="mt-6 grid gap-6 lg:grid-cols-2">@foreach(['L'=>'Putra','P'=>'Putri'] as $k=>$title)<section class="rounded-2xl bg-white p-6 shadow"><h2 class="text-xl font-black">Hasil {{$title}}</h2>@forelse($hasil->where('kategori',$k) as $item)<article class="mt-4">@if($item->photoUrl())<img src="{{$item->photoUrl()}}" class="mb-2 h-28 w-full rounded object-cover" alt="Foto pasangan">@endif<div class="flex justify-between font-bold"><span>Nomor {{$item->nomor_urut}}</span><span>{{$item->jumlah_suara}} suara ({{$item->persentase}}%)</span></div><div class="mt-2 h-3 rounded bg-slate-200"><div class="h-full rounded bg-emerald-600" style="width:{{$item->persentase}}%"></div></div></article>@empty<p class="mt-4 text-slate-500">Belum ada pasangan.</p>@endforelse</section>@endforeach</div></div>
