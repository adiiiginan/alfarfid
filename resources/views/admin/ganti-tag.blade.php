@extends('layouts.admin')

@section('title', 'Ganti Tag')

@section('content')
    <div class="px-6 py-8 space-y-8">

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

        {{-- ================= FORM GANTI TAG ================= --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-display font-semibold text-slate-900 text-base">Pasang Tag Baru ke Baju Ini</h2>
            </div>

            <form method="POST" action="{{ route('admin.ganti-tag.store') }}" class="px-6 py-5 space-y-5"
                id="gantiTagForm">
                @csrf

                {{-- STEP 1: Pilih baju --}}
                <div>
                    <label for="garment_id" class="block text-sm font-medium text-slate-700 mb-1.5">1. Pilih baju</label>
                    <select name="garment_id" id="garment_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent @error('garment_id') border-red-400 @enderror">
                        <option value="">— Pilih baju —</option>
                        @forelse ($eligibleGarments as $garment)
                            {{--
                                PERUBAHAN: currentTag sudah dianotasi di
                                TagReassignmentController@indexGanti (annotateTagWear),
                                jadi remaining/wear-percent/near-eol/over-limit tinggal
                                dipakai langsung, satu sumber kebenaran dengan halaman
                                Pindah Tag & Analytics (ambang 90% yang sama).

                                Baju yang tag-nya sudah near-end-of-life MEMANG kandidat
                                utama alur ini — beri tanda supaya operator langsung lihat
                                baju mana yang paling butuh diganti tag-nya duluan.
                            --}}
                            <option value="{{ $garment->garment_id }}"
                                data-tag-uid="{{ $garment->currentTag->tag_uid ?? '' }}"
                                data-tag-used="{{ $garment->currentTag->total_cycles_used ?? 0 }}"
                                data-tag-max="{{ $garment->currentTag->rated_max_cycles ?? 0 }}"
                                data-tag-remaining="{{ $garment->currentTag->remaining_cycles ?? '' }}"
                                data-tag-wear-percent="{{ $garment->currentTag->wear_percent ?? '' }}"
                                data-tag-near-eol="{{ $garment->currentTag->is_near_end_of_life ?? false ? 'true' : 'false' }}"
                                data-tag-over-limit="{{ $garment->currentTag->is_over_limit ?? false ? 'true' : 'false' }}"
                                data-division-id="{{ $garment->division_id }}"
                                {{ old('garment_id') === $garment->garment_id ? 'selected' : '' }}>
                                @if ($garment->currentTag->is_over_limit ?? false)
                                    🔴
                                @elseif ($garment->currentTag->is_near_end_of_life ?? false)
                                    ⚠
                                @endif
                                {{ $garment->garment_code }} &middot; ukuran {{ $garment->size ?? '-' }}
                                &middot; tag saat ini: {{ $garment->currentTag->tag_uid ?? '-' }}
                                @if ($garment->currentTag)
                                    ({{ $garment->currentTag->total_cycles_used }}/{{ $garment->currentTag->rated_max_cycles }}
                                    siklus)
                                @endif
                            </option>
                        @empty
                            <option value="" disabled>Tidak ada baju yang punya tag aktif.</option>
                        @endforelse
                    </select>
                    <p id="oldTagHint" class="mt-1.5 text-xs hidden"></p>
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
            const garmentSelect = document.getElementById('garment_id');
            const oldTagHint = document.getElementById('oldTagHint');
            const newTagUidInput = document.getElementById('new_tag_uid');
            const scanStatus = document.getElementById('scanStatus');
            const divisionSelect = document.getElementById('division_id');
            const scanBtn = document.getElementById('scanBtn');
            const scanBtnLabel = document.getElementById('scanBtnLabel');
            const manualToggle = document.getElementById('manualToggle');

            // Ambil token langsung dari hidden input @csrf yang sudah pasti ada
            // di form ini — tidak gantung ke <meta name="csrf-token"> di layout
            // yang belum tentu tersedia (kemungkinan besar ini penyebab request
            // "Mulai Scan" gagal 419 dan startListening() tidak pernah kepanggil).
            const csrfToken = document.querySelector('#gantiTagForm input[name="_token"]').value;

            let pollTimer = null;
            let timeoutTimer = null;
            let scanning = false;

            const POLL_INTERVAL_MS = 1500;
            const SCAN_TIMEOUT_MS = 30000; // 30 detik, sesuaikan kalau ternyata beda dengan modal Input Tag Baru

            // ── PILIH BAJU ──────────────────────────────────────────
            function renderOldTagHint() {
                const opt = garmentSelect.options[garmentSelect.selectedIndex];
                if (!garmentSelect.value) {
                    oldTagHint.classList.add('hidden');
                    scanBtn.disabled = true;
                    return;
                }
                const tagUid = opt.dataset.tagUid;
                const used = Number(opt.dataset.tagUsed);
                const max = Number(opt.dataset.tagMax);
                const remaining = opt.dataset.tagRemaining;
                const isOverLimit = opt.dataset.tagOverLimit === 'true';
                const isNearEol = opt.dataset.tagNearEol === 'true';

                // PERUBAHAN: warnai hint sesuai keausan tag yang sedang
                // terpasang. Baju dengan tag near-end-of-life / over-limit
                // justru pas untuk alur "Ganti Tag" ini — bukan halangan,
                // cuma penekanan supaya operator sadar ini prioritas.
                if (isOverLimit) {
                    oldTagHint.innerHTML =
                        `Tag saat ini: <strong>${tagUid}</strong> (${used}/${max} siklus) — sudah melewati batas siklus maksimal. Pas sekali diganti sekarang.`;
                    oldTagHint.className = 'mt-1.5 text-xs font-medium text-red-600';
                } else if (isNearEol) {
                    oldTagHint.innerHTML =
                        `Tag saat ini: <strong>${tagUid}</strong> (${used}/${max} siklus, sisa ${remaining}) — mendekati batas siklus, disarankan diganti sekarang.`;
                    oldTagHint.className = 'mt-1.5 text-xs text-amber-600';
                } else {
                    oldTagHint.innerHTML =
                        `Tag saat ini: <strong>${tagUid}</strong> (${used}/${max} siklus) — akan diarsipkan (retired) setelah tag baru terpasang.`;
                    oldTagHint.className = 'mt-1.5 text-xs text-blue-600';
                }
                oldTagHint.classList.remove('hidden');

                if (opt.dataset.divisionId && !divisionSelect.value) {
                    divisionSelect.value = opt.dataset.divisionId;
                }

                scanBtn.disabled = false;
                if (!scanning) {
                    scanStatus.textContent = 'Baju dipilih. Tekan Mulai Scan lalu tempelkan tag baru ke reader.';
                    scanStatus.className = 'text-xs text-slate-400';
                }
            }

            garmentSelect.addEventListener('change', renderOldTagHint);
            renderOldTagHint();

            // ── SCAN VIA READER (start/poll/stop) ──────────────────
            async function startScan() {
                if (!garmentSelect.value) {
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

                    // ── ASUMSI BENTUK RESPONSE ──────────────────────
                    // Belum lihat isi ScanEventController@pollScan, jadi dicoba
                    // 2 kemungkinan nama field paling umum. Sesuaikan baris ini
                    // begitu bentuk response aslinya dikonfirmasi.
                    const detectedUid = data.tag_uid ?? data.uid ?? null;
                    // ─────────────────────────────────────────────────

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
