<?php

use App\Models\Biodata;
use App\Models\PilketosHakPilih;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Models\Siswa;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.pilketos')] class extends Component {
    public ?PilketosPeriode $periode = null;
    public array $stats = [];

    public function mount(): void
    {
        $this->periode = PilketosPeriode::query()->where('status', 'aktif')->latest('id')->first() ?? PilketosPeriode::query()->latest('id')->first();
        $this->stats = ['total_siswa' => 0, 'putra' => 0, 'putri' => 0, 'pasangan_putra' => 0, 'pasangan_putri' => 0, 'token_tersedia' => 0, 'token_digunakan' => 0, 'sudah_memilih' => 0, 'belum_memilih' => 0];
        if (! $this->periode) return;
        $nis = Siswa::query()->where('tahun', $this->periode->tahun)->distinct()->pluck('nis');
        $this->stats = ['total_siswa' => $nis->count(), 'putra' => Biodata::query()->where('jenis_kelamin','L')->whereIn('nis',$nis)->count(), 'putri' => Biodata::query()->where('jenis_kelamin','P')->whereIn('nis',$nis)->count(), 'pasangan_putra' => PilketosPasangan::query()->where('periode_id',$this->periode->id)->where('kategori','L')->count(), 'pasangan_putri' => PilketosPasangan::query()->where('periode_id',$this->periode->id)->where('kategori','P')->count(), 'token_tersedia' => PilketosToken::query()->where('periode_id',$this->periode->id)->where('status','tersedia')->count(), 'token_digunakan' => PilketosToken::query()->where('periode_id',$this->periode->id)->where('status','digunakan')->count(), 'sudah_memilih' => PilketosHakPilih::query()->where('periode_id',$this->periode->id)->where('status','sudah_memilih')->count(), 'belum_memilih' => 0];
        $this->stats['belum_memilih'] = max(0, $this->stats['total_siswa'] - $this->stats['sudah_memilih']);
    }
}; ?>
<div><div wire:loading.delay><x-skeleton variant="dashboard" /></div><div wire:loading.remove><header><p class="font-bold text-emerald-700">SMP Al Musyaffa</p><h1 class="text-3xl font-black">Dashboard PILKETOS</h1><p>{{$periode?->tahun ?? 'Belum ada periode'}}</p></header><nav class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach(['pilketos.pengaturan.index'=>'Periode Pemilihan & Pengaturan','pilketos.kandidat.index'=>'Pasangan & Profil Kandidat','pilketos.token.index'=>'Generate Token','pilketos.pemilih.index'=>'Monitoring Pemilih','pilketos.hasil.index'=>'Hasil Pemilihan'] as $route=>$label)<a wire:navigate href="{{route($route)}}" class="rounded bg-emerald-700 p-3 font-bold text-white">{{$label}}</a>@endforeach</nav><section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach(['Total siswa'=>'total_siswa','Putra'=>'putra','Putri'=>'putri','Sudah memilih'=>'sudah_memilih','Belum memilih'=>'belum_memilih','Token tersedia'=>'token_tersedia','Token digunakan'=>'token_digunakan','Pasangan putra'=>'pasangan_putra','Pasangan putri'=>'pasangan_putri'] as $label=>$key)<article class="rounded-2xl bg-white p-5 shadow"><p>{{$label}}</p><p class="text-3xl font-black">{{$stats[$key]}}</p></article>@endforeach</section></div></div>
