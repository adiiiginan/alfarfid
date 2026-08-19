@extends('layouts.admin')

@section('title', 'Tag Reassignment')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

        {{-- ================= HEADER ================= --}}
        <div>
            <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1">FR-19 &middot; Should Have</p>
            <h1 class="font-display font-semibold text-2xl text-slate-900">Tag Reassignment</h1>
            <p class="text-sm text-slate-500 mt-1">
                Skenario A: baju rusak, tag masih awet &rarr; pindahkan tag ke baju baru (siklus baju baru direset ke 0).
                Skenario B: baju masih bagus, tag sudah aus &rarr; ganti tag baju ini dengan tag baru (siklus baju TIDAK
                direset).
                Sistem mendeteksi otomatis dari pilihan kamu di bawah.
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

        {{-- ================= FORM REASSIGNMENT ================= --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-display font-semibold text-slate-900 text-base">Proses Rebind Tag</h2>
            </div>

            <form method="POST" action="{{ route('admin.tag-reassignment.store') }}" class="px-6 py-5 space-y-5"
                id="reassignForm">
                @csrf

                <div class="grid sm:grid-cols-2 gap-5">
                    {{-- Pilih Tag --}}
                    <div>
                        <label for="tag_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tag RFID
                        </label>
                        <select name="tag_id" id="tag_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('tag_id') border-red-400 @enderror">
                            <option value="">— Pilih tag —</option>
                            @foreach ($availableTags as $tag)
                                <option value="{{ $tag->tag_id }}" data-total-used="{{ $tag->total_cycles_used }}"
                                    data-rated-max="{{ $tag->rated_max_cycles }}"
                                    {{ old('tag_id') === $tag->tag_id ? 'selected' : '' }}>
                                    {{ $tag->tag_uid }} &middot; {{ $tag->total_cycles_used }}/{{ $tag->rated_max_cycles }}
                                    siklus
                                </option>
                            @endforeach
                        </select>
                        <p id="tagWearHint" class="mt-1.5 text-xs text-slate-400 hidden"></p>
                    </div>

                    {{-- Baju tujuan --}}
                    <div>
                        <label for="new_garment_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Baju tujuan
                        </label>
                        <select name="new_garment_id" id="new_garment_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('new_garment_id') border-red-400 @enderror">
                            <option value="">— Pilih baju tujuan —</option>
                            @foreach ($eligibleGarments as $garment)
                                <option value="{{ $garment->garment_id }}"
                                    data-current-tag-uid="{{ $garment->currentTag->tag_uid ?? '' }}"
                                    {{ old('new_garment_id') === $garment->garment_id ? 'selected' : '' }}>
                                    {{ $garment->garment_code }} &middot; ukuran {{ $garment->size ?? '-' }}
                                    @if ($garment->currentTag)
                                        &middot; tag saat ini: {{ $garment->currentTag->tag_uid }}
                                    @else
                                        &middot; belum ada tag
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ── PREVIEW OTOMATIS SKENARIO ── --}}
                <div id="scenarioPreview" class="hidden rounded-lg border px-4 py-3 text-sm flex items-start gap-3">
                    <svg id="scenarioIcon" class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24"></svg>
                    <span id="scenarioText"></span>
                </div>

                {{-- Alasan unbind --}}
                <div>
                    <label for="unbind_reason" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Alasan pelepasan tag/baju lama <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="unbind_reason" id="unbind_reason" maxlength="100"
                        value="{{ old('unbind_reason') }}"
                        placeholder="mis. Baju retired karena mencapai batas siklus / tag sudah aus"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <p class="text-xs text-slate-400 max-w-sm">
                        Proses ini tercatat di Audit Trail dan tidak bisa dibatalkan begitu tersimpan.
                    </p>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-[var(--copper)] px-5 py-2.5 text-sm font-medium text-white hover:opacity-90 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M17 2.5 21 6l-4 3.5M21 6H8a5 5 0 0 0-5 5" />
                            <path d="M7 21.5 3 18l4-3.5M3 18h13a5 5 0 0 0 5-5" />
                        </svg>
                        Proses
                    </button>
                </div>
            </form>
        </div>

        {{-- ================= RIWAYAT REASSIGNMENT ================= --}}
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
                                    {{ $binding->unbound_at ? $binding->unbound_at->format('d M Y H:i') : '—' }}
                                </td>
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
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400">
                                    Belum ada histori binding tag.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($history->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Data baju asal per tag_id, disiapkan dari server supaya json directive
            // cuma menerima 1 variabel simpel tanpa koma di dalamnya.
            const currentBindings = @json($currentBindings);

            const tagSelect = document.getElementById('tag_id');
            const garmentSelect = document.getElementById('new_garment_id');
            const tagWearHint = document.getElementById('tagWearHint');
            const scenarioPreview = document.getElementById('scenarioPreview');
            const scenarioIcon = document.getElementById('scenarioIcon');
            const scenarioText = document.getElementById('scenarioText');

            const ICON_INFO =
                '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25h.008v.008h-.008v-.008ZM12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM11.25 15h.008v.008h-.008V15Zm.375-6.375a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />';

            function renderTagWear() {
                const selectedOption = tagSelect.options[tagSelect.selectedIndex];
                if (!tagSelect.value) {
                    tagWearHint.classList.add('hidden');
                    return;
                }
                const totalUsed = Number(selectedOption.dataset.totalUsed);
                const ratedMax = Number(selectedOption.dataset.ratedMax);
                const ratio = ratedMax > 0 ? totalUsed / ratedMax : 0;

                if (ratio >= 0.9) {
                    tagWearHint.textContent =
                        `Perhatian: tag ini sudah ${totalUsed}/${ratedMax} siklus, mendekati/melewati batas umur fisik.`;
                    tagWearHint.classList.remove('hidden', 'text-slate-400');
                    tagWearHint.classList.add('text-amber-600');
                } else {
                    tagWearHint.classList.add('hidden');
                }
            }

            function renderScenarioPreview() {
                const tagId = tagSelect.value;
                const garmentOption = garmentSelect.options[garmentSelect.selectedIndex];
                const garmentId = garmentSelect.value;

                if (!tagId || !garmentId) {
                    scenarioPreview.classList.add('hidden');
                    return;
                }

                const oldGarmentCode = currentBindings[tagId]; // baju asal tag ini (kalau ada)
                const targetCurrentTagUid = garmentOption.dataset.currentTagUid || null;

                scenarioPreview.classList.remove('hidden');

                if (oldGarmentCode && !targetCurrentTagUid) {
                    // SKENARIO A
                    scenarioPreview.className =
                        'rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm flex items-start gap-3 text-blue-800';
                    scenarioIcon.innerHTML = ICON_INFO;
                    scenarioText.innerHTML =
                        `<strong>Skenario A — Pindah tag:</strong> baju <strong>${oldGarmentCode}</strong> akan diarsipkan (retired) karena kehilangan tag ini. Baju tujuan dianggap baju baru, siklusnya akan <strong>direset ke 0</strong>.`;
                } else if (!oldGarmentCode && targetCurrentTagUid) {
                    // SKENARIO B
                    scenarioPreview.className =
                        'rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm flex items-start gap-3 text-amber-800';
                    scenarioIcon.innerHTML = ICON_INFO;
                    scenarioText.innerHTML =
                        `<strong>Skenario B — Ganti tag:</strong> tag lama (<strong>${targetCurrentTagUid}</strong>) di baju ini akan diarsipkan (retired) karena aus. Siklus baju <strong>TIDAK direset</strong> (baju yang sama).`;
                } else if (oldGarmentCode && targetCurrentTagUid) {
                    // KOMBINASI — akan ditolak backend
                    scenarioPreview.className =
                        'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm flex items-start gap-3 text-red-800';
                    scenarioIcon.innerHTML = ICON_INFO;
                    scenarioText.innerHTML =
                        `<strong>Tidak bisa diproses:</strong> tag ini sedang terpasang di baju lain DAN baju tujuan juga sudah punya tag lain. Selesaikan salah satu dulu secara terpisah.`;
                } else {
                    // Binding pertama kali (baju & tag sama-sama belum terpasang)
                    scenarioPreview.className =
                        'rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm flex items-start gap-3 text-slate-600';
                    scenarioIcon.innerHTML = ICON_INFO;
                    scenarioText.innerHTML =
                        `Tag akan dipasang langsung ke baju ini. Tidak ada tag/baju lama yang perlu diarsipkan.`;
                }
            }

            tagSelect.addEventListener('change', () => {
                renderTagWear();
                renderScenarioPreview();
            });
            garmentSelect.addEventListener('change', renderScenarioPreview);

            renderTagWear();
            renderScenarioPreview();
        </script>
    @endpush
@endsection
