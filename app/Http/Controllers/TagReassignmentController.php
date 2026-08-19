<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePindahTagRequest;
use App\Http\Requests\StoreGantiTagRequest;
use App\Models\Division;
use App\Models\Garment;
use App\Models\Tag;
use App\Models\TagGarmentBinding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

/**
 * Menangani 2 alur reassignment tag yang terpisah:
 *
 *  Skenario A — Pindah Tag (indexPindah/storePindah)
 *    Baju rusak, tag masih bagus. Tag lama dipindah ke baju baru yang
 *    sudah diinput tapi belum dipasangi tag.
 *
 *  Skenario B — Ganti Tag (indexGanti/storeGanti)
 *    Baju masih bagus, tag sudah aus. Baju ini dipasangi tag fisik
 *    BARU hasil scan (belum pernah terdaftar). Siklus baju tidak direset.
 *
 * Digabung dalam satu controller karena cuma beda logic tipis dan
 * berbagi helper yang sama (extractSignalMessage) — URL & view tetap
 * 2 halaman terpisah karena memang 2 alur kerja yang berbeda.
 *
 * ── PERUBAHAN (patch batas siklus tag) ──────────────────────────────
 * Baju & tag punya umur pakai yang BEDA (baju ±50x autoclave, tag
 * ±200x autoclave — lihat garments.max_cycle_limit vs
 * tags.rated_max_cycles). Karena satu tag bisa dipindah lintas
 * beberapa baju lewat "Pindah Tag", total_cycles_used milik tag bisa
 * mendekati/melewati rated_max_cycles walau baju yang ditinggalkan
 * belum tentu habis umur duluan. Sebelumnya tidak ada info apapun soal
 * ini di halaman Pindah Tag / Ganti Tag, jadi operator bisa saja
 * memindah tag yang sebenarnya sudah mepet habis jatah.
 *
 * Penegakan keras (block) ada di proses_pindah_tag (lihat
 * patch_tag_cycle_limits.sql). Di controller ini kita cuma menambah
 * INFORMASI sisa siklus & flag peringatan supaya operator sudah bisa
 * lihat dari daftar, sebelum submit — bukan cuma kena error dari DB.
 * ─────────────────────────────────────────────────────────────────
 */
class TagReassignmentController extends Controller
{
    /**
     * Ambang batas "mendekati akhir umur" untuk tag, dipakai buat
     * warning di UI (bukan buat block — block-nya ada di proses_pindah_tag).
     * Disamakan dengan ambang "kritis" yang dipakai AnalyticsController
     * untuk baju (90%), supaya konsisten.
     */
    private const TAG_WEAR_WARNING_THRESHOLD = 0.9;

    // ===================================================================
    // SKENARIO A — PINDAH TAG
    // ===================================================================

    public function indexPindah(): View
    {
        $availableTags = Tag::where('status', 'active')
            ->orderBy('tag_uid')
            ->get(['tag_id', 'tag_uid', 'rated_max_cycles', 'total_cycles_used'])
            ->map(function ($tag) {
                return $this->annotateTagWear($tag);
            });

        // Baju tujuan WAJIB belum punya tag sama sekali.
        $eligibleGarments = Garment::whereNull('current_tag_id')
            ->where('status', '!=', 'retired')
            ->orderBy('garment_code')
            ->get(['garment_id', 'garment_code', 'size', 'status']);

        $history = $this->recentHistory();

        $currentBindings = $availableTags->mapWithKeys(function ($tag) {
            $binding = TagGarmentBinding::with('garment:garment_id,garment_code')
                ->where('tag_id', $tag->tag_id)
                ->where('is_current', true)
                ->first();

            return [$tag->tag_id => $binding?->garment?->garment_code];
        });

        return view('admin.pindah-tag', [
            'availableTags'    => $availableTags,
            'eligibleGarments' => $eligibleGarments,
            'history'          => $history,
            'currentBindings'  => $currentBindings,
        ]);
    }

