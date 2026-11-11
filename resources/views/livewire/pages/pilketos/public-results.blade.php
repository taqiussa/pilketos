<?php

use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosSuara;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public PilketosPeriode $periode;
    public $hasil;
    public int $total = 0;
    public function mount(): void { $this->periode=PilketosPeriode::query()->where('hasil_publik',true)->whereIn('status',['aktif','ditutup'])->latest('id')->firstOrFail(); $this->total=PilketosSuara::query()->where('periode_id',$this->periode->id)->count(); $counts=PilketosSuara::query()->where('periode_id',$this->periode->id)->selectRaw('pasangan_id,count(*) as jumlah')->groupBy('pasangan_id')->pluck('jumlah','pasangan_id'); $this->hasil=PilketosPasangan::query()->where('periode_id',$this->periode->id)->orderBy('kategori')->orderBy('nomor_urut')->get()->map(function($p)use($counts){$p->jumlah_suara=(int)($counts[$p->id]??0);$p->persentase=$this->total?round($p->jumlah_suara/$this->total*100,2):0;return $p;}); }
}; ?>
<section class="rounded-3xl bg-white p-6 shadow-2xl sm:p-10"><a wire:navigate href="{{route('pilketos.landing')}}" class="font-bold text-emerald-700">← Beranda</a><h1 class="mt-5 text-3xl font-black">{{$periode->nama}}</h1><p class="text-slate-600">Total suara sah {{$total}}</p><div class="mt-8 grid gap-6 md:grid-cols-2">@foreach(['L'=>'Putra','P'=>'Putri'] as $k=>$title)<section class="rounded-2xl border p-5"><h2 class="text-xl font-black">Hasil {{$title}}</h2>@forelse($hasil->where('kategori',$k) as $item)<article class="mt-4">@if($item->photoUrl())<img src="{{$item->photoUrl()}}" class="mb-2 h-28 w-full rounded object-cover" alt="Foto pasangan">@endif<div class="flex justify-between font-bold"><span>Nomor {{$item->nomor_urut}}</span><span>{{$item->jumlah_suara}} ({{$item->persentase}}%)</span></div><div class="mt-2 h-3 rounded bg-slate-200"><div class="h-full rounded bg-emerald-600" style="width:{{$item->persentase}}%"></div></div></article>@empty<p class="mt-4 text-slate-500">Belum ada data.</p>@endforelse</section>@endforeach</div></section>
