<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {}; ?>
<div class="mx-auto max-w-xl rounded-3xl bg-white p-8 text-center shadow-2xl"><h1 class="text-3xl font-black">Terima kasih</h1><p class="mt-4 text-slate-600">Pilihan Anda sudah berhasil direkam.</p><a wire:navigate href="{{route('pilketos.landing')}}" class="mt-6 inline-block rounded-xl bg-emerald-700 px-4 py-3 font-bold text-white">Kembali ke beranda</a></div>
