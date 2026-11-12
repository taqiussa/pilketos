@props([
    'label' => '',
    'options' => [],
    'placeholder' => 'Pilih siswa',
    'searchPlaceholder' => 'Cari nama, kelas, atau NIS...',
])

<div
    x-data="{
        options: @js($options),
        isOpen: false,
        search: '',
        activeIndex: -1,
        value: @entangle($attributes->wire('model')),
        get filteredOptions() {
            const keyword = this.search.toLowerCase();
            return this.options.filter((option) => option.label.toLowerCase().includes(keyword));
        },
        get selectedLabel() {
            return this.options.find((option) => String(option.value) === String(this.value))?.label ?? '';
        },
        open() {
            this.isOpen = true;
            this.search = '';
            this.activeIndex = this.filteredOptions.findIndex((option) => String(option.value) === String(this.value));
            if (this.activeIndex === -1 && this.filteredOptions.length) this.activeIndex = 0;
            this.$nextTick(() => this.$refs.search.focus());
        },
        close() {
            this.isOpen = false;
            this.activeIndex = -1;
        },
        moveActive(direction) {
            if (! this.filteredOptions.length) return;
            this.activeIndex = (this.activeIndex + direction + this.filteredOptions.length) % this.filteredOptions.length;
        },
        select(option) {
            this.value = option.value;
            this.search = '';
            this.close();
        },
    }"
    @click.outside="close()"
    class="flex flex-col gap-1"
>
    @if ($label)
        <label class="text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif

    <div class="relative">
        <button type="button" @click="isOpen ? close() : open()" :aria-expanded="isOpen" class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-left text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
            <span class="truncate" :class="selectedLabel ? 'text-slate-800' : 'text-slate-400'" x-text="selectedLabel || @js($placeholder)"></span>
            <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
        </button>

        <div x-cloak x-show="isOpen" x-transition class="absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
            <input type="search" x-model="search" x-ref="search" @input="activeIndex = filteredOptions.length ? 0 : -1" @keydown.arrow-down.prevent="moveActive(1)" @keydown.arrow-up.prevent="moveActive(-1)" @keydown.enter.prevent="activeIndex >= 0 && select(filteredOptions[activeIndex])" @keydown.escape="close()" @click.stop class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" placeholder="{{ $searchPlaceholder }}">
            <div role="listbox" class="mt-2 max-h-60 overflow-y-auto">
                <template x-for="(option, index) in filteredOptions" :key="option.value">
                    <button type="button" @click="select(option)" @mouseenter="activeIndex = index" role="option" :aria-selected="String(option.value) === String(value)" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-800" :class="{'bg-emerald-50 text-emerald-800': String(option.value) === String(value), 'bg-slate-100': index === activeIndex}" x-text="option.label"></button>
                </template>
                <p x-show="filteredOptions.length === 0" class="px-3 py-4 text-center text-sm text-slate-500">Siswa tidak ditemukan</p>
            </div>
        </div>
    </div>

    @error($attributes->wire('model')->value())
        <p class="text-sm font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
