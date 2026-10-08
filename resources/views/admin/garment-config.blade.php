@extends('layouts.admin')

@section('title', 'Konfigurasi Baju')
@section('page-title', 'Konfigurasi Baju')

@push('styles')
<style>
    .btn-timbul-blue {
        background: linear-gradient(135deg, #1E40AF 0%, #2563EB 50%, #3B82F6 100%) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.42), 0 2px 4px -1px rgba(37, 99, 235, 0.28) !important;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-timbul-blue:hover {
        background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 50%, #2563EB 100%) !important;
        box-shadow: 0 8px 22px -2px rgba(37, 99, 235, 0.55), 0 4px 8px -2px rgba(37, 99, 235, 0.35) !important;
        transform: translateY(-2px) !important;
        color: #ffffff !important;
    }
    .btn-timbul-blue:active {
        transform: translateY(0) scale(0.98) !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4) !important;
    }
</style>
@endpush

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

    <div x-data="garmentConfigPage()" x-init="init()" class="space-y-6">

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
                :class="tab === 'categories' ? 'border-blue-600 text-blue-600 font-semibold' :
                    'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'">
                Kategori Baju
            </button>
            <button @click="tab = 'divisions'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
                :class="tab === 'divisions' ? 'border-blue-600 text-blue-600 font-semibold' :
                    'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'">
                Divisi &amp; Warna
            </button>
            <button @click="tab = 'tags'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
                :class="tab === 'tags' ? 'border-blue-600 text-blue-600 font-semibold' :
                    'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'">
                Tag RFID
            </button>
            <button @click="tab = 'garments'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
                :class="tab === 'garments' ? 'border-blue-600 text-blue-600 font-semibold' :
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
                        <p class="text-xs text-[var(--ink-soft)] mt-0.5">Jenis baju steril beserta batas maksimal siklus default (FR-04).</p>
                    </div>
                    <button @click="openCreateCategory()"
                        class="btn-timbul-blue text-xs px-4 py-2 rounded-xl gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Kategori</span>
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
                        <p class="text-xs text-[var(--ink-soft)] mt-0.5">Setiap divisi punya warna baju steril masing-masing meski jenis bajunya sama.</p>
                    </div>
                    <button @click="openCreateDivision()"
                        class="btn-timbul-blue text-xs px-4 py-2 rounded-xl gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Divisi</span>
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
                        class="btn-timbul-blue text-xs px-4 py-2 rounded-xl gap-2 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Tag</span>
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
                                    <td colspan="5" class="px-5 py-10 text-center text-[var(--ink-soft)] text-sm">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <p>Belum ada tag RFID terdaftar. Klik tombol di bawah untuk mulai.</p>
                                            <button type="button" @click="openCreateTag()"
                                                class="btn-timbul-blue text-xs px-3.5 py-1.5 rounded-xl gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                                <span>Tambah Tag</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Tag RFID --}}
                @if ($tags->hasPages())
                    <div class="p-4 border-t border-[var(--line)] flex flex-col sm:flex-row justify-between items-center gap-3 bg-gray-50/50">
                        <div class="text-xs text-[var(--ink-soft)] font-mono">
                            Menampilkan {{ $tags->firstItem() ?? 0 }} - {{ $tags->lastItem() ?? 0 }} dari {{ $tags->total() }} Tag
                        </div>
                        <div class="overflow-x-auto max-w-full">
                            {{ $tags->appends(['tab' => 'tags'])->links() }}
                        </div>
                    </div>
                @endif
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
                        class="btn-timbul-blue text-xs px-4 py-2 rounded-xl gap-2 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Garment</span>
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
                            @forelse (($garments ?? []) as $garment)
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
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <p>Belum ada garment aktif. Klik tombol di bawah untuk mulai.</p>
                                            <button type="button" @click="openCreateGarment()"
                                                class="btn-timbul-blue text-xs px-3.5 py-1.5 rounded-xl gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                                <span>Tambah Garment</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Garment --}}
                @if ($garments->hasPages())
                    <div class="p-4 border-t border-[var(--line)] flex flex-col sm:flex-row justify-between items-center gap-3 bg-gray-50/50">
                        <div class="text-xs text-[var(--ink-soft)] font-mono">
                            Menampilkan {{ $garments->firstItem() ?? 0 }} - {{ $garments->lastItem() ?? 0 }} dari {{ $garments->total() }} Garment
                        </div>
                        <div class="overflow-x-auto max-w-full">
                            {{ $garments->appends(['tab' => 'garments'])->links() }}
                        </div>
                    </div>
                @endif
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
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Deskripsi</label>
                            <textarea name="description" x-model="categoryForm.description" rows="2"
                                placeholder="Deskripsi singkat kategori baju ini"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Batas Maksimal Siklus
                                Default</label>
                            <input type="number" name="default_max_cycle" x-model="categoryForm.default_max_cycle"
                                required min="1" placeholder="mis. 200"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
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
                            class="btn-timbul-blue text-xs px-5 py-2.5 rounded-xl">
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
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Nama Warna</label>
                                <input type="text" name="color_name" x-model="divisionForm.color_name" required
                                    placeholder="mis. Biru"
                                    class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Kode Warna</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="color_hex_picker" x-model="divisionForm.color_hex"
                                        class="w-10 h-9 rounded-lg border border-[var(--line)] p-0.5 cursor-pointer">
                                    <input type="text" name="color_hex" x-model="divisionForm.color_hex" required
                                        placeholder="#2A5CAA"
                                        class="flex-1 text-sm px-3 py-2 rounded-lg border border-[var(--line)] font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Deskripsi</label>
                            <textarea name="description" x-model="divisionForm.description" rows="2"
                                placeholder="Deskripsi singkat divisi ini"
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20"></textarea>
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
                            class="btn-timbul-blue text-xs px-5 py-2.5 rounded-xl">
                            <span x-text="divisionFormMode === 'create' ? 'Simpan Divisi' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== MODAL: TAG RFID ===================== --}}
        <div x-show="tagModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
            style="background: rgba(20,24,34,.45);">
            <div @click.away="closeAllModals()" class="card w-full max-w-lg max-h-[92vh] overflow-y-auto flex flex-col my-auto shadow-2xl">
                <form :action="tagFormMode === 'create' ? '{{ route('admin.tags.store') }}' : tagEditActionUrl"
                    method="POST">
                    @csrf
                    <template x-if="tagFormMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
                        <h3 class="font-display font-semibold text-base"
                            x-text="tagFormMode === 'create' ? 'Tambah Tag RFID Baru' : 'Edit Tag RFID'"></h3>
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
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-medium text-[var(--ink-soft)]">Tag UID (EPC/TID)</label>
                                <div class="flex items-center gap-2" x-show="tagFormMode === 'create'">
                                    <button type="button" @click="testGenerateUid()"
                                        class="text-[10px] text-blue-600 hover:text-blue-800 underline font-mono">
                                        + Contoh UID Acak
                                    </button>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" id="tag_uid_input" name="tag_uid" x-model="tagForm.tag_uid" required
                                    @keydown.enter.prevent="if (tagForm.tag_uid) { setScannedTag(tagForm.tag_uid, 'Scanner / Keyboard'); }"
                                    :readonly="tagFormMode === 'edit'" placeholder="Scan reader atau ketik manual"
                                    class="flex-1 text-sm px-3 py-2 rounded-lg border border-[var(--line)] font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    :class="tagFormMode === 'edit' ? 'bg-[var(--copper-soft)]/40 cursor-not-allowed' : ''">
                                
                                <button type="button" @click="toggleScan()" x-show="tagFormMode === 'create'"
                                    class="shrink-0 text-xs font-semibold px-3.5 py-2 rounded-lg border transition flex items-center gap-1.5"
                                    :class="scanning ?
                                        'bg-rose-600 text-white border-rose-600 shadow-sm animate-pulse' :
                                        'border-blue-600 text-blue-600 hover:bg-blue-50'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75H6A2.25 2.25 0 0 0 3.75 6v2.25M8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25m12-12H18A2.25 2.25 0 0 1 20.25 6v2.25m-12 12H18A2.25 2.25 0 0 0 20.25 18v-2.25M9 12h6" />
                                    </svg>
                                    <span x-show="!scanning">Mulai Scan</span>
                                    <span x-show="scanning">Batal (<span x-text="scanCountdown"></span>s)</span>
                                </button>
                            </div>
                            <div class="mt-1.5 min-h-[20px]">
                                <template x-if="scanning && !tagForm.tag_uid">
                                    <p class="text-xs text-blue-700 font-medium flex items-center gap-1.5 animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                        <span>Menunggu scan tag di reader (<span x-text="scanCountdown"></span>s)...</span>
                                    </p>
                                </template>
                                <template x-if="tagForm.tag_uid">
                                    <p class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0 inline" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Tag UID terbaca: <strong class="font-bold text-slate-900 font-mono" x-text="tagForm.tag_uid"></strong></span>
                                        <span x-show="scannedFromDevice" class="text-slate-500 text-[11px] font-normal">(dari: <span x-text="scannedFromDevice"></span>)</span>
                                    </p>
                                </template>
                                <template x-if="!scanning && !tagForm.tag_uid">
                                    <p class="text-[11px] text-[var(--ink-soft)]">
                                        Klik "Mulai Scan" lalu dekatkan tag ke reader RFID, gunakan scanner USB, atau ketik UID manual.
                                    </p>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Divisi</label>
                            <select name="division_id" x-model="tagForm.division_id" required
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
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
                                            class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Rated Max
                                            Cycles</label>
                                        <input type="number" name="rated_max_cycles" x-model="tagForm.rated_max_cycles"
                                            min="1" placeholder="200"
                                            class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Manufaktur</label>
                                    <input type="text" name="manufacturer" x-model="tagForm.manufacturer"
                                        placeholder="mis. Alien Technology"
                                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Catatan</label>
                                    <textarea name="notes" x-model="tagForm.notes" rows="2"
                                        placeholder="Catatan opsional, mis. asal pembelian/batch"
                                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20"></textarea>
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Status</label>
                                    <select name="status" x-model="tagForm.status"
                                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                        <option value="active">Active</option>
                                        <option value="retired">Retired</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="lost">Lost</option>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t border-[var(--line)] bg-slate-50 flex items-center justify-end gap-3 rounded-b-xl sticky bottom-0">
                        <button type="button" @click="closeAllModals()"
                            class="text-xs font-medium px-4 py-2.5 rounded-lg border border-[var(--line)] bg-white text-slate-700 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" @click="stopScan()"
                            class="btn-timbul-blue text-xs px-5 py-2.5 rounded-xl gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span x-text="tagFormMode === 'create' ? 'Simpan Tag' : 'Simpan Perubahan'"></span>
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
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Kode Garment (Otomatis)</label>
                            <input type="text" name="garment_code" x-model="garmentForm.garment_code" readonly required
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] font-mono font-semibold bg-[var(--copper-soft)]/40 cursor-not-allowed text-[var(--ink)] select-none focus:outline-none">
                            <p class="text-[11px] text-[var(--ink-soft)] mt-1">Kode digenerate otomatis oleh sistem dan tidak dapat diubah.</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Kategori</label>
                            <select name="category_id" x-model="garmentForm.category_id" required
                                class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
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
                                    class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20"
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
                                    class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-[var(--ink-soft)] mb-1 block">Tag RFID</label>

                            <div class="relative" x-data="{ tagDropdownOpen: false }" @click.away="tagDropdownOpen = false">
                                <button type="button" @click="tagDropdownOpen = !tagDropdownOpen"
                                    class="w-full flex items-center justify-between gap-2 text-sm px-3 py-2 rounded-lg border border-[var(--line)] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
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
                            class="btn-timbul-blue text-xs px-5 py-2.5 rounded-xl">
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
                            class="text-xs font-semibold px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
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
                    nextGarmentCode: '{{ $nextGarmentCode ?? "GRM001" }}',
                    availableTagsList: @json($availableTagsForJs),
                    selectedTag() {
                        return this.availableTagsList.find(t => t.tag_id === this.garmentForm.tag_id) || null;
                    },

                    deleteModalOpen: false,
                    deleteActionUrl: '',

                    init() {
                        window.__garmentConfig = this;
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.has('tab') && ['categories', 'divisions', 'tags', 'garments'].includes(urlParams.get('tab'))) {
                            this.tab = urlParams.get('tab');
                        }

                        // Listener global untuk scanner RFID USB Keyboard Wedge
                        let scanBuffer = '';
                        let lastKeyTime = 0;

                        window.addEventListener('keydown', (e) => {
                            if (!this.tagModalOpen || this.tagFormMode !== 'create') return;
                            if (e.target && (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT')) return;

                            const now = Date.now();
                            if (now - lastKeyTime > 300) {
                                scanBuffer = '';
                            }
                            lastKeyTime = now;

                            if (e.key === 'Enter') {
                                if (scanBuffer.trim().length >= 3) {
                                    const uid = scanBuffer.trim();
                                    this.setScannedTag(uid, 'Scanner USB / Keyboard');
                                    e.preventDefault();
                                    scanBuffer = '';
                                } else if (this.tagForm.tag_uid && this.tagForm.tag_uid.trim().length >= 3) {
                                    this.setScannedTag(this.tagForm.tag_uid.trim(), 'Scanner USB / Keyboard');
                                    e.preventDefault();
                                }
                            } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                                scanBuffer += e.key;
                            }
                        });
                    },

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
                        this.scannedFromDevice = null;
                        this.tagModalOpen = true;
                        this.$nextTick(() => {
                            const input = document.getElementById('tag_uid_input');
                            if (input) input.focus();
                        });
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
                            garment_code: this.nextGarmentCode,
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

                    // ============ Set Tag Hasil Scan / Manual ============
                    setScannedTag(uid, source = 'Reader RFID') {
                        if (!uid) return;
                        const cleanUid = String(uid).trim().toUpperCase();
                        this.tagForm.tag_uid = cleanUid;
                        this.scannedFromDevice = source;

                        const input = document.getElementById('tag_uid_input');
                        if (input) {
                            input.value = cleanUid;
                        }

                        this.stopScan();

                        this.$nextTick(() => {
                            const inputElem = document.getElementById('tag_uid_input');
                            if (inputElem) {
                                inputElem.value = cleanUid;
                                inputElem.focus();
                            }
                        });
                    },

                    testGenerateUid() {
                        const hex = 'E2801130' + Array.from({length: 16}, () => Math.floor(Math.random()*16).toString(16).toUpperCase()).join('');
                        this.setScannedTag(hex, 'Simulasi Tag Test');
                    },

                    // ============ Scan RFID via ESP32 / USB Scanner ============
                    toggleScan() {
                        this.scanning ? this.stopScan() : this.startScan();
                    },
                    async startScan() {
                        this.scanning = true;
                        this.scanCountdown = 60;
                        this.scannedFromDevice = null;
                        this.tagForm.tag_uid = '';

                        this.$nextTick(() => {
                            const input = document.getElementById('tag_uid_input');
                            if (input) {
                                input.value = '';
                                input.focus();
                            }
                        });

                        try {
                            await fetch(`{{ route('admin.tag-registration.start') }}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                        } catch (e) {
                            console.error('startListening error:', e);
                        }

                        if (this.scanPollTimer) clearInterval(this.scanPollTimer);
                        if (this.scanCountdownTimer) clearInterval(this.scanCountdownTimer);

                        this.scanPollTimer = setInterval(() => this.pollScan(), 800);
                        this.scanCountdownTimer = setInterval(() => {
                            this.scanCountdown--;
                            if (this.scanCountdown <= 0) this.stopScan();
                        }, 1000);
                    },
                    async pollScan() {
                        try {
                            const res = await fetch(`{{ route('admin.tag-registration.poll') }}`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            if (!res.ok) return;
                            const data = await res.json();
                            const detectedUid = data.tag_uid ?? data.uid ?? null;
                            if (detectedUid) {
                                this.setScannedTag(detectedUid, data.device_name || 'Reader ESP32');
                            }
                        } catch (e) {
                            console.error('pollScan error:', e);
                        }
                    },
                    stopScan() {
                        if (!this.scanning && !this.scanPollTimer) return;
                        this.scanning = false;
                        if (this.scanPollTimer) {
                            clearInterval(this.scanPollTimer);
                            this.scanPollTimer = null;
                        }
                        if (this.scanCountdownTimer) {
                            clearInterval(this.scanCountdownTimer);
                            this.scanCountdownTimer = null;
                        }

                        fetch(`{{ route('admin.tag-registration.stop') }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).catch(() => {});
                    }
                }
            }
        </script>
    @endpush

@endsection