    public function storePindah(StorePindahTagRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::statement('CALL proses_pindah_tag(?, ?, ?, ?)', [
                $data['tag_id'],
                $data['new_garment_id'],
                auth()->id(),
                $data['unbind_reason'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('Gagal proses_pindah_tag: ' . $e->getMessage());

            return back()->withInput()->with('error', $this->extractSignalMessage($e->getMessage(), 'pindah tag'));
        }

        return redirect()
            ->route('admin.pindah-tag.index')
            ->with('success', 'Tag berhasil dipindahkan ke baju baru.');
    }

    // ===================================================================
    // SKENARIO B — GANTI TAG
    // ===================================================================

    public function indexGanti(): View
    {
        // Baju yang bisa diganti tag-nya: WAJIB sudah punya tag aktif.
        $eligibleGarments = Garment::with('currentTag:tag_id,tag_uid,total_cycles_used,rated_max_cycles')
            ->whereNotNull('current_tag_id')
            ->where('status', '!=', 'retired')
            ->orderBy('garment_code')
            ->get(['garment_id', 'garment_code', 'size', 'status', 'division_id', 'current_tag_id'])
            ->each(function ($garment) {
                // Tempel info sisa siklus tag yang sedang terpasang, supaya
                // operator langsung lihat "oh tag di baju ini sudah mepet,
                // sekalian aja diganti" tanpa harus buka halaman lain.
                if ($garment->currentTag) {
                    $garment->currentTag = $this->annotateTagWear($garment->currentTag);
                }
            });

        $divisions = Division::orderBy('division_name')->get(['division_id', 'division_name']);

        $history = $this->recentHistory();

        return view('admin.ganti-tag', [
            'eligibleGarments' => $eligibleGarments,
            'divisions'        => $divisions,
            'history'          => $history,
        ]);
    }

    public function storeGanti(StoreGantiTagRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::statement('CALL proses_ganti_tag(?, ?, ?, ?, ?, ?, ?, ?)', [
                $data['garment_id'],
                $data['new_tag_uid'],
                $data['tag_type'] ?? null,
                $data['manufacturer'] ?? null,
                $data['rated_max_cycles'] ?? null,
                $data['division_id'] ?? null,
                auth()->id(),
                $data['unbind_reason'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('Gagal proses_ganti_tag: ' . $e->getMessage());

            return back()->withInput()->with('error', $this->extractSignalMessage($e->getMessage(), 'ganti tag'));
        }

        return redirect()
            ->route('admin.ganti-tag.index')
            ->with('success', 'Tag baru berhasil dipasang. Siklus baju tidak berubah.');
    }

    // ===================================================================
    // HELPER BERSAMA
    // ===================================================================

    private function recentHistory()
    {
        return TagGarmentBinding::with(['tag:tag_id,tag_uid', 'garment:garment_id,garment_code', 'boundByUser:user_id,full_name'])
            ->orderByDesc('bound_at')
            ->paginate(15);
    }

    /**
     * Tempelkan atribut turunan soal sisa umur tag ke object Tag, supaya
     * blade view tinggal pakai $tag->remaining_cycles / $tag->wear_percent /
     * $tag->is_near_end_of_life tanpa hitung ulang di view.
     *
     * Catatan: ini HANYA untuk tampilan/peringatan. Penegakan keras
     * (block) tetap di proses_pindah_tag lewat SIGNAL, supaya konsisten
     * walau ada jalur lain (mis. panggilan API langsung) yang bypass
     * controller ini.
     */
    private function annotateTagWear(Tag $tag): Tag
    {
        $rated = $tag->rated_max_cycles ?: 1; // guard div-by-zero, seharusnya tidak pernah 0
        $used  = $tag->total_cycles_used ?? 0;

        $tag->remaining_cycles   = max(0, $rated - $used);
        $tag->wear_percent       = round(min(100, ($used / $rated) * 100), 1);
        $tag->is_over_limit      = $used >= $rated;
        $tag->is_near_end_of_life = !$tag->is_over_limit
            && ($used / $rated) >= self::TAG_WEAR_WARNING_THRESHOLD;

        return $tag;
    }

    private function extractSignalMessage(string $rawMessage, string $context): string
    {
        if (preg_match('/1644\s+(.*)$/s', $rawMessage, $matches)) {
            return trim($matches[1]);
        }

        return "Gagal memproses {$context}. Silakan hubungi administrator sistem.";
    }
}
