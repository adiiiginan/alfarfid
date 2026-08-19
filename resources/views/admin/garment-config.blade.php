@extends('layouts.admin')

@section('title', 'Konfigurasi Baju')
@section('page-title', 'Konfigurasi Baju')

@section('content')

    {{--
        PERUBAHAN: helper zona siklus, dipakai di tabel Tag RFID (siklus
        tag, basis rated_max_cycles) MAUPUN tabel Garment (siklus baju,
        basis max_cycle_limit). Dua angka ini beda skala/arti (baju
        biasanya ±50x, tag ±200x) makanya dihitung terpisah di masing-
        masing tabel, tapi cara menampilkannya (persen + badge warna)
        disamakan supaya konsisten dengan halaman Analytics & Pindah/
        Ganti Tag. Pakai closure (bukan `function cycleZone(...)` biasa)
        supaya tidak "cannot redeclare function" kalau view ini pernah
        di-render lebih dari sekali dalam satu request.
    --}}
    @php
        $cycleZone = function ($used, $max) {
            $used = (float) ($used ?? 0);
            $max = (float) ($max ?? 0);
            if ($max <= 0) {
                return ['percent' => null, 'label' => '-', 'class' => 'bg-[var(--line)] text-[var(--ink-soft)]'];
            }
            $percent = round(($used / $max) * 100, 1);
            if ($percent >= 90) {
                $style = ['label' => 'Kritis', 'class' => 'bg-[var(--red-soft)] text-[var(--red)]'];
            } elseif ($percent >= 70) {
                $style = ['label' => 'Waspada', 'class' => 'bg-yellow-100 text-yellow-700'];
            } else {
                $style = ['label' => 'Aman', 'class' => 'bg-[var(--teal-soft)] text-[var(--teal)]'];
            }
            return array_merge($style, ['percent' => $percent]);
        };
    @endphp

    <div x-data="garmentConfigPage()" x-init="init()">

        {{-- HEADER --}}
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <div>
                <h2 class="font-display font-semibold text-xl">Konfigurasi Baju</h2>
                <p class="text-sm text-[var(--ink-soft)] mt-1">
                    Kelola jenis baju steril (kategori &amp; batas siklus default), divisi beserta warna bajunya, dan tag
                    RFID.
                </p>
            </div>
        </div>

        {{-- TABS --}}
        <div class="flex items-center gap-1 border-b border-[var(--line)] mb-6">
            <button @click="tab = 'categories'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
                :class="tab === 'categories' ? 'border-[var(--navy)] text-[var(--navy)]' :
                    'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'">
                Kategori Baju
            </button>
            <button @click="tab = 'divisions'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
                :class="tab === 'divisions' ? 'border-[var(--navy)] text-[var(--navy)]' :
                    'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'">
                Divisi &amp; Warna
            </button>
            <button @click="tab = 'tags'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
                :class="tab === 'tags' ? 'border-[var(--navy)] text-[var(--navy)]' :
                    'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'">
                Tag RFID
            </button>
            <button @click="tab = 'garments'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
                :class="tab === 'garments' ? 'border-[var(--navy)] text-[var(--navy)]' :
                    'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'">
                Garment
            </button>
        </div>

        {{-- ===================== TAB: KATEGORI BAJU ===================== --}}
        <div x-show="tab === 'categories'">
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--line)] flex items-center justify-between">
                    <div>
                        <h3 class="font-display font-semibold text-sm">Kategori Baju</h3>
                        <p class="text-xs text-[var(--ink-soft)] mt-0.5">Jenis baju steril beserta batas maksimal siklus
                            default (FR-04).</p>
                    </div>
                    <button @click="openCreateCategory()"
                        class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Tambah Kategori
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-[11px] uppercase tracking-wider text-[var(--ink-soft)] border-b border-[var(--line)]">
                                <th class="px-5 py-3 font-medium">Nama Kategori</th>
                                <th class="px-5 py-3 font-medium">Deskripsi</th>
                                <th class="px-5 py-3 font-medium">Batas Siklus Default</th>
                                <th class="px-5 py-3 font-medium">Jumlah Item</th>
                                <th class="px-5 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--line)]">
                            @forelse (($categories ?? []) as $category)
                                <tr>
                                    <td class="px-5 py-3 font-medium">{{ $category->category_name }}</td>
                                    <td class="px-5 py-3 text-[var(--ink-soft)] max-w-xs truncate">
                                        {{ $category->description }}</td>
                                    <td class="px-5 py-3 font-mono text-xs">{{ $category->default_max_cycle }}x</td>
                                    <td class="px-5 py-3 text-[var(--ink-soft)]">{{ $category->garments_count ?? 0 }} item
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click='openEditCategory(@json($category))'
                                                class="p-1.5 rounded-lg hover:bg-[var(--copper-soft)] text-[var(--ink-soft)]"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6"
                                                    viewBox="0 0 24 24">
                                                    <path d="M12 20h9" stroke-linecap="round" />
                                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                                                </svg>
                                            </button>
                                            <button @click="confirmDeleteCategory('{{ $category->category_id ?? 1 }}')"
                                                class="p-1.5 rounded-lg hover:bg-[var(--red-soft)] text-[var(--ink-soft)] hover:text-[var(--red)]"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6"
                                                    viewBox="0 0 24 24">
                                                    <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-[var(--ink-soft)] text-sm">
                                        Belum ada kategori baju. Klik "Tambah Kategori" untuk mulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== TAB: DIVISI & WARNA ===================== --}}
        <div x-show="tab === 'divisions'" x-cloak>
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--line)] flex items-center justify-between">
                    <div>
                        <h3 class="font-display font-semibold text-sm">Divisi &amp; Warna Baju</h3>
                        <p class="text-xs text-[var(--ink-soft)] mt-0.5">Setiap divisi punya warna baju steril masing-masing
                            meski jenis bajunya sama.</p>
                    </div>
                    <button @click="openCreateDivision()"
                        class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Tambah Divisi
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-[11px] uppercase tracking-wider text-[var(--ink-soft)] border-b border-[var(--line)]">
                                <th class="px-5 py-3 font-medium">Divisi</th>
                                <th class="px-5 py-3 font-medium">Warna Baju</th>
                                <th class="px-5 py-3 font-medium">Deskripsi</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--line)]">
                            @forelse (($divisions ?? []) as $division)
                                <tr>
                                    <td class="px-5 py-3 font-medium">{{ $division->division_name }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full border border-[var(--line)] inline-block"
                                                style="background: {{ $division->color_hex ?? '#999' }};"></span>
                                            <span class="text-[var(--ink-soft)]">{{ $division->color_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-[var(--ink-soft)] max-w-xs truncate">
                                        {{ $division->description }}</td>
                                    <td class="px-5 py-3">
                                        @if ($division->is_active ?? true)
                                            <span
                                                class="text-[11px] font-medium bg-[var(--teal-soft)] text-[var(--teal)] px-2 py-0.5 rounded-full">Aktif</span>
                                        @else
                                            <span
                                                class="text-[11px] font-medium bg-[var(--line)] text-[var(--ink-soft)] px-2 py-0.5 rounded-full">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click='openEditDivision(@json($division))'
                                                class="p-1.5 rounded-lg hover:bg-[var(--copper-soft)] text-[var(--ink-soft)]"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.6" viewBox="0 0 24 24">
                                                    <path d="M12 20h9" stroke-linecap="round" />
                                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                                                </svg>
                                            </button>
                                            <button @click="confirmDeleteDivision('{{ $division->division_id ?? 1 }}')"
                                                class="p-1.5 rounded-lg hover:bg-[var(--red-soft)] text-[var(--ink-soft)] hover:text-[var(--red)]"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.6" viewBox="0 0 24 24">
                                                    <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-[var(--ink-soft)] text-sm">
                                        Belum ada data divisi. Klik "Tambah Divisi" untuk mulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== TAB: TAG RFID ===================== --}}
        <div x-show="tab === 'tags'" x-cloak>
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--line)] flex items-center justify-between">
                    <div>
                        <h3 class="font-display font-semibold text-sm">Tag RFID</h3>
                        <p class="text-xs text-[var(--ink-soft)] mt-0.5">Registrasi tag RFID baru dan kepemilikan
                            divisinya, sebelum tag dipasang ke baju</p>
                        <p class="text-[11px] text-[var(--ink-soft)] mt-1 italic">
                            Siklus tag dihitung terpisah dari siklus baju — satu tag bisa dipakai ulang di beberapa
                            baju berbeda lewat menu Pindah Tag/Ganti Tag, jadi umurnya lebih panjang dari satu baju.
                        </p>
                    </div>
                    <button @click="openCreateTag()"
                        class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Input Tag Baru
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-[11px] uppercase tracking-wider text-[var(--ink-soft)] border-b border-[var(--line)]">
                                <th class="px-5 py-3 font-medium">Tag UID</th>
                                <th class="px-5 py-3 font-medium">Divisi</th>

                                <th class="px-5 py-3 font-medium">Siklus Tag</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--line)]">
                            @forelse (($tags ?? []) as $tag)
                                <tr>
                                    <td class="px-5 py-3 font-mono text-xs font-medium">{{ $tag->tag_uid }}</td>
                                    <td class="px-5 py-3">
                                        @if ($tag->division)
                                            <div class="flex items-center gap-2">
                                                <span class="w-3 h-3 rounded-full border border-[var(--line)] inline-block"
                                                    style="background: {{ $tag->division->color_hex ?? '#999' }};"></span>
                                                <span
                                                    class="text-[var(--ink-soft)]">{{ $tag->division->division_name }}</span>
                                            </div>
                                        @else
                                            <span class="text-[var(--ink-soft)] italic">Belum ditetapkan</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-3">
                                        @php
                                            $tagZone = $cycleZone($tag->total_cycles_used, $tag->rated_max_cycles);
                                        @endphp
                                        <div class="flex flex-col gap-1 min-w-[120px]">
                                            <span class="font-mono text-xs">
                                                {{ $tag->total_cycles_used }} / {{ $tag->rated_max_cycles }}
                                                @if ($tagZone['percent'] !== null)
                                                    <span
                                                        class="text-[var(--ink-soft)]">({{ $tagZone['percent'] }}%)</span>
                                                @endif
                                            </span>
                                            <div class="h-1.5 w-full rounded-full bg-[var(--line)] overflow-hidden">
                                                <div class="h-full rounded-full {{ $tagZone['class'] }}"
                                                    style="width: {{ min(100, $tagZone['percent'] ?? 0) }}%; background-color: currentColor;">
                                                </div>
                                            </div>
                                            <span
                                                class="inline-flex w-fit text-[10px] font-medium px-2 py-0.5 rounded-full {{ $tagZone['class'] }}">
                                                {{ $tagZone['label'] }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        @php
                                            $statusStyle = match ($tag->status) {
                                                'active' => 'bg-[var(--teal-soft)] text-[var(--teal)]',
                                                'retired' => 'bg-[var(--line)] text-[var(--ink-soft)]',
                                                default => 'bg-[var(--red-soft)] text-[var(--red)]',
                                            };
                                        @endphp
                                        <span
                                            class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $statusStyle }}">{{ ucfirst($tag->status) }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click='openEditTag(@json($tag))'
                                                class="p-1.5 rounded-lg hover:bg-[var(--copper-soft)] text-[var(--ink-soft)]"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.6" viewBox="0 0 24 24">
                                                    <path d="M12 20h9" stroke-linecap="round" />
                                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                                                </svg>
                                            </button>
                                            <button @click="confirmDeleteTag('{{ $tag->tag_id }}')"
                                                class="p-1.5 rounded-lg hover:bg-[var(--red-soft)] text-[var(--ink-soft)] hover:text-[var(--red)]"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.6" viewBox="0 0 24 24">
                                                    <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-[var(--ink-soft)] text-sm">
                                        Belum ada tag RFID terdaftar. Klik "Input Tag Baru" untuk mulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== TAB: GARMENT ===================== --}}
        <div x-show="tab === 'garments'" x-cloak>
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--line)] flex items-center justify-between">
                    <div>
                        <h3 class="font-display font-semibold text-sm">Garment</h3>
                        <p class="text-xs text-[var(--ink-soft)] mt-0.5">Baju steril yang sudah didaftarkan beserta tag
                            RFID yang terpasang</p>

                    </div>
                    <button @click="openCreateGarment()"
                        class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Tambah Garment
                    </button>
                </div>

                <div class="overflow-x-auto">
                    {{--
                        PERUBAHAN: tabel ini cuma menampilkan garment yang
                        masih status 'active'. Garment 'retired' (baju yang
                        sudah diarsipkan lewat Pindah Tag/hapus manual) sengaja
                        disembunyikan dari sini supaya tidak campur dengan yang
                        masih dipakai — riwayatnya tetap bisa dilihat lewat
                        halaman Pindah Tag/Ganti Tag (tabel Riwayat Binding Tag).
                    --}}
                    @php
                        $activeGarments = collect($garments ?? [])
                            ->where('status', 'active')
                            ->values();
                    @endphp
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-[11px] uppercase tracking-wider text-[var(--ink-soft)] border-b border-[var(--line)]">
                                <th class="px-5 py-3 font-medium">Kode Garment</th>
                                <th class="px-5 py-3 font-medium">Kategori</th>
                                <th class="px-5 py-3 font-medium">Divisi</th>
                                <th class="px-5 py-3 font-medium">Tag Terpasang</th>
                                <th class="px-5 py-3 font-medium">Siklus Baju</th>
                                <th class="px-5 py-3 font-medium">Siklus Tag</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--line)]">
                            @forelse ($activeGarments as $garment)
                                <tr>
                                    <td class="px-5 py-3 font-medium">{{ $garment->garment_code }}</td>
                                    <td class="px-5 py-3 text-[var(--ink-soft)]">
                                        {{ $garment->category->category_name ?? '-' }}</td>
                                    <td class="px-5 py-3">
                                        @if ($garment->division)
                                            <div class="flex items-center gap-2">
                                                <span class="w-3 h-3 rounded-full border border-[var(--line)] inline-block"
                                                    style="background: {{ $garment->division->color_hex ?? '#999' }};"></span>
                                                <span
                                                    class="text-[var(--ink-soft)]">{{ $garment->division->division_name }}</span>
                                            </div>
                                        @else
                                            <span class="text-[var(--ink-soft)] italic">Belum ditetapkan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs">
                                        {{ $garment->currentTag->tag_uid ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @php
                                            $garmentZone = $cycleZone(
                                                $garment->current_cycle_count,
                                                $garment->max_cycle_limit,
                                            );
                                        @endphp
                                        <div class="flex flex-col gap-1 min-w-[120px]">
                                            <span class="font-mono text-xs">
                                                {{ $garment->current_cycle_count }} / {{ $garment->max_cycle_limit }}
                                                @if ($garmentZone['percent'] !== null)
                                                    <span
                                                        class="text-[var(--ink-soft)]">({{ $garmentZone['percent'] }}%)</span>
                                                @endif
                                            </span>
                                            <div class="h-1.5 w-full rounded-full bg-[var(--line)] overflow-hidden">
                                                <div class="h-full rounded-full {{ $garmentZone['class'] }}"
                                                    style="width: {{ min(100, $garmentZone['percent'] ?? 0) }}%; background-color: currentColor;">
                                                </div>
                                            </div>
                                            <span
                                                class="inline-flex w-fit text-[10px] font-medium px-2 py-0.5 rounded-full {{ $garmentZone['class'] }}">
                                                {{ $garmentZone['label'] }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        {{--
                                            PERUBAHAN: kolom terpisah untuk siklus TAG yang sedang
                                            terpasang di baju ini — beda basis (rated_max_cycles)
                                            dari kolom Siklus Baju di sebelah kiri (max_cycle_limit).
                                            Baju bisa belum punya tag (currentTag null), tampilkan "-".
                                        --}}
                                        @if ($garment->currentTag)
                                            @php
                                                $garmentTagZone = $cycleZone(
                                                    $garment->currentTag->total_cycles_used,
                                                    $garment->currentTag->rated_max_cycles,
                                                );
                                            @endphp
                                            <div class="flex flex-col gap-1 min-w-[120px]">
                                                <span class="font-mono text-xs">
                                                    {{ $garment->currentTag->total_cycles_used }} /
                                                    {{ $garment->currentTag->rated_max_cycles }}
                                                    @if ($garmentTagZone['percent'] !== null)
                                                        <span
                                                            class="text-[var(--ink-soft)]">({{ $garmentTagZone['percent'] }}%)</span>
                                                    @endif
                                                </span>
                                                <div class="h-1.5 w-full rounded-full bg-[var(--line)] overflow-hidden">
                                                    <div class="h-full rounded-full {{ $garmentTagZone['class'] }}"
                                                        style="width: {{ min(100, $garmentTagZone['percent'] ?? 0) }}%; background-color: currentColor;">
                                                    </div>
                                                </div>
                                                <span
                                                    class="inline-flex w-fit text-[10px] font-medium px-2 py-0.5 rounded-full {{ $garmentTagZone['class'] }}">
                                                    {{ $garmentTagZone['label'] }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-[var(--ink-soft)] italic text-xs">Belum ada tag</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @php
                                            $garmentStatusStyle = match ($garment->status) {
                                                'active' => 'bg-[var(--teal-soft)] text-[var(--teal)]',
                                                'retired' => 'bg-[var(--line)] text-[var(--ink-soft)]',
                                                default => 'bg-[var(--red-soft)] text-[var(--red)]',
                                            };
                                        @endphp
                                        <span
                                            class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $garmentStatusStyle }}">{{ ucfirst($garment->status) }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click="confirmDeleteGarment('{{ $garment->garment_id }}')"
                                                class="p-1.5 rounded-lg hover:bg-[var(--red-soft)] text-[var(--ink-soft)] hover:text-[var(--red)]"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="1.6" viewBox="0 0 24 24">
                                                    <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-[var(--ink-soft)] text-sm">
                                        Belum ada garment aktif. Klik "Tambah Garment" untuk mulai, atau semua garment
                                        yang ada sudah berstatus retired.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== MODAL: KATEGORI BAJU ===================== --}}
        <div x-show="categoryModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(20,24,34,.45);">
            <div @click.away="categoryModalOpen = false" class="card w-full max-w-md">
                <form
                    :action="categoryFormMode === 'create' ? '{{ route('admin.garment-categories.store') }}' :
                        categoryEditActionUrl"
                    method="POST">
                    @csrf
                    <template x-if="categoryFormMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
                        <h3 class="font-display font-semibold text-base"
                            x-text="categoryFormMode === 'create' ? 'Tambah Kategori Baju' : 'Edit Kategori Baju'"></h3>
                        <button type="button" @click="categoryModalOpen = false"
                            class="text-[var(--ink-soft)] hover:text-[var(--ink)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Nama Kategori</label>
                            <input type="text" name="category_name" x-model="categoryForm.category_name" required
                                placeholder="mis. Sterile Garment"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Deskripsi</label>
                            <textarea name="description" x-model="categoryForm.description" rows="2"
                                placeholder="Deskripsi singkat kategori baju ini"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Batas Maksimal Siklus
                                Default</label>
                            <input type="number" name="default_max_cycle" x-model="categoryForm.default_max_cycle"
                                required min="1" placeholder="mis. 200"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                            <p class="text-[11px] text-[var(--ink-soft)] mt-1">Dipakai sebagai default saat mendaftarkan
                                item baru dari kategori ini; masih bisa dioverride per item.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-[var(--line)] flex justify-end gap-2">
                        <button type="button" @click="categoryModalOpen = false"
                            class="text-xs font-medium px-4 py-2 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition">
                            <span x-text="categoryFormMode === 'create' ? 'Simpan Kategori' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== MODAL: DIVISI & WARNA ===================== --}}
        <div x-show="divisionModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(20,24,34,.45);">
            <div @click.away="divisionModalOpen = false" class="card w-full max-w-md">
                <form
                    :action="divisionFormMode === 'create' ? '{{ route('admin.divisions.store') }}' : divisionEditActionUrl"
                    method="POST">
                    @csrf
                    <template x-if="divisionFormMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
                        <h3 class="font-display font-semibold text-base"
                            x-text="divisionFormMode === 'create' ? 'Tambah Divisi' : 'Edit Divisi'"></h3>
                        <button type="button" @click="divisionModalOpen = false"
                            class="text-[var(--ink-soft)] hover:text-[var(--ink)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Nama Divisi</label>
                            <input type="text" name="division_name" x-model="divisionForm.division_name" required
                                placeholder="mis. Produksi Steril"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Nama Warna</label>
                                <input type="text" name="color_name" x-model="divisionForm.color_name" required
                                    placeholder="mis. Biru"
                                    class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Kode Warna</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="color_hex_picker" x-model="divisionForm.color_hex"
                                        class="w-10 h-9 rounded-lg border border-[var(--line)] p-0.5 cursor-pointer">
                                    <input type="text" name="color_hex" x-model="divisionForm.color_hex" required
                                        placeholder="#2A5CAA"
                                        class="flex-1 text-sm px-3 py-2 rounded-lg border border-[var(--line)] font-mono focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Deskripsi</label>
                            <textarea name="description" x-model="divisionForm.description" rows="2"
                                placeholder="Deskripsi singkat divisi ini"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20"></textarea>
                        </div>

                        <template x-if="divisionFormMode === 'edit'">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" x-model="divisionForm.is_active"
                                    id="division_is_active" class="w-4 h-4 rounded border-[var(--line)]">
                                <label for="division_is_active" class="text-sm">Divisi aktif</label>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t border-[var(--line)] flex justify-end gap-2">
                        <button type="button" @click="divisionModalOpen = false"
                            class="text-xs font-medium px-4 py-2 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition">
                            <span x-text="divisionFormMode === 'create' ? 'Simpan Divisi' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== MODAL: TAG RFID ===================== --}}
        <div x-show="tagModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(20,24,34,.45);">
            <div @click.away="closeAllModals()" class="card w-full max-w-md">
                <form :action="tagFormMode === 'create' ? '{{ route('admin.tags.store') }}' : tagEditActionUrl"
                    method="POST">
                    @csrf
                    <template x-if="tagFormMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
                        <h3 class="font-display font-semibold text-base"
                            x-text="tagFormMode === 'create' ? 'Input Tag RFID Baru' : 'Edit Tag RFID'"></h3>
                        <button type="button" @click="closeAllModals()"
                            class="text-[var(--ink-soft)] hover:text-[var(--ink)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-[var(--ink-soft)] mb-1">Tag UID</label>
                            <div class="flex gap-2">
                                <input type="text" name="tag_uid" x-model="tagForm.tag_uid" required
                                    :readonly="tagFormMode === 'edit'" placeholder="Scan reader atau ketik manual"
                                    class="flex-1 text-sm px-3 py-2 rounded-lg border border-[var(--line)] font-mono focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20"
                                    :class="tagFormMode === 'edit' ? 'bg-[var(--copper-soft)]/40 cursor-not-allowed' : ''">
                                <button type="button" @click="toggleScan()" x-show="tagFormMode === 'create'"
                                    class="shrink-0 text-xs font-medium px-3 py-2 rounded-lg border transition"
                                    :class="scanning ?
                                        'bg-[var(--red)] text-white border-[var(--red)]' :
                                        'border-[var(--navy)] text-[var(--navy)] hover:bg-[var(--navy)]/5'">
                                    <span x-show="!scanning">Mulai Scan</span>
                                    <span x-show="scanning">Batal (<span x-text="scanCountdown"></span>s)</span>
                                </button>
                            </div>
                            <p class="mt-1 text-[10px] text-[var(--ink-soft)]">
                                <span x-show="!scanning">Klik "Mulai Scan" lalu tempelkan tag ke reader mana pun, atau
                                    ketik manual UID di atas.</span>
                                <span x-show="scanning && !scannedFromDevice" class="text-[var(--navy)]">Menunggu tag
                                    discan di reader...</span>
                                <span x-show="scannedFromDevice" class="text-[var(--teal)]">Terdeteksi dari:
                                    <span x-text="scannedFromDevice"></span></span>
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Divisi</label>
                            <select name="division_id" x-model="tagForm.division_id" required
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                <option value="" disabled>Pilih divisi</option>
                                @foreach ($divisions ?? [] as $division)
                                    <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field di bawah ini HANYA muncul saat mode EDIT.
                             Saat mode CREATE, nilai default (tag_type, manufacturer,
                             rated_max_cycles) sudah di-hardcode di TagController@store,
                             jadi operator tidak perlu (dan tidak bisa) mengisinya manual
                             lewat form registrasi. --}}
                        <template x-if="tagFormMode === 'edit'">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Tipe
                                            Tag</label>
                                        <input type="text" name="tag_type" x-model="tagForm.tag_type"
                                            placeholder="mis. NFC, UHF"
                                            class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Rated Max
                                            Cycles</label>
                                        <input type="number" name="rated_max_cycles" x-model="tagForm.rated_max_cycles"
                                            min="1" placeholder="200"
                                            class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Manufaktur</label>
                                    <input type="text" name="manufacturer" x-model="tagForm.manufacturer"
                                        placeholder="mis. Alien Technology"
                                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Catatan</label>
                                    <textarea name="notes" x-model="tagForm.notes" rows="2"
                                        placeholder="Catatan opsional, mis. asal pembelian/batch"
                                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20"></textarea>
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Status</label>
                                    <select name="status" x-model="tagForm.status"
                                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                        <option value="active">Active</option>
                                        <option value="retired">Retired</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="lost">Lost</option>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t border-[var(--line)] flex justify-end gap-2">
                        <button type="button" @click="closeAllModals()"
                            class="text-xs font-medium px-4 py-2 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition">
                            Batal
                        </button>
                        <button type="submit" @click="stopScan()"
                            class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition">
                            <span x-text="tagFormMode === 'create' ? 'Daftarkan Tag' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== MODAL: GARMENT ===================== --}}
        <div x-show="garmentModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(20,24,34,.45);">
            <div @click.away="garmentModalOpen = false" class="card w-full max-w-md">
                <form action="{{ route('admin.garments.store') }}" method="POST">
                    @csrf

                    <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
                        <h3 class="font-display font-semibold text-base">Tambah Garment Baru</h3>
                        <button type="button" @click="garmentModalOpen = false"
                            class="text-[var(--ink-soft)] hover:text-[var(--ink)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Kode Garment</label>
                            <input type="text" name="garment_code" x-model="garmentForm.garment_code" required
                                placeholder="mis. GRM-0001"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] font-mono focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Kategori</label>
                            <select name="category_id" x-model="garmentForm.category_id" required
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                <option value="" disabled>Pilih kategori</option>
                                @foreach ($categories ?? [] as $category)
                                    <option value="{{ $category->category_id }}">
                                        {{ $category->category_name }} ({{ $category->default_max_cycle }}x)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Divisi</label>
                                <select name="division_id" x-model="garmentForm.division_id"
                                    :disabled="garmentForm.tag_id"
                                    class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20"
                                    :class="garmentForm.tag_id ?
                                        'bg-[var(--copper-soft)]/40 cursor-not-allowed text-[var(--ink-soft)]' : ''">
                                    <option value="">Belum ditentukan</option>
                                    @foreach ($divisions ?? [] as $division)
                                        <option value="{{ $division->division_id }}">{{ $division->division_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="division_id" :value="garmentForm.division_id"
                                    x-show="garmentForm.tag_id">
                                <p class="text-[11px] text-[var(--ink-soft)] mt-1" x-show="garmentForm.tag_id">
                                    Divisi mengikuti tag RFID yang dipilih.
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Ukuran</label>
                                <input type="text" name="size" x-model="garmentForm.size" placeholder="mis. L, XL"
                                    class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Tag RFID</label>

                            <div class="relative" x-data="{ tagDropdownOpen: false }" @click.away="tagDropdownOpen = false">
                                <button type="button" @click="tagDropdownOpen = !tagDropdownOpen"
                                    class="w-full flex items-center justify-between gap-2 text-sm px-3 py-2 rounded-lg border border-[var(--line)] bg-white focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                                    <template x-if="selectedTag()">
                                        <span class="flex items-center gap-2 truncate">
                                            <span
                                                class="w-3 h-3 rounded-full border border-[var(--line)] inline-block shrink-0"
                                                :style="`background: ${selectedTag().color_hex || '#999'}`"></span>
                                            <span class="font-mono text-xs" x-text="selectedTag().tag_uid"></span>
                                            <span class="text-[var(--ink-soft)]"
                                                x-text="selectedTag().division_name ? '— ' + selectedTag().division_name : '— Divisi belum ditetapkan'"></span>
                                        </span>
                                    </template>
                                    <span class="text-[var(--ink-soft)]" x-show="!selectedTag()">Pilih tag yang
                                        tersedia</span>

                                    <svg class="w-4 h-4 text-[var(--ink-soft)] shrink-0" fill="none"
                                        stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>

                                <div x-show="tagDropdownOpen" x-cloak
                                    class="absolute z-10 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-[var(--line)] bg-white shadow-lg">
                                    <template x-for="t in availableTagsList" :key="t.tag_id">
                                        <button type="button"
                                            @click="garmentForm.tag_id = t.tag_id; garmentForm.division_id = t.division_id || ''; tagDropdownOpen = false"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-[var(--copper-soft)] text-left">
                                            <span
                                                class="w-3 h-3 rounded-full border border-[var(--line)] inline-block shrink-0"
                                                :style="`background: ${t.color_hex || '#999'}`"></span>
                                            <span class="font-mono text-xs" x-text="t.tag_uid"></span>
                                            <span class="text-[var(--ink-soft)] text-xs"
                                                x-text="t.division_name ? '— ' + t.division_name : '— Divisi belum ditetapkan'"></span>
                                        </button>
                                    </template>
                                    <div x-show="availableTagsList.length === 0"
                                        class="px-3 py-4 text-center text-xs text-[var(--ink-soft)]">
                                        Tidak ada tag tersedia.
                                    </div>
                                </div>

                                <input type="hidden" name="tag_id" :value="garmentForm.tag_id">
                            </div>

                            <p class="text-[11px] text-[var(--ink-soft)] mt-1">Hanya menampilkan tag berstatus aktif
                                yang belum terpasang ke garment manapun.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-[var(--line)] flex justify-end gap-2">
                        <button type="button" @click="garmentModalOpen = false"
                            class="text-xs font-medium px-4 py-2 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--navy)] text-white hover:opacity-90 transition">
                            Simpan Garment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== MODAL KONFIRMASI HAPUS (SHARED) ===================== --}}
        <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(20,24,34,.45);">
            <div @click.away="deleteModalOpen = false" class="card w-full max-w-sm p-6">
                <h3 class="font-display font-semibold text-base">Hapus data ini?</h3>
                <p class="text-sm text-[var(--ink-soft)] mt-2">
                    Tindakan ini tidak dapat dibatalkan. Pastikan tidak ada item garment aktif yang masih memakai
                    kategori/divisi/tag ini.
                </p>
                <div class="flex justify-end gap-2 mt-5">
                    <button @click="deleteModalOpen = false"
                        class="text-xs font-medium px-4 py-2 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition">
                        Batal
                    </button>
                    <form :action="deleteActionUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="text-xs font-medium px-4 py-2 rounded-lg bg-[var(--red)] text-white hover:opacity-90 transition">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @php
        // Disiapkan terpisah di sini (bukan inline di dalam @json() pada script)
        // supaya tidak ada closure PHP multi-baris di dalam @json() —
        // beberapa editor/linter salah mengira ini sintaks JavaScript.
        $availableTagsForJs = collect($availableTags ?? [])->map(function ($t) {
            return [
                'tag_id' => $t->tag_id,
                'tag_uid' => $t->tag_uid,
                'division_id' => $t->division_id,
                'division_name' => optional($t->division)->division_name,
                'color_hex' => optional($t->division)->color_hex,
            ];
        });
    @endphp

    @push('scripts')
        <script>
            function garmentConfigPage() {
                return {
                    tab: 'categories',

                    categoryModalOpen: false,
                    categoryFormMode: 'create',
                    categoryEditActionUrl: '',
                    categoryForm: {
                        category_name: '',
                        description: '',
                        default_max_cycle: ''
                    },

                    divisionModalOpen: false,
                    divisionFormMode: 'create',
                    divisionEditActionUrl: '',
                    divisionForm: {
                        division_name: '',
                        color_name: '',
                        color_hex: '#2A5CAA',
                        description: '',
                        is_active: true
                    },

                    tagModalOpen: false,
                    tagFormMode: 'create',
                    tagEditActionUrl: '',
                    tagForm: {
                        tag_uid: '',
                        division_id: '',
                        // Properti di bawah ini TIDAK ditampilkan di form CREATE
                        // (nilai default sudah di-hardcode di TagController@store),
                        // tapi tetap dipakai & bisa diedit di form EDIT.
                        tag_type: '',
                        manufacturer: '',
                        rated_max_cycles: 200,
                        notes: '',
                        status: 'active'
                    },
                    scanning: false,
                    scanCountdown: 60,
                    scannedFromDevice: null,
                    scanPollTimer: null,
                    scanCountdownTimer: null,

                    // ============ Garment (baju) ============
                    garmentModalOpen: false,
                    garmentForm: {
                        garment_code: '',
                        category_id: '',
                        division_id: '',
                        size: '',
                        tag_id: ''
                    },
                    availableTagsList: @json($availableTagsForJs),
                    selectedTag() {
                        return this.availableTagsList.find(t => t.tag_id === this.garmentForm.tag_id) || null;
                    },

                    deleteModalOpen: false,
                    deleteActionUrl: '',

                    init() {},

                    // Menutup semua modal sebelum membuka salah satunya,
                    // supaya tidak ada dua modal yang tampil bertumpuk.
                    closeAllModals() {
                        this.stopScan();
                        this.categoryModalOpen = false;
                        this.divisionModalOpen = false;
                        this.tagModalOpen = false;
                        this.garmentModalOpen = false;
                        this.deleteModalOpen = false;
                    },

                    openCreateCategory() {
                        this.closeAllModals();
                        this.categoryFormMode = 'create';
                        this.categoryForm = {
                            category_name: '',
                            description: '',
                            default_max_cycle: ''
                        };
                        this.categoryModalOpen = true;
                    },
                    openEditCategory(category) {
                        this.closeAllModals();
                        this.categoryFormMode = 'edit';
                        this.categoryForm = {
                            category_name: category.category_name,
                            description: category.description,
                            default_max_cycle: category.default_max_cycle
                        };
                        this.categoryEditActionUrl = `{{ url('admin/garment-categories') }}/${category.category_id}`;
                        this.categoryModalOpen = true;
                    },
                    confirmDeleteCategory(id) {
                        this.closeAllModals();
                        this.deleteActionUrl = `{{ url('admin/garment-categories') }}/${id}`;
                        this.deleteModalOpen = true;
                    },

                    openCreateDivision() {
                        this.closeAllModals();
                        this.divisionFormMode = 'create';
                        this.divisionForm = {
                            division_name: '',
                            color_name: '',
                            color_hex: '#2A5CAA',
                            description: '',
                            is_active: true
                        };
                        this.divisionModalOpen = true;
                    },
                    openEditDivision(division) {
                        this.closeAllModals();
                        this.divisionFormMode = 'edit';
                        this.divisionForm = {
                            division_name: division.division_name,
                            color_name: division.color_name,
                            color_hex: division.color_hex,
                            description: division.description,
                            is_active: !!division.is_active
                        };
                        this.divisionEditActionUrl = `{{ url('admin/divisions') }}/${division.division_id}`;
                        this.divisionModalOpen = true;
                    },
                    confirmDeleteDivision(id) {
                        this.closeAllModals();
                        this.deleteActionUrl = `{{ url('admin/divisions') }}/${id}`;
                        this.deleteModalOpen = true;
                    },

                    openCreateTag() {
                        this.closeAllModals();
                        this.tagFormMode = 'create';
                        this.tagForm = {
                            tag_uid: '',
                            division_id: '',
                            tag_type: '',
                            manufacturer: '',
                            rated_max_cycles: 200,
                            notes: '',
                            status: 'active'
                        };
                        this.tagModalOpen = true;
                    },
                    openEditTag(tag) {
                        this.stopScan();
                        this.closeAllModals();
                        this.tagFormMode = 'edit';
                        this.tagForm = {
                            tag_uid: tag.tag_uid,
                            division_id: tag.division_id,
                            tag_type: tag.tag_type,
                            manufacturer: tag.manufacturer,
                            rated_max_cycles: tag.rated_max_cycles,
                            notes: tag.notes,
                            status: tag.status
                        };
                        this.tagEditActionUrl = `{{ url('admin/tags') }}/${tag.tag_id}`;
                        this.tagModalOpen = true;
                    },
                    confirmDeleteTag(id) {
                        this.stopScan();
                        this.closeAllModals();
                        this.deleteActionUrl = `{{ url('admin/tags') }}/${id}`;
                        this.deleteModalOpen = true;
                    },

                    // ============ Garment (baju): buka modal create & konfirmasi hapus ============
                    openCreateGarment() {
                        this.closeAllModals();
                        this.garmentForm = {
                            garment_code: '',
                            category_id: '',
                            division_id: '',
                            size: '',
                            tag_id: ''
                        };
                        this.garmentModalOpen = true;
                    },
                    confirmDeleteGarment(id) {
                        this.closeAllModals();
                        this.deleteActionUrl = `{{ url('admin/garments') }}/${id}`;
                        this.deleteModalOpen = true;
                    },

                    // ============ Scan RFID via ESP32 (global, semua reader) ============
                    toggleScan() {
                        this.scanning ? this.stopScan() : this.startScan();
                    },
                    async startScan() {
                        this.scanning = true;
                        this.scanCountdown = 60;
                        this.scannedFromDevice = null;

                        await fetch(`{{ url('admin/tag-registration/start') }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        this.scanPollTimer = setInterval(() => this.pollScan(), 2000);
                        this.scanCountdownTimer = setInterval(() => {
                            this.scanCountdown--;
                            if (this.scanCountdown <= 0) this.stopScan();
                        }, 1000);
                    },
                    async pollScan() {
                        const res = await fetch(`{{ url('admin/tag-registration/poll') }}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.tag_uid) {
                            this.tagForm.tag_uid = data.tag_uid;
                            this.scannedFromDevice = data.device_name ?? null;
                            this.stopScan();
                        }
                    },
                    stopScan() {
                        if (!this.scanning && !this.scanPollTimer) return;
                        this.scanning = false;
                        clearInterval(this.scanPollTimer);
                        clearInterval(this.scanCountdownTimer);
                        this.scanPollTimer = null;
                        this.scanCountdownTimer = null;

                        fetch(`{{ url('admin/tag-registration/stop') }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                    }
                }
            }
        </script>
    @endpush

@endsection
