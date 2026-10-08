@extends('layouts.admin')

@section('title', 'Pindah Tag')

@section('content')
    <div class="space-y-6">

        {{-- ================= HEADER ================= --}}
        <div>

            <h1 class="font-display font-semibold text-2xl text-slate-900">Pindah Tag</h1>
            <p class="text-sm text-slate-500 mt-1">
                Baju rusak, tag masih bagus. Tag lama dipindahkan ke baju baru yang sudah diinput lewat menu
                <em>Tambah Garment Baru</em> tapi belum dipasangi tag. Baju asal akan diarsipkan (retired), siklus
                baju baru direset ke 0.
            </p>
            <p class="text-xs text-slate-400 mt-2">
                Baju tujuan sudah punya tag lain? Itu bukan kasus ini &mdash; pakai menu
                <a href="{{ route('admin.ganti-tag.index') }}" class="underline hover:text-slate-600">Ganti Tag</a>.
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
            $tagsForJs = $availableTags->map(function ($tag) use ($currentBindings) {
                $originGarmentCode = $currentBindings[$tag->tag_id] ?? null;
                return [
                    'tag_id' => (string) $tag->tag_id,
                    'tag_uid' => $tag->tag_uid,
                    'total_cycles_used' => (int) $tag->total_cycles_used,
                    'rated_max_cycles' => (int) $tag->rated_max_cycles,
                    'remaining_cycles' => $tag->remaining_cycles ?? 0,
                    'wear_percent' => $tag->wear_percent ?? 0,
                    'is_near_end_of_life' => (bool) ($tag->is_near_end_of_life ?? false),
                    'is_over_limit' => (bool) ($tag->is_over_limit ?? false),
                    'division_name' => $tag->division->division_name ?? '—',
                    'division_color' => $tag->division->color_hex ?? '#64748b',
                    'origin_garment_code' => $originGarmentCode,
                ];
            })->values();

            $garmentsForJs = $eligibleGarments->map(function ($garment) {
                return [
                    'garment_id' => (string) $garment->garment_id,
                    'garment_code' => $garment->garment_code,
                    'size' => $garment->size ?? '-',
                ];
            })->values();

            $initialTagId = (string) old('tag_id', '');
            $initialGarmentId = (string) old('new_garment_id', '');
        @endphp

        {{-- ================= FORM PINDAH TAG ================= --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm"
            x-data="pindahTagHandler({ tags: {{ Js::from($tagsForJs) }}, garments: {{ Js::from($garmentsForJs) }}, initialTagId: '{{ $initialTagId }}', initialGarmentId: '{{ $initialGarmentId }}' })">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-display font-semibold text-slate-900 text-base">Pindahkan Tag ke Baju Baru</h2>
            </div>

            <form method="POST" action="{{ route('admin.pindah-tag.store') }}" class="px-6 py-5 space-y-5"
                id="pindahTagForm">
                @csrf

                <div class="grid sm:grid-cols-2 gap-5 items-start">
                    {{-- KOLOM KIRI: Pilih Tag Lama (dengan Pencarian Live) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="tag_search_input" class="block text-sm font-medium text-slate-700">
                                1. Tag Lama (yang mau dipindah)
                            </label>
                            <span class="text-xs text-slate-400" x-text="tags.length + ' tag aktif'"></span>
                        </div>

                        {{-- Hidden input untuk form submission --}}
                        <input type="hidden" name="tag_id" id="tag_id" :value="selectedTag ? selectedTag.tag_id : ''" required>

                        {{-- STATE A: Belum memilih tag (Search box & dropdown) --}}
                        <div x-show="!selectedTag" class="relative">
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text"
                                    id="tag_search_input"
                                    x-ref="tagSearchInput"
                                    x-model="searchQuery"
                                    @focus="isOpen = true"
                                    @input="isOpen = true"
                                    @keydown.escape="isOpen = false"
                                    @keydown.enter.prevent="if (firstSelectableTag) selectTag(firstSelectableTag)"
                                    placeholder="Cari UID tag (mis. E280...) atau kode baju asal..."
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-slate-300 pl-9 pr-10 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('tag_id') border-red-400 @enderror">

                                <button type="button"
                                    x-show="searchQuery.length > 0"
                                    @click="searchQuery = ''; $refs.tagSearchInput.focus()"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Dropdown Hasil Pencarian Tag --}}
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

                                <template x-if="filteredTags.length === 0">
                                    <div class="p-4 text-center text-sm text-slate-400">
                                        <p>Tidak ada tag yang cocok dengan "<span x-text="searchQuery" class="font-medium text-slate-600"></span>"</p>
                                    </div>
                                </template>

                                <template x-for="t in filteredTags" :key="t.tag_id">
                                    <div @click="!t.is_over_limit && selectTag(t)"
                                        :class="{
                                            'opacity-60 bg-red-50/40 cursor-not-allowed': t.is_over_limit,
                                            'hover:bg-slate-50 cursor-pointer': !t.is_over_limit
                                        }"
                                        class="p-3 transition flex items-center justify-between gap-3 text-left">
                                        <div class="space-y-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-mono font-bold text-slate-900 text-sm" x-text="t.tag_uid"></span>
                                                <template x-if="t.origin_garment_code">
                                                    <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full border border-blue-200 bg-blue-50 text-blue-700 font-medium">
                                                        <span>Baju:</span> <span class="font-mono font-semibold" x-text="t.origin_garment_code"></span>
                                                    </span>
                                                </template>
                                                <template x-if="!t.origin_garment_code">
                                                    <span class="text-[11px] px-2 py-0.5 rounded-full border border-slate-200 bg-slate-100 text-slate-500">
                                                        Bebas / Belum terpasang
                                                    </span>
                                                </template>
                                            </div>
                                            <div class="text-xs text-slate-500 flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="w-2 h-2 rounded-full inline-block shrink-0" :style="`background-color: ${t.division_color}`"></span>
                                                    <span x-text="t.division_name"></span>
                                                </span>
                                                <span>&bull;</span>
                                                <span x-text="t.total_cycles_used + '/' + t.rated_max_cycles + ' siklus (' + t.wear_percent + '%)'"></span>
                                            </div>
                                        </div>

                                        {{-- Badges status keausan --}}
                                        <div class="shrink-0 text-right">
                                            <template x-if="t.is_over_limit">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold bg-red-100 text-red-700 border border-red-200">
                                                    <span>🔴</span> Over Limit (Terkunci)
                                                </span>
                                            </template>
                                            <template x-if="!t.is_over_limit && t.is_near_end_of_life">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                    <span>⚠</span> Sisa <span x-text="t.remaining_cycles"></span>
                                                </span>
                                            </template>
                                            <template x-if="!t.is_over_limit && !t.is_near_end_of_life">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600">
                                                    Normal
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- STATE B: Tag sudah dipilih (Tampilkan Kartu Preview Tag Terpilih) --}}
                        <div x-show="selectedTag" style="display: none;" class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Tag Terpilih
                                </span>
                                <button type="button"
                                    @click="clearTag()"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-[var(--copper)] hover:text-blue-800 hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Ganti / Cari Tag Lain
                                </button>
                            </div>

                            <div class="bg-white p-3 rounded-lg border border-slate-200 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wide text-slate-400 font-medium">Tag UID</p>
                                        <p class="font-mono font-bold text-base text-slate-900 mt-0.5" x-text="selectedTag ? selectedTag.tag_uid : ''"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[11px] uppercase tracking-wide text-slate-400 font-medium">Siklus Tag</p>
                                        <p class="text-sm font-semibold text-slate-800 mt-0.5" x-text="(selectedTag ? selectedTag.total_cycles_used : 0) + ' / ' + (selectedTag ? selectedTag.rated_max_cycles : 0) + ' siklus'"></p>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Status baju asal:</span>
                                    <template x-if="selectedTag && selectedTag.origin_garment_code">
                                        <span class="font-medium text-slate-700">
                                            Baju <strong class="font-mono text-blue-700" x-text="selectedTag.origin_garment_code"></strong> (akan diarsipkan)
                                        </span>
                                    </template>
                                    <template x-if="selectedTag && !selectedTag.origin_garment_code">
                                        <span class="text-slate-500">Belum terpasang ke baju mana pun</span>
                                    </template>
                                </div>
                            </div>

                            {{-- Dynamic Alert Banner keausan --}}
                            <template x-if="selectedTag && selectedTag.is_over_limit">
                                <div class="flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 p-2.5 text-xs text-red-800">
                                    <span class="text-base leading-none">🔴</span>
                                    <div>
                                        <strong class="font-semibold">Batas siklus terlampaui!</strong> Tag ini tidak dapat dipindahkan lagi ke baju baru.
                                    </div>
                                </div>
                            </template>

                            <template x-if="selectedTag && !selectedTag.is_over_limit && selectedTag.is_near_end_of_life">
                                <div class="flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 p-2.5 text-xs text-amber-800">
                                    <span class="text-base leading-none">⚠</span>
                                    <div>
                                        <strong class="font-semibold">Perhatian:</strong> Tag sudah <span x-text="selectedTag.wear_percent"></span>% dari batas siklus (sisa <span x-text="selectedTag.remaining_cycles"></span> siklus).
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- KOLOM KANAN: Baju tujuan (baru, belum ada tag) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="new_garment_id" class="block text-sm font-medium text-slate-700">
                                2. Baju Tujuan (Baru, belum ada tag)
                            </label>
                            <span class="text-xs text-slate-400" x-text="garments.length + ' baju kosong'"></span>
                        </div>

                        <select name="new_garment_id" id="new_garment_id" x-model="selectedGarmentId" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('new_garment_id') border-red-400 @enderror">
                            <option value="">— Pilih baju tujuan —</option>
                            @forelse ($eligibleGarments as $garment)
                                <option value="{{ $garment->garment_id }}"
                                    {{ old('new_garment_id') === $garment->garment_id ? 'selected' : '' }}>
                                    {{ $garment->garment_code }} &middot; ukuran {{ $garment->size ?? '-' }}
                                </option>
                            @empty
                                <option value="" disabled>Tidak ada baju yang belum punya tag. Buat dulu lewat Tambah Garment Baru.</option>
                            @endforelse
                        </select>
                        @if ($eligibleGarments->isEmpty())
                            <p class="mt-1.5 text-xs text-amber-600">
                                Belum ada baju kosong. Input baju baru dulu lewat menu
                                <span class="font-medium">Tambah Garment Baru</span> tanpa mengisi field tag.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Preview Skenario Pindah Tag --}}
                <div x-show="selectedTag && selectedGarment" style="display: none;"
                    class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm flex items-start gap-3 text-blue-800">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.25 11.25h.008v.008h-.008v-.008ZM12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM11.25 15h.008v.008h-.008V15Zm.375-6.375a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    <div>
                        Tag <strong class="font-mono font-bold" x-text="selectedTag ? selectedTag.tag_uid : ''"></strong> akan dipindahkan ke baju <strong class="font-mono font-bold" x-text="selectedGarment ? selectedGarment.garment_code : ''"></strong>. Siklus baju tujuan direset ke 0.
                    </div>
                </div>

                <div>
                    <label for="unbind_reason" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Alasan baju lama diarsipkan <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="unbind_reason" id="unbind_reason" maxlength="100"
                        value="{{ old('unbind_reason') }}" placeholder="mis. Baju robek / rusak, retired"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <p class="text-xs text-slate-400 max-w-sm">Proses ini tercatat di Audit Trail dan tidak bisa dibatalkan
                        begitu tersimpan.</p>
                    <button type="submit" id="submitBtn"
                        :disabled="!selectedTag || !selectedGarmentId || (selectedTag && selectedTag.is_over_limit)"
                        class="inline-flex items-center gap-2 rounded-lg bg-[var(--copper)] px-5 py-2.5 text-sm font-medium text-white hover:opacity-90 transition disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M17 2.5 21 6l-4 3.5M21 6H8a5 5 0 0 0-5 5" />
                            <path d="M7 21.5 3 18l4-3.5M3 18h13a5 5 0 0 0 5-5" />
                        </svg>
                        Pindahkan Tag
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
            function registerAlpinePindahTag() {
                Alpine.data('pindahTagHandler', (config) => ({
                    tags: config.tags || [],
                    garments: config.garments || [],
                    initialTagId: config.initialTagId || '',
                    initialGarmentId: config.initialGarmentId || '',
                    searchQuery: '',
                    isOpen: false,
                    selectedTag: null,
                    selectedGarmentId: config.initialGarmentId || '',

                    get filteredTags() {
                        if (!this.searchQuery.trim()) {
                            return this.tags;
                        }
                        const q = this.searchQuery.toLowerCase().trim();
                        return this.tags.filter(t =>
                            (t.tag_uid && t.tag_uid.toLowerCase().includes(q)) ||
                            (t.origin_garment_code && t.origin_garment_code.toLowerCase().includes(q)) ||
                            (t.division_name && t.division_name.toLowerCase().includes(q))
                        );
                    },

                    get firstSelectableTag() {
                        return this.filteredTags.find(t => !t.is_over_limit) || null;
                    },

                    get selectedGarment() {
                        return this.garments.find(g => String(g.garment_id) === String(this.selectedGarmentId)) || null;
                    },

                    init() {
                        if (this.initialTagId) {
                            const found = this.tags.find(t => String(t.tag_id) === String(this.initialTagId));
                            if (found) {
                                this.selectTag(found);
                            }
                        }
                    },

                    selectTag(tag) {
                        if (tag.is_over_limit) {
                            return;
                        }
                        this.selectedTag = tag;
                        this.isOpen = false;
                        this.searchQuery = '';
                    },

                    clearTag() {
                        this.selectedTag = null;
                        this.searchQuery = '';
                        this.$nextTick(() => {
                            if (this.$refs.tagSearchInput) {
                                this.$refs.tagSearchInput.focus();
                            }
                        });
                    }
                }));
            }

            if (window.Alpine) {
                registerAlpinePindahTag();
            } else {
                document.addEventListener('alpine:init', registerAlpinePindahTag);
            }
        </script>
    @endpush
@endsection
