<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Skenario A — Pindah Tag.
 * Tag lama (masih bagus) dipindah ke baju baru yang sudah diinput
 * lewat form "Tambah Garment Baru" tapi belum dipasangi tag apa pun
 * (current_tag_id IS NULL).
 */
class StorePindahTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi role (Super Admin / Admin CSSD) ditangani di middleware route.
        return true;
    }

    public function rules(): array
    {
        return [
            'tag_id' => ['required', 'uuid', 'exists:tags,tag_id'],

            // Baju tujuan wajib belum punya tag aktif — dicek lagi secara
            // definitif di stored procedure proses_pindah_tag (FOR UPDATE),
            // validasi di sini cuma lapisan pertama untuk pesan error cepat.
            'new_garment_id' => [
                'required',
                'uuid',
                Rule::exists('garments', 'garment_id'),
            ],

            'unbind_reason' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'tag_id.required'         => 'Tag RFID yang mau dipindah wajib dipilih.',
            'tag_id.exists'           => 'Tag RFID tidak ditemukan di sistem.',
            'new_garment_id.required' => 'Baju tujuan (baju baru) wajib dipilih.',
            'new_garment_id.exists'   => 'Baju tujuan tidak ditemukan di sistem.',
        ];
    }
}
