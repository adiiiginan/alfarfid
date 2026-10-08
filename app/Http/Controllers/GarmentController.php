<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Division;
use App\Models\Garment;
use App\Models\GarmentCategory;
use App\Models\Tag;
use App\Models\TagGarmentBinding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GarmentController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tag::with(['division', 'currentBinding.garment'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'tags_page', (int) $request->input('tags_page', 1))
            ->withQueryString();

        $garments = Garment::with(['category', 'division', 'currentTag.division'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'garments_page', (int) $request->input('garments_page', 1))
            ->withQueryString();

        return view('admin.garment-config', [
            'garments'        => $garments,
            'categories'      => GarmentCategory::withCount('garments')->orderBy('category_name')->get(),
            'divisions'       => Division::orderBy('division_name')->get(),
            'tags'            => $tags,
            'nextGarmentCode' => Garment::generateNextCode(),
            'availableTags'   => Tag::with('division')
                ->where('status', 'active')
                ->whereDoesntHave('currentBinding')
                ->orderBy('tag_uid')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        // ── PERUBAHAN ──────────────────────────────────────────
        // Modal "Tambah Garment" mengirim tag_id lewat
        // <input type="hidden" name="tag_id" :value="garmentForm.tag_id">
        // dan division_id lewat <select> dengan <option value="">.
        // Kalau admin tidak memilih apa-apa, yang terkirim ke server
        // adalah STRING KOSONG (""), bukan null asli.
        //
        // Normalisasi eksplisit di sini:
        $request->merge([
            'garment_code' => $request->input('garment_code') ?: Garment::generateNextCode(),
            'tag_id'       => $request->input('tag_id') ?: null,
            'division_id'  => $request->input('division_id') ?: null,
        ]);
        // ─────────────────────────────────────────────────────

        $validated = $request->validate([
            'garment_code'    => ['required', 'string', 'max:50', Rule::unique('garments', 'garment_code')],
            'category_id'     => ['required', 'string', Rule::exists('garment_categories', 'category_id')],
            'division_id'     => ['nullable', 'string', Rule::exists('divisions', 'division_id')],
            'size'            => ['nullable', 'string', 'max:20'],
            'max_cycle_limit' => ['nullable', 'integer', 'min:1'],
            // tag_id nullable — baju boleh dibuat dulu tanpa tag (nanti
            // dipasangi tag lewat alur "Pindah Tag" di Tag Reassignment).
            // Kalau diisi, tetap harus tag yang valid & tersedia.
            'tag_id'          => ['nullable', 'string', Rule::exists('tags', 'tag_id')],
        ], [
            'garment_code.unique'   => 'Kode garment ini sudah terdaftar.',
            'category_id.required' => 'Kategori baju wajib dipilih.',
        ]);

        $garment = DB::transaction(function () use ($validated, $request) {
            $tag = null;

            // Blok pengecekan & binding tag cuma jalan kalau tag_id memang
            // diisi. Kalau dikosongkan, baju dibuat begitu saja dengan
            // current_tag_id = NULL.
            if (!empty($validated['tag_id'])) {
                $tag = Tag::where('tag_id', $validated['tag_id'])->lockForUpdate()->firstOrFail();

                if ($tag->status !== 'active') {
                    abort(422, "Tag {$tag->tag_uid} berstatus '{$tag->status}', tidak bisa dipasang ke garment.");
                }

                if ($tag->currentBinding()->exists()) {
                    abort(422, "Tag {$tag->tag_uid} sudah terpasang ke garment lain.");
                }
            }

            $category = GarmentCategory::findOrFail($validated['category_id']);

            // ── PERUBAHAN ──────────────────────────────────────────
            // Kalau garment dipasangi tag, divisi HARUS ikut divisi
            // tag itu — diambil langsung dari DB di sini, bukan dari
            // division_id yang dikirim client. Ini menutup celah kalau
            // data tag di sisi frontend (Alpine, di-bake saat halaman
            // di-load) sudah stale, misalnya divisi tag baru saja
            // diubah tapi halaman belum di-refresh: tanpa ini, garment
            // bisa kebentuk dengan division_id kosong walau tag-nya
            // sebenarnya sudah punya divisi di DB.
            // Kalau tidak ada tag, baru pakai division_id manual dari form.
            $divisionId = $tag
                ? $tag->division_id
                : ($validated['division_id'] ?? null);
            // ─────────────────────────────────────────────────────

            $garment = Garment::create([
                'garment_code'    => $validated['garment_code'],
                'category_id'     => $validated['category_id'],
                'division_id'     => $divisionId,
                'size'            => $validated['size'] ?? null,
                'current_tag_id'  => $tag?->tag_id, // null kalau belum ada tag
                'max_cycle_limit' => $validated['max_cycle_limit'] ?? $category->default_max_cycle,
                'status'          => 'active',
                'date_first_used' => now()->toDateString(),
            ]);

            // Binding cuma dibuat kalau memang ada tag.
            if ($tag) {
                TagGarmentBinding::create([
                    'tag_id'     => $tag->tag_id,
                    'garment_id' => $garment->garment_id,
                    'bound_at'   => now(),
                    'bound_by'   => $request->user()?->user_id,
                    'is_current' => true,
                ]);
            }

            return $garment;
        });

        $message = $garment->current_tag_id
            ? "Garment {$garment->garment_code} berhasil dibuat dan tag terpasang."
            : "Garment {$garment->garment_code} berhasil dibuat tanpa tag. Pasang tag lewat menu Tag Reassignment.";

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', $message);
    }

    public function destroy(Request $request, $id)
    {
        $garment = Garment::findOrFail($id);

        if ($garment->currentTag()->exists()) {
            return back()->with('error', 'Lepas tag dari garment ini dulu sebelum dihapus.');
        }

        $garment->delete();
        return back()->with('success', 'Garment berhasil dihapus.');
    }
}
