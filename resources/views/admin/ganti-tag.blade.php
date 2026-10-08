@extends('layouts.admin')

@section('title', 'Ganti Tag')

@section('content')
    <div class="space-y-6">

        {{-- ================= HEADER ================= --}}
        <div>
            <h1 class="font-display font-semibold text-2xl text-slate-900">Ganti Tag</h1>
            <p class="text-sm text-slate-500 mt-1">
                Baju masih bagus, tag sudah aus. Pilih baju, lalu tekan <strong>Mulai Scan</strong> dan tempelkan tag
                fisik baru ke reader — bukan pilih dari daftar, karena tag barunya memang belum pernah terdaftar.
                Tag lama diarsipkan (retired), siklus baju <strong>tidak</strong> direset.
            </p>
            <p class="text-xs text-slate-400 mt-2">
                Baju belum punya tag sama sekali? Itu bukan kasus ini &mdash; pakai menu
                <a href="{{ route('admin.pindah-tag.index') }}" class="underline hover:text-slate-600">Pindah Tag</a>.
            </p>
        </div>

        {{-- ================= FLASH MESSAGES ================= --}}
        @if (session('success'))
            <div
                class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-medium mb-1">Periksa kembali input berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $garmentsForJs = $eligibleGarments->map(function ($garment) {
                return [
                    'garment_id' => (string) $garment->garment_id,
                    'garment_code' => $garment->garment_code,
                    'size' => $garment->size ?? '-',
                    'status' => $garment->status,
                    'division_id' => (string) ($garment->division_id ?? ''),
                    'division_name' => $garment->division->division_name ?? '—',
                    'division_color' => $garment->division->color_hex ?? '#64748b',
                    'tag_uid' => $garment->currentTag->tag_uid ?? '—',
                    'total_cycles_used' => (int) ($garment->currentTag->total_cycles_used ?? 0),
                    'rated_max_cycles' => (int) ($garment->currentTag->rated_max_cycles ?? 0),
                    'remaining_cycles' => $garment->currentTag->remaining_cycles ?? 0,
                    'wear_percent' => $garment->currentTag->wear_percent ?? 0,
                    'is_near_end_of_life' => (bool) ($garment->currentTag->is_near_end_of_life ?? false),
                    'is_over_limit' => (bool) ($garment->currentTag->is_over_limit ?? false),
                ];
            })->values();

            $initialGarmentId = (string) old('garment_id', '');
        @endphp

        {{-- ================= FORM GANTI TAG ================= --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm"
            x-data="gantiTagGarmentSearch({ garments: {{ Js::from($garmentsForJs) }}, initialId: '{{ $initialGarmentId }}' })">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-display font-semibold text-slate-900 text-base">Pasang Tag Baru ke Baju Ini</h2>
            </div>

            <form method="POST" action="{{ route('admin.ganti-tag.store') }}" class="px-6 py-5 space-y-5"
                id="gantiTagForm">
                @csrf

                {{-- STEP 1: Pilih baju via Pencarian Kode Baju --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="garment_search_input" class="block text-sm font-medium text-slate-700">
                            1. Pilih Baju (Cari Kode Baju / Tag UID)
                        </label>
                        <span class="text-xs text-slate-400" x-text="garments.length + ' baju siap diganti tag'"></span>
                    </div>

                    {{-- Hidden input untuk submit form --}}
                    <input type="hidden" name="garment_id" id="garment_id" :value="selectedGarment ? selectedGarment.garment_id : ''" required>

                    {{-- STATE A: Belum memilih baju (Tampilkan Search Box & Dropdown) --}}
                    <div x-show="!selectedGarment" class="relative">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                id="garment_search_input"
                                x-ref="searchInput"
                                x-model="searchQuery"
                                @focus="isOpen = true"
                                @input="isOpen = true"
                                @keydown.escape="isOpen = false"
                                @keydown.enter.prevent="if (filteredGarments.length > 0) selectGarment(filteredGarments[0])"
                                placeholder="Ketik kode baju (mis. GRM001, GAR-BDG-001) atau UID tag..."
                                autocomplete="off"
                                class="w-full rounded-lg border border-slate-300 pl-9 pr-10 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('garment_id') border-red-400 @enderror">

                            <button type="button"
                                x-show="searchQuery.length > 0"
                                @click="searchQuery = ''; $refs.searchInput.focus()"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Dropdown Hasil Pencarian --}}
                        <div x-show="isOpen"
                            @click.away="isOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-30 mt-1 w-full rounded-xl border border-slate-200 bg-white shadow-xl max-h-72 overflow-y-auto divide-y divide-slate-100"
                            style="display: none;">

                            <template x-if="filteredGarments.length === 0">
                                <div class="p-4 text-center text-sm text-slate-400">
                                    <p>Tidak ada baju yang cocok dengan "<span x-text="searchQuery" class="font-medium text-slate-600"></span>"</p>
                                    <p class="text-xs text-slate-400 mt-1">Pastikan kode baju sudah terdaftar dan memiliki tag aktif.</p>
                                </div>
                            </template>

                            <template x-for="g in filteredGarments" :key="g.garment_id">
                                <div @click="selectGarment(g)"
                                    class="p-3 hover:bg-slate-50 cursor-pointer transition flex items-center justify-between gap-3 text-left">
                                    <div class="space-y-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono font-bold text-slate-900 text-sm" x-text="g.garment_code"></span>
                                            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-700">
                                                <span class="w-2 h-2 rounded-full inline-block shrink-0" :style="`background-color: ${g.division_color}`"></span>
                                                <span x-text="g.division_name"></span>
                                            </span>
                                            <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-medium" x-text="'Ukuran ' + g.size"></span>
                                        </div>
                                        <div class="text-xs text-slate-500 flex items-center gap-2">
                                            <span>Tag: <code class="font-mono font-medium text-slate-700" x-text="g.tag_uid"></code></span>
                                            <span>&bull;</span>
                                            <span x-text="g.total_cycles_used + '/' + g.rated_max_cycles + ' siklus'"></span>
                                        </div>
                                    </div>

                                    {{-- Badges status keausan --}}
                                    <div class="shrink-0 text-right">
                                        <template x-if="g.is_over_limit">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                                <span>🔴</span> Over Limit
                                            </span>
                                        </template>
                                        <template x-if="!g.is_over_limit && g.is_near_end_of_life">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                <span>⚠</span> Mepet Siklus
                                            </span>
                                        </template>
                                        <template x-if="!g.is_over_limit && !g.is_near_end_of_life">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                                                Normal
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- STATE B: Baju sudah dipilih (Tampilkan Kartu Preview Baju Terpilih) --}}
                    <div x-show="selectedGarment" style="display: none;" class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Baju Terpilih
                            </span>
                            <button type="button"
                                @click="clearSelection()"
                                class="inline-flex items-center gap-1 text-xs font-medium text-[var(--copper)] hover:text-blue-800 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Ganti / Cari Baju Lain
                            </button>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-3 bg-white p-3 rounded-lg border border-slate-200">
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-slate-400 font-medium">Kode Baju</p>
                                <p class="font-mono font-bold text-base text-slate-900 mt-0.5" x-text="selectedGarment ? selectedGarment.garment_code : ''"></p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-slate-400 font-medium">Divisi & Ukuran</p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="w-2.5 h-2.5 rounded-full inline-block shrink-0" :style="`background-color: ${selectedGarment ? selectedGarment.division_color : '#64748b'}`"></span>
                                    <span class="text-sm font-medium text-slate-800" x-text="selectedGarment ? selectedGarment.division_name : ''"></span>
                                    <span class="text-xs text-slate-500 font-normal" x-text="'(Size ' + (selectedGarment ? selectedGarment.size : '-') + ')'"></span>
                                </div>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-slate-400 font-medium">Tag RFID Saat Ini</p>
                                <p class="font-mono text-sm font-semibold text-slate-700 mt-0.5" x-text="selectedGarment ? selectedGarment.tag_uid : ''"></p>
                                <p class="text-xs text-slate-500" x-text="(selectedGarment ? selectedGarment.total_cycles_used : 0) + ' / ' + (selectedGarment ? selectedGarment.rated_max_cycles : 0) + ' siklus'"></p>
                            </div>
                        </div>

                        {{-- Dynamic Alert Banner status keausan --}}
                        <template x-if="selectedGarment && selectedGarment.is_over_limit">
                            <div class="flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-800">
                                <span class="text-base leading-none">🔴</span>
                                <div>
                                    <strong class="font-semibold">Tag saat ini sudah melewati batas siklus maksimal</strong> (<span x-text="selectedGarment.total_cycles_used"></span>/<span x-text="selectedGarment.rated_max_cycles"></span> siklus). Sangat tepat untuk diganti sekarang.
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedGarment && !selectedGarment.is_over_limit && selectedGarment.is_near_end_of_life">
                            <div class="flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                                <span class="text-base leading-none">⚠</span>
                                <div>
                                    <strong class="font-semibold">Tag saat ini mendekati batas siklus</strong> (<span x-text="selectedGarment.total_cycles_used"></span>/<span x-text="selectedGarment.rated_max_cycles"></span> siklus, sisa <span x-text="selectedGarment.remaining_cycles"></span> siklus). Disarankan untuk segera diganti.
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedGarment && !selectedGarment.is_over_limit && !selectedGarment.is_near_end_of_life">
                            <div class="flex items-start gap-2.5 rounded-lg border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800">
                                <svg class="w-4 h-4 shrink-0 text-blue-600 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    Tag lama (<span class="font-mono font-medium" x-text="selectedGarment.tag_uid"></span> &bull; <span x-text="selectedGarment.total_cycles_used + '/' + selectedGarment.rated_max_cycles + ' siklus'"></span>) akan otomatis diarsipkan (retired) saat tag baru terpasang.
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- STEP 2: Scan tag baru (tombol Mulai Scan + polling, sama pola dengan "Input Tag Baru") --}}
                <div class="rounded-lg border-2 border-dashed border-slate-300 px-4 py-5 bg-slate-50">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        2. Scan tag fisik baru
                    </label>

                    <div class="flex items-center gap-3">
                        <button type="button" id="scanBtn" disabled
                            class="shrink-0 inline-flex items-center gap-2 rounded-lg bg-[var(--copper)] px-4 py-2.5 text-sm font-medium text-white hover:opacity-90 transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg id="scanBtnIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 3.75H6A2.25 2.25 0 0 0 3.75 6v2.25M8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25m12-12H18A2.25 2.25 0 0 1 20.25 6v2.25m-12 12H18A2.25 2.25 0 0 0 20.25 18v-2.25M9 12h6" />
                            </svg>
                            <span id="scanBtnLabel">Mulai Scan</span>
                        </button>

                        <input type="text" name="new_tag_uid" id="new_tag_uid" autocomplete="off" required readonly
                            value="{{ old('new_tag_uid') }}" placeholder="UID tag akan muncul di sini setelah di-scan..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-mono bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('new_tag_uid') border-red-400 @enderror">
                    </div>

                    <div class="flex items-center justify-between mt-2">
                        <p id="scanStatus" class="text-xs text-slate-400">Pilih baju dulu, baru tombol Mulai Scan aktif.</p>
                        <button type="button" id="manualToggle"
                            class="text-xs text-slate-400 underline hover:text-slate-600">
                            Ketik manual
                        </button>
                    </div>
                </div>

                {{-- STEP 3: Detail tag baru (opsional, ada default) --}}
                <details class="rounded-lg border border-slate-200 px-4 py-3">
                    <summary class="text-sm font-medium text-slate-700 cursor-pointer select-none">
                        3. Detail tag baru <span class="text-slate-400 font-normal">(opsional, ada default)</span>
                    </summary>
                    <div class="grid sm:grid-cols-2 gap-5 mt-4">
                        <div>
                            <label for="tag_type" class="block text-sm font-medium text-slate-700 mb-1.5">Tipe tag</label>
                            <input type="text" name="tag_type" id="tag_type" value="{{ old('tag_type', 'UHF') }}"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                        </div>
                        <div>
                            <label for="manufacturer"
                                class="block text-sm font-medium text-slate-700 mb-1.5">Manufaktur</label>
                            <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                        </div>
                        <div>
                            <label for="rated_max_cycles" class="block text-sm font-medium text-slate-700 mb-1.5">Rated max
                                cycles</label>
                            <input type="number" name="rated_max_cycles" id="rated_max_cycles" min="1"
                                value="{{ old('rated_max_cycles', 200) }}"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                        </div>
                        <div>
                            <label for="division_id" class="block text-sm font-medium text-slate-700 mb-1.5">Divisi
                                tag</label>
                            <select name="division_id" id="division_id"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                                <option value="">— Sama seperti baju —</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->division_id }}"
                                        {{ old('division_id') === $division->division_id ? 'selected' : '' }}>
                                        {{ $division->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </details>

                <div>
                    <label for="unbind_reason" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Alasan tag lama diarsipkan <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="unbind_reason" id="unbind_reason" maxlength="100"
                        value="{{ old('unbind_reason') }}" placeholder="mis. Tag aus, sinyal lemah"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <p class="text-xs text-slate-400 max-w-sm">Siklus baju tidak berubah — ini baju yang sama, cuma tag-nya
                        yang baru.</p>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-[var(--copper)] px-5 py-2.5 text-sm font-medium text-white hover:opacity-90 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24">
                            <path d="M17 2.5 21 6l-4 3.5M21 6H8a5 5 0 0 0-5 5" />
                            <path d="M7 21.5 3 18l4-3.5M3 18h13a5 5 0 0 0 5-5" />
                        </svg>
                        Ganti Tag
                    </button>
                </div>
            </form>
        </div>

        {{-- ================= RIWAYAT ================= --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-display font-semibold text-slate-900 text-base">Riwayat Binding Tag</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                            <th class="px-6 py-3 font-medium">Tag UID</th>
                            <th class="px-6 py-3 font-medium">Baju</th>
                            <th class="px-6 py-3 font-medium">Dipasang</th>
                            <th class="px-6 py-3 font-medium">Dilepas</th>
                            <th class="px-6 py-3 font-medium">Operator</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($history as $binding)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $binding->tag->tag_uid ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-slate-700">{{ $binding->garment->garment_code ?? '—' }}</td>
                                <td class="px-6 py-3 text-slate-500">
                                    {{ optional($binding->bound_at)->format('d M Y H:i') }}</td>
                                <td class="px-6 py-3 text-slate-500">
                                    {{ $binding->unbound_at ? $binding->unbound_at->format('d M Y H:i') : '—' }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $binding->boundByUser->full_name ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    @if ($binding->is_current)
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 text-xs px-2.5 py-0.5">Aktif</span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-slate-100 text-slate-500 text-xs px-2.5 py-0.5">Ditutup</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400">Belum ada histori
                                    binding tag.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($history->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">{{ $history->links() }}</div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function registerAlpineGarmentSearch() {
                Alpine.data('gantiTagGarmentSearch', (config) => ({
                    garments: config.garments || [],
                    initialId: config.initialId || '',
                    searchQuery: '',
                    isOpen: false,
                    selectedGarment: null,

                    get filteredGarments() {
                        if (!this.searchQuery.trim()) {
                            return this.garments;
                        }
                        const q = this.searchQuery.toLowerCase().trim();
                        return this.garments.filter(g =>
                            (g.garment_code && g.garment_code.toLowerCase().includes(q)) ||
                            (g.tag_uid && g.tag_uid.toLowerCase().includes(q)) ||
                            (g.division_name && g.division_name.toLowerCase().includes(q)) ||
                            (g.size && g.size.toLowerCase().includes(q))
                        );
                    },

                    init() {
                        if (this.initialId) {
                            const found = this.garments.find(g => String(g.garment_id) === String(this.initialId));
                            if (found) {
                                this.selectGarment(found);
                            }
                        }
                    },

                    selectGarment(garment) {
                        this.selectedGarment = garment;
                        this.isOpen = false;
                        this.searchQuery = '';

                        const garmentIdInput = document.getElementById('garment_id');
                        if (garmentIdInput) {
                            garmentIdInput.value = garment.garment_id;
                        }

                        const divisionSelect = document.getElementById('division_id');
                        if (divisionSelect && garment.division_id && !divisionSelect.value) {
                            divisionSelect.value = garment.division_id;
                        }

                        const scanBtn = document.getElementById('scanBtn');
                        if (scanBtn) {
                            scanBtn.disabled = false;
                        }

                        const scanStatus = document.getElementById('scanStatus');
                        if (scanStatus && typeof scanning !== 'undefined' && !scanning) {
                            scanStatus.textContent = 'Baju dipilih. Tekan Mulai Scan lalu tempelkan tag baru ke reader.';
                            scanStatus.className = 'text-xs text-slate-400';
                        }
                    },

                    clearSelection() {
                        this.selectedGarment = null;
                        this.searchQuery = '';

                        const garmentIdInput = document.getElementById('garment_id');
                        if (garmentIdInput) {
                            garmentIdInput.value = '';
                        }

                        const scanBtn = document.getElementById('scanBtn');
                        if (scanBtn) {
                            scanBtn.disabled = true;
                        }

                        const scanStatus = document.getElementById('scanStatus');
                        if (scanStatus) {
                            scanStatus.textContent = 'Pilih baju dulu, baru tombol Mulai Scan aktif.';
                            scanStatus.className = 'text-xs text-slate-400';
                        }

                        if (typeof scanning !== 'undefined' && scanning && typeof stopScan === 'function') {
                            stopScan(false);
                        }

                        this.$nextTick(() => {
                            if (this.$refs.searchInput) {
                                this.$refs.searchInput.focus();
                            }
                        });
                    }
                }));
            }

            if (window.Alpine) {
                registerAlpineGarmentSearch();
            } else {
                document.addEventListener('alpine:init', registerAlpineGarmentSearch);
            }

            const newTagUidInput = document.getElementById('new_tag_uid');
            const scanStatus = document.getElementById('scanStatus');
            const divisionSelect = document.getElementById('division_id');
            const scanBtn = document.getElementById('scanBtn');
            const scanBtnLabel = document.getElementById('scanBtnLabel');
            const manualToggle = document.getElementById('manualToggle');

            const csrfToken = document.querySelector('#gantiTagForm input[name="_token"]').value;

            let pollTimer = null;
            let timeoutTimer = null;
            let scanning = false;

            const POLL_INTERVAL_MS = 1500;
            const SCAN_TIMEOUT_MS = 30000;

            // ── SCAN VIA READER (start/poll/stop) ──────────────────
            async function startScan() {
                const garmentIdInput = document.getElementById('garment_id');
                if (!garmentIdInput || !garmentIdInput.value) {
                    scanStatus.textContent = 'Pilih baju dulu sebelum mulai scan.';
                    scanStatus.className = 'text-xs text-amber-600';
                    return;
                }

                scanning = true;
                newTagUidInput.value = '';
                newTagUidInput.readOnly = true;
                scanBtnLabel.textContent = 'Batalkan Scan';
                scanBtn.classList.add('!bg-slate-500');
                scanStatus.textContent = 'Menyalakan reader, menunggu tag di-scan...';
                scanStatus.className = 'text-xs text-blue-600';

                try {
                    const res = await fetch('{{ route('admin.tag-registration.start') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (!res.ok) {
                        scanStatus.textContent = `Gagal memulai sesi scan (HTTP ${res.status}). Cek console/network tab.`;
                        scanStatus.className = 'text-xs text-red-600';
                        console.error('tag-registration/start gagal:', res.status, await res.text());
                        resetScanUI();
                        return;
                    }
                } catch (e) {
                    scanStatus.textContent = 'Gagal menghubungi reader. Coba lagi.';
                    scanStatus.className = 'text-xs text-red-600';
                    console.error('tag-registration/start error:', e);
                    resetScanUI();
                    return;
                }

                pollTimer = setInterval(pollForTag, POLL_INTERVAL_MS);
                timeoutTimer = setTimeout(() => {
                    scanStatus.textContent = 'Timeout — tidak ada tag terbaca dalam 30 detik. Coba lagi.';
                    scanStatus.className = 'text-xs text-amber-600';
                    stopScan(false);
                }, SCAN_TIMEOUT_MS);
            }

            async function pollForTag() {
                try {
                    const res = await fetch('{{ route('admin.tag-registration.poll') }}', {
                        headers: {
                            'Accept': 'application/json'
                        },
                    });
                    const data = await res.json();

                    const detectedUid = data.tag_uid ?? data.uid ?? null;

                    if (detectedUid) {
                        newTagUidInput.value = detectedUid;
                        scanStatus.textContent = `Tag terbaca: ${detectedUid}`;
                        scanStatus.className = 'text-xs text-emerald-600';
                        stopScan(true);
                    }
                } catch (e) {
                    // Diamkan, dicoba lagi di siklus polling berikutnya.
                }
            }

            async function stopScan(found) {
                clearInterval(pollTimer);
                clearTimeout(timeoutTimer);
                pollTimer = null;
                timeoutTimer = null;

                try {
                    await fetch('{{ route('admin.tag-registration.stop') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) {
                    // Diamkan — sesi scan tetap dianggap selesai di sisi UI.
                }

                resetScanUI();

                if (!found) {
                    newTagUidInput.value = '';
                }
            }

            function resetScanUI() {
                scanning = false;
                scanBtnLabel.textContent = 'Mulai Scan';
                scanBtn.classList.remove('!bg-slate-500');
                newTagUidInput.readOnly = !manualModeOn;
            }

            scanBtn.addEventListener('click', () => {
                if (scanning) {
                    scanStatus.textContent = 'Scan dibatalkan.';
                    scanStatus.className = 'text-xs text-slate-400';
                    stopScan(false);
                } else {
                    startScan();
                }
            });

            // ── FALLBACK: ketik manual (kalau reader tidak tersedia) ─
            let manualModeOn = false;
            manualToggle.addEventListener('click', () => {
                manualModeOn = !manualModeOn;
                newTagUidInput.readOnly = !manualModeOn || scanning;
                manualToggle.textContent = manualModeOn ? 'Pakai reader' : 'Ketik manual';
                if (manualModeOn) {
                    newTagUidInput.value = '';
                    newTagUidInput.focus();
                    scanStatus.textContent = 'Mode input manual aktif — ketik UID tag secara langsung.';
                    scanStatus.className = 'text-xs text-slate-400';
                }
            });
        </script>
    @endpush
@endsection
