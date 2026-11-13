<?php

use App\Models\Biodata;
use App\Models\PilketosHakPilih;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Models\Siswa;
use App\Services\PilketosTeacherDirectory;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.pilketos')] class extends Component {
    public ?PilketosPeriode $periode = null;
    public array $stats = [];
    public function mount(PilketosTeacherDirectory $teachers): void
    {
        $this->periode = PilketosPeriode::query()->where('status','aktif')->latest('id')->first() ?? PilketosPeriode::query()->latest('id')->first();
        $this->stats = array_fill_keys(['total_siswa','total_guru','total_eligible','putra','putri','pasangan_putra','pasangan_putri','token_tersedia','token_digunakan','sudah_memilih','belum_memilih'],0);
        if (! $this->periode) return;
        $nis = Siswa::query()->where('tahun',$this->periode->tahun)->distinct()->pluck('nis');
        $studentTotal = Biodata::query()->whereIn('nis',$nis)->whereIn('jenis_kelamin',['L','P'])->count();
        $teacherTotal = $teachers->eligibleQuery()->get()->filter(fn ($user) => $teachers->normalizeGender($user->jenis_kelamin) !== null)->count();
        $total = $studentTotal + $teacherTotal;
        $sudah = PilketosHakPilih::query()->where('periode_id',$this->periode->id)->where('status','sudah_memilih')->count();
        $this->stats = ['total_siswa'=>$studentTotal,'total_guru'=>$teacherTotal,'total_eligible'=>$total,'putra'=>Biodata::query()->where('jenis_kelamin','L')->whereIn('nis',$nis)->count(),'putri'=>Biodata::query()->where('jenis_kelamin','P')->whereIn('nis',$nis)->count(),'pasangan_putra'=>PilketosPasangan::query()->where('periode_id',$this->periode->id)->where('kategori','L')->count(),'pasangan_putri'=>PilketosPasangan::query()->where('periode_id',$this->periode->id)->where('kategori','P')->count(),'token_tersedia'=>PilketosToken::query()->where('periode_id',$this->periode->id)->where('status','tersedia')->count(),'token_digunakan'=>PilketosToken::query()->where('periode_id',$this->periode->id)->where('status','digunakan')->count(),'sudah_memilih'=>$sudah,'belum_memilih'=>max(0,$total-$sudah)];
    }
}; ?>
<div><div wire:loading.delay><x-skeleton variant="dashboard" /></div><div wire:loading.remove><header class="flex flex-wrap items-start justify-between gap-4"><div><p class="font-bold text-emerald-700">SMP Al Musyaffa</p><h1 class="text-3xl font-black">Dashboard PILKETOS</h1><p>{{ $periode?->tahun ?? 'Belum ada periode' }}</p></div></header><nav class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach(['pilketos.pengaturan.index'=>'Periode & Pengaturan','pilketos.kandidat.index'=>'Kandidat','pilketos.token.index'=>'Token Siswa','pilketos.pemilih.index'=>'Pemilih Siswa','pilketos.token-guru.index'=>'Token Guru','pilketos.pemilih-guru.index'=>'Pemilih Guru','pilketos.hasil.index'=>'Hasil'] as $route=>$label)<a wire:navigate href="{{ route($route) }}" class="rounded bg-emerald-700 p-3 font-bold text-white">{{ $label }}</a>@endforeach</nav><section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach(['Siswa eligible'=>'total_siswa','Guru eligible'=>'total_guru','Total eligible'=>'total_eligible','Sudah memilih'=>'sudah_memilih','Belum memilih'=>'belum_memilih','Token tersedia'=>'token_tersedia','Token digunakan'=>'token_digunakan','Pasangan putra'=>'pasangan_putra','Pasangan putri'=>'pasangan_putri'] as $label=>$key)<article class="rounded-2xl bg-white p-5 shadow"><p>{{ $label }}</p><p class="text-3xl font-black">{{ $stats[$key] }}</p></article>@endforeach</section></div></div>
