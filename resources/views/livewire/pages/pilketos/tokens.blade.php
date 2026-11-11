<?php

use App\Models\Kelas;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Services\PilketosTokenService;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.pilketos')] class extends Component {
    use WithPagination;
    public $periodes; public $kelas; public ?int $periodeId=null; public ?int $kelasId=null; public array $printed=[];
    public function mount(): void { $this->periodes=PilketosPeriode::query()->latest('tahun')->get(); $this->kelas=Kelas::query()->orderBy('tingkat')->orderBy('nama')->get(); $this->periodeId=$this->periodes->first()?->id; $this->kelasId=$this->kelas->first()?->id; }
    public function generate(PilketosTokenService $service): void { $this->validate(['periodeId'=>['required','exists:pilketos_periodes,id'],'kelasId'=>['required','exists:kelas,id']]); $this->printed=$service->generateForClass(PilketosPeriode::query()->findOrFail($this->periodeId),(int)$this->kelasId,auth()->id()); $this->dispatch('tokens-generated'); }
    public function replaceToken(int $id,PilketosTokenService $service): void { try{$plain=$service->replaceUnusedToken($id,auth()->id());$t=PilketosToken::query()->findOrFail($id);$this->printed=[['nis'=>$t->nis,'name'=>'Token pengganti','kelas'=>'Distribusikan langsung kepada siswa','token'=>$plain]];}catch(RuntimeException $e){$this->addError('token',$e->getMessage());} }
    public function with(): array { return ['tokens'=>PilketosToken::query()->where('periode_id',$this->periodeId)->latest('id')->paginate(25)]; }
}; ?>
<div><a wire:navigate href="{{route('guru.dashboard')}}" class="font-bold text-emerald-700">← Dashboard</a><h1 class="mt-4 text-3xl font-black">Token pemilih</h1><form wire:submit="generate" class="mt-6 flex flex-wrap gap-3 rounded-2xl bg-white p-5 shadow"><select wire:model="periodeId" class="rounded border-slate-300">@foreach($periodes as $p)<option value="{{$p->id}}">{{$p->tahun}}</option>@endforeach</select><select wire:model="kelasId" class="rounded border-slate-300">@foreach($kelas as $k)<option value="{{$k->id}}">{{$k->tingkat}} {{$k->nama}}</option>@endforeach</select><button wire:loading.attr="disabled" class="rounded bg-emerald-700 px-4 py-2 font-bold text-white">Generate token</button></form><x-input-error :messages="$errors->get('token')" class="mt-3"/>@if($printed)<section class="mt-6 rounded bg-white p-5 shadow">@foreach($printed as $p)<p class="font-mono font-bold">{{$p['nis']}} — {{$p['token']}}</p>@endforeach</section>@endif<section class="mt-6 overflow-x-auto rounded-2xl bg-white shadow"><table class="w-full text-left"><thead><tr><th class="p-3">NIS</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($tokens as $token)<tr class="border-t"><td class="p-3">{{$token->nis}}</td><td>{{$token->status}}</td><td>@if($token->status==='tersedia')<button wire:click="replaceToken({{$token->id}})" wire:loading.attr="disabled" class="font-bold text-amber-700">Ganti</button>@endif</td></tr>@empty<tr><td colspan="3" class="p-4">Belum ada token.</td></tr>@endforelse</tbody></table></section><div class="mt-4">{{$tokens->links()}}</div></div>
