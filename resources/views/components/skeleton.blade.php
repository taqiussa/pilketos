@props(['variant' => 'page', 'rows' => 5, 'columns' => 4, 'cards' => 4, 'showHeader' => true, 'showFilters' => true])
<div {{ $attributes->merge(['class' => 'animate-pulse motion-reduce:animate-none space-y-5']) }} role="status" aria-label="Memuat konten">
    @if($showHeader)<div class="h-8 w-1/3 rounded bg-slate-200"></div><div class="h-4 w-1/2 rounded bg-slate-100"></div>@endif
    @if($variant === 'table') @if($showFilters)<div class="h-10 w-full rounded bg-slate-100"></div>@endif<div class="overflow-hidden rounded-xl bg-white">@for($r=0;$r<$rows+1;$r++)<div class="flex gap-4 border-b p-4">@for($c=0;$c<$columns;$c++)<div class="h-4 flex-1 rounded bg-slate-100"></div>@endfor</div>@endfor</div>
    @elseif($variant === 'form') @for($r=0;$r<$rows;$r++)<div class="space-y-2"><div class="h-4 w-24 rounded bg-slate-200"></div><div class="h-10 rounded bg-slate-100"></div></div>@endfor
    @else <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-{{ min($cards,4) }}">@for($i=0;$i<$cards;$i++)<div class="h-28 rounded-2xl bg-slate-100"></div>@endfor</div><div class="h-56 rounded-2xl bg-slate-100"></div>@endif
</div>
