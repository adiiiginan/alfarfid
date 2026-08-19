<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Skenario B — Ganti Tag.
 * Baju yang sudah ada (masih bagus) dipasangi tag fisik BARU yang
 * baru saja di-scan dan BELUM PERNAH terdaftar di tabel tags.
 * Bukan pilih dari dropdown — tag_uid diketik/di-scan langsung.
 */
class StoreGantiTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'garment_id' => ['required', 'uuid', Rule::exists('garments', 'garment_id')],

            // Wajib BELUM ada di tabel tags — inti dari Skenario B.
            'new_tag_uid' => ['required', 'string', 'max:64', Rule::unique('tags', 'tag_uid')],

            'tag_type'         => ['nullable', 'string', 'max:50'],
            'manufacturer'     => ['nullable', 'string', 'max:100'],
            'rated_max_cycles' => ['nullable', 'integer', 'min:1'],
            'division_id'      => ['nullable', 'uuid', Rule::exists('divisions', 'division_id')],
            'unbind_reason'    => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'garment_id.required'  => 'Baju yang mau diganti tag-nya wajib dipilih.',
            'garment_id.exists'    => 'Baju tidak ditemukan di sistem.',
            'new_tag_uid.required' => 'Scan tag fisik baru dulu — UID belum terbaca.',
            'new_tag_uid.unique'   => 'UID ini sudah terdaftar di sistem. Ganti Tag hanya untuk tag fisik yang benar-benar baru.',
        ];
    }
}
