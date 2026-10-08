<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GarmentCategory;
use App\Models\Division;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\Garment;

class GarmentConfigController extends Controller
{
    /**
     * GET /admin/garment-config -> admin.garment-config.index
     * Halaman gabungan: Kategori Baju + Divisi & Warna.
     */
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

    // ================= KATEGORI BAJU =================

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'category_name'     => 'required|string|max:100|unique:garment_categories,category_name',
            'description'       => 'nullable|string',
            'default_max_cycle' => 'required|integer|min:1',
        ]);

        GarmentCategory::create($validated);

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', 'Kategori baju berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, GarmentCategory $category)
    {
        $validated = $request->validate([
            'category_name'     => 'required|string|max:100|unique:garment_categories,category_name,' . $category->category_id . ',category_id',
            'description'       => 'nullable|string',
            'default_max_cycle' => 'required|integer|min:1',
        ]);

        $category->update($validated);

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', 'Kategori baju berhasil diperbarui.');
    }

    public function destroyCategory(GarmentCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', 'Kategori baju berhasil dihapus.');
    }

    // ================= DIVISI & WARNA =================

    public function storeDivision(Request $request)
    {
        $validated = $request->validate([
            'division_name' => 'required|string|max:100|unique:divisions,division_name',
            'color_name'    => 'required|string|max:50',
            'color_hex'     => 'required|string|max:7',
            'description'   => 'nullable|string',
        ]);

        $validated['is_active'] = true;

        Division::create($validated);

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function updateDivision(Request $request, Division $division)
    {
        $validated = $request->validate([
            'division_name' => 'required|string|max:100|unique:divisions,division_name,' . $division->division_id . ',division_id',
            'color_name'    => 'required|string|max:50',
            'color_hex'     => 'required|string|max:7',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $division->update($validated);

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroyDivision(Division $division)
    {
        $division->delete();

        return redirect()
            ->route('admin.garment-config.index')
            ->with('success', 'Divisi berhasil dihapus.');
    }
}
