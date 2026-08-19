@extends('layouts.admin')

@section('title', 'Pindah Tag')

@section('content')
    <div class="px-6 py-8 space-y-8">

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

        {{-- ================= FORM PINDAH TAG ================= --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-display font-semibold text-slate-900 text-base">Pindahkan Tag ke Baju Baru</h2>
            </div>

            <form method="POST" action="{{ route('admin.pindah-tag.store') }}" class="px-6 py-5 space-y-5"
                id="pindahTagForm">
                @csrf

                <div class="grid sm:grid-cols-2 gap-5">
                    {{-- Pilih Tag lama --}}
                    <div>
                        <label for="tag_id" class="block text-sm font-medium text-slate-700 mb-1.5">Tag lama (yang mau
                            dipindah)</label>
                        <select name="tag_id" id="tag_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('tag_id') border-red-400 @enderror">
                            <option value="">— Pilih tag —</option>
                            @foreach ($availableTags as $tag)
                                {{--
                                    PERUBAHAN: total_cycles_used vs rated_max_cycles beda skala
                                    dengan siklus baju (200 vs 50) — dianotasi di
                                    TagReassignmentController@annotateTagWear supaya threshold
                                    "mendekati/lewat batas" satu sumber kebenaran (tidak dihitung
                                    ulang di JS dengan angka ambang yang bisa beda-beda).

                                    Tag yang sudah over_limit tetap ditampilkan (supaya kelihatan
                                    statusnya) tapi option-nya di-disable, karena
                                    proses_pindah_tag di DB memang akan menolaknya — daripada
                                    operator submit dulu baru kena error dari server.
                                --}}
                                <option value="{{ $tag->tag_id }}" data-total-used="{{ $tag->total_cycles_used }}"
                                    data-rated-max="{{ $tag->rated_max_cycles }}"
                                    data-remaining="{{ $tag->remaining_cycles }}"
                                    data-wear-percent="{{ $tag->wear_percent }}"
                                    data-near-eol="{{ $tag->is_near_end_of_life ? 'true' : 'false' }}"
                                    data-over-limit="{{ $tag->is_over_limit ? 'true' : 'false' }}"
                                    {{ $tag->is_over_limit ? 'disabled' : '' }}
                                    {{ old('tag_id') === $tag->tag_id ? 'selected' : '' }}>
                                    @if ($tag->is_over_limit)
                                        🔴
                                    @elseif ($tag->is_near_end_of_life)
                                        ⚠
                                    @endif
                                    {{ $tag->tag_uid }} &middot;
                                    {{ $tag->total_cycles_used }}/{{ $tag->rated_max_cycles }} siklus
                                    @if ($tag->is_over_limit)
                                        (batas terlampaui, tidak bisa dipindah)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p id="tagOriginHint" class="mt-1.5 text-xs text-slate-400 hidden"></p>
                        <p id="tagWearHint" class="mt-1.5 text-xs hidden"></p>
                    </div>

                    {{-- Baju tujuan (baru, belum ada tag) --}}
                    <div>
                        <label for="new_garment_id" class="block text-sm font-medium text-slate-700 mb-1.5">Baju tujuan
                            (baru)</label>
                        <select name="new_garment_id" id="new_garment_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('new_garment_id') border-red-400 @enderror">
                            <option value="">— Pilih baju tujuan —</option>
                            @forelse ($eligibleGarments as $garment)
                                <option value="{{ $garment->garment_id }}"
                                    {{ old('new_garment_id') === $garment->garment_id ? 'selected' : '' }}>
                                    {{ $garment->garment_code }} &middot; ukuran {{ $garment->size ?? '-' }}
                                </option>
                            @empty
                                <option value="" disabled>Tidak ada baju yang belum punya tag. Buat dulu lewat Tambah
                                    Garment Baru.</option>
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

                {{-- Preview --}}
                <div id="scenarioPreview"
                    class="hidden rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm flex items-start gap-3 text-blue-800">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.25 11.25h.008v.008h-.008v-.008ZM12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM11.25 15h.008v.008h-.008V15Zm.375-6.375a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    <span id="scenarioText"></span>
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
            const currentBindings = @json($currentBindings);

            const tagSelect = document.getElementById('tag_id');
            const garmentSelect = document.getElementById('new_garment_id');
            const tagOriginHint = document.getElementById('tagOriginHint');
            const tagWearHint = document.getElementById('tagWearHint');
            const scenarioPreview = document.getElementById('scenarioPreview');
            const scenarioText = document.getElementById('scenarioText');
            const submitBtn = document.getElementById('submitBtn');

            function renderTagHints() {
                const opt = tagSelect.options[tagSelect.selectedIndex];
                if (!tagSelect.value) {
                    tagOriginHint.classList.add('hidden');
                    tagWearHint.classList.add('hidden');
                    submitBtn.disabled = false;
                    return;
                }

                const originCode = currentBindings[tagSelect.value];
                if (originCode) {
                    tagOriginHint.textContent =
                        `Tag ini sekarang terpasang di baju ${originCode} — baju itu akan diarsipkan (retired).`;
                    tagOriginHint.classList.remove('hidden');
                } else {
                    tagOriginHint.textContent = 'Tag ini sedang tidak terpasang ke baju mana pun.';
                    tagOriginHint.classList.remove('hidden');
                }

                // PERUBAHAN: pakai flag yang sudah dihitung server-side
                // (annotateTagWear di controller), bukan hitung ulang ratio
                // di JS — supaya ambang batas "mendekati/lewat" konsisten
                // dengan yang dipakai di proses_pindah_tag & Analytics.
                const remaining = Number(opt.dataset.remaining);
                const wearPercent = opt.dataset.wearPercent;
                const isOverLimit = opt.dataset.overLimit === 'true';
                const isNearEol = opt.dataset.nearEol === 'true';

                if (isOverLimit) {
                    tagWearHint.textContent =
                        `Tag ini sudah melewati batas siklus maksimal (${wearPercent}%). Tidak bisa dipindahkan — pilih tag lain, lalu retired-kan tag ini.`;
                    tagWearHint.className = 'mt-1.5 text-xs font-medium text-red-600';
                    tagWearHint.classList.remove('hidden');
                    submitBtn.disabled = true;
                } else if (isNearEol) {
                    tagWearHint.textContent =
                        `Perhatian: tag ini sudah ${wearPercent}% dari batas siklus (sisa ${remaining} siklus). Masih boleh dipindah, tapi pantau terus.`;
                    tagWearHint.className = 'mt-1.5 text-xs text-amber-600';
                    tagWearHint.classList.remove('hidden');
                    submitBtn.disabled = false;
                } else {
                    tagWearHint.classList.add('hidden');
                    submitBtn.disabled = false;
                }
            }

            function renderScenarioPreview() {
                if (!tagSelect.value || !garmentSelect.value) {
                    scenarioPreview.classList.add('hidden');
                    return;
                }
                const garmentCode = garmentSelect.options[garmentSelect.selectedIndex].text.split('·')[0].trim();
                const tagLabel = tagSelect.options[tagSelect.selectedIndex].text.split('·')[0].trim();
                scenarioText.innerHTML =
                    `Tag <strong>${tagLabel}</strong> akan dipindah ke baju <strong>${garmentCode}</strong>. Siklus baju tujuan direset ke 0.`;
                scenarioPreview.classList.remove('hidden');
            }

            tagSelect.addEventListener('change', () => {
                renderTagHints();
                renderScenarioPreview();
            });
            garmentSelect.addEventListener('change', renderScenarioPreview);

            renderTagHints();
            renderScenarioPreview();
        </script>
    @endpush
@endsection
