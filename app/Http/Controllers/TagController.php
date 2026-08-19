<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Division;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /**
     * Nilai default untuk tag baru. Diletakkan di sini (bukan di form)
     * supaya operator tidak perlu isi manual — cukup scan/ketik Tag UID
     * dan pilih Divisi. Kalau nanti nilai default ini perlu diubah,
     * cukup ubah di satu tempat ini.
     */
    private const DEFAULT_TAG_TYPE = 'UHF';
    private const DEFAULT_MANUFACTURER = 'Alien Technology';
    private const DEFAULT_RATED_MAX_CYCLES = 200;

    /**
     * Simpan tag RFID baru sekaligus assign ke divisi.
     * Dipanggil dari form "Input Tag Baru" di halaman Garment & Tag.
     *
     * Catatan: tag_type, manufacturer, dan rated_max_cycles TIDAK lagi
     * diambil dari input form saat create — nilainya di-hardcode di
     * konstanta di atas. Field-field ini hanya bisa diubah lewat form
     * Edit Tag (lihat method update()).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => ['required', 'string', 'max:64', Rule::unique('tags', 'tag_uid')],
            'division_id' => ['required', 'string', Rule::exists('divisions', 'division_id')],
        ], [
            'tag_uid.required' => 'Tag UID wajib diisi (scan atau input manual).',
            'tag_uid.unique' => 'Tag UID ini sudah terdaftar di sistem.',
            'division_id.required' => 'Divisi wajib dipilih.',
            'division_id.exists' => 'Divisi tidak ditemukan.',
        ]);

        $tag = Tag::create([
            'tag_uid' => $validated['tag_uid'],
            'division_id' => $validated['division_id'],
            'tag_type' => self::DEFAULT_TAG_TYPE,
            'manufacturer' => self::DEFAULT_MANUFACTURER,
            'rated_max_cycles' => self::DEFAULT_RATED_MAX_CYCLES,
            'notes' => null,
            'status' => 'active',
            'date_first_deployed' => now()->toDateString(),
        ]);

        AuditLog::create([
            'table_name' => 'tags',
            'record_id' => $tag->tag_id,
            'action' => 'insert',
            'new_value' => json_encode($tag->toArray()),
            'changed_by' => $request->user()?->user_id,
        ]);

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', "Tag {$tag->tag_uid} berhasil didaftarkan untuk divisi {$tag->division->division_name}.");
    }

    /**
     * Endpoint AJAX opsional: cek ketersediaan tag_uid secara real-time
     * saat operator scan/ketik di form, sebelum submit.
     */
    public function checkUid(Request $request)
    {
        $request->validate(['tag_uid' => ['required', 'string', 'max:64']]);

        $exists = Tag::where('tag_uid', $request->tag_uid)->exists();

        return response()->json([
            'available' => ! $exists,
        ]);
    }

    /**
     * Update tag yang sudah ada. Di sini SEMUA field (termasuk tag_type,
     * manufacturer, rated_max_cycles, notes, status) tetap bisa diedit
     * manual lewat form Edit Tag — hardcode default HANYA berlaku saat
     * create (lihat method store() di atas).
     */
    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $validated = $request->validate([
            'division_id' => ['required', 'string', Rule::exists('divisions', 'division_id')],
            'tag_type' => ['nullable', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'rated_max_cycles' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'retired', 'damaged', 'lost'])],
        ]);

        $old = $tag->getOriginal();
        $tag->update($validated);

        AuditLog::create([
            'table_name' => 'tags',
            'record_id' => $tag->tag_id,
            'action' => 'update',
            'old_value' => json_encode($old),
            'new_value' => json_encode($tag->toArray()),
            'changed_by' => $request->user()?->user_id,
        ]);

        return back()->with('success', 'Tag berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        // Cegah hapus tag yang masih terpasang aktif ke baju
        if ($tag->currentGarment()->exists()) {
            return back()->with('error', 'Tag ini masih terpasang ke baju aktif, lepas dulu sebelum dihapus.');
        }

        $tag->delete();

        return back()->with('success', 'Tag berhasil dihapus.');
    }
}
