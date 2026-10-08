{{-- Modal "Input Tag Baru" — sertakan partial ini di halaman admin.garment-config.index --}}
{{-- Butuh Alpine.js (biasanya sudah ikut Laravel Breeze/Jetstream/starter kit Tailwind) --}}

<div x-data="{ open: false }" x-cloak>

    {{-- Tombol pemicu — taruh di header halaman Garment & Tag --}}
    <button @click="open = true" type="button"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-[var(--copper)] hover:opacity-90 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14" />
        </svg>
        Input Tag Baru
    </button>

    {{-- Overlay + modal --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        style="display: none;">
        <div @click.outside="open = false" class="w-full max-w-md rounded-xl bg-[#1b1b1f] border border-white/10 p-6">

            <div class="flex items-center justify-between mb-5">
                <h3 class="font-display font-semibold text-white text-base">Input Tag RFID Baru</h3>
                <button @click="open = false" class="text-white/40 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 6l12 12M18 6l-12 12" />
                    </svg>
                </button>
            </div>

            @if ($errors->any())
                <div
                    class="mb-4 px-3 py-2 rounded-lg bg-[var(--red)]/10 border border-[var(--red)]/30 text-xs text-[var(--red)]">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.tags.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-white/60 mb-1.5">Tag UID</label>
                    <input type="text" name="tag_uid" required value="{{ old('tag_uid') }}"
                        placeholder="Scan reader atau ketik manual"
                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-[var(--copper)]">
                    <p class="mt-1 text-[10px] text-white/35">
                        Mode reader: pastikan reader dalam mode "registrasi", bukan mode titik scan autoclave,
                        supaya tidak ikut tercatat sebagai siklus.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-white/60 mb-1.5">Divisi</label>
                    <select name="division_id" required
                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-[var(--copper)]">
                        <option value="" disabled selected>Pilih divisi</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->division_id }}"
                                {{ old('division_id') == $division->division_id ? 'selected' : '' }}>
                                {{ $division->division_name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Preview badge warna tiap divisi, untuk referensi visual operator --}}
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($divisions as $division)
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px]"
                                style="background: {{ $division->color_hex }}22; color: {{ $division->color_hex }};">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    style="background: {{ $division->color_hex }};"></span>
                                {{ $division->division_name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-white/60 mb-1.5">Tipe tag</label>
                        <input type="text" name="tag_type" value="{{ old('tag_type') }}" placeholder="mis. NFC, UHF"
                            class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-[var(--copper)]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white/60 mb-1.5">Rated max cycles</label>
                        <input type="number" name="rated_max_cycles" value="{{ old('rated_max_cycles', 200) }}"
                            min="1"
                            class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-[var(--copper)]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-white/60 mb-1.5">Catatan (opsional)</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-[var(--copper)]">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 rounded-lg text-sm text-white/60 hover:text-white">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-[var(--copper)] hover:opacity-90">
                        Daftarkan Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
