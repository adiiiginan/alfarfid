<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagReassignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi role (Super Admin / Admin CSSD) ditangani di middleware route,
        // form request ini fokus ke validasi input saja.
        return true;
    }

    public function rules(): array
    {
        return [
            'tag_id' => ['required', 'uuid', 'exists:tags,tag_id'],

            // Baju tujuan (baru): wajib belum punya tag aktif terpasang,
            // supaya tidak menabrak constraint uq_one_current_binding_per_garment.
            'new_garment_id' => [
                'required',
                'uuid',
                Rule::exists('garments', 'garment_id'),
                'different:old_garment_id',
            ],

            // Baju lama: hidden field, di-derive otomatis oleh controller dari
            // binding is_current milik tag_id terpilih — tapi tetap divalidasi
            // di sini kalau ada dikirim dari form (defense in depth).
            'old_garment_id' => ['nullable', 'uuid', 'exists:garments,garment_id'],

            'unbind_reason' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'tag_id.required'          => 'Tag RFID wajib dipilih.',
            'tag_id.exists'            => 'Tag RFID tidak ditemukan di sistem.',
            'new_garment_id.required'  => 'Baju tujuan (baru) wajib dipilih.',
            'new_garment_id.exists'    => 'Baju tujuan tidak ditemukan di sistem.',
            'new_garment_id.different' => 'Baju tujuan tidak boleh sama dengan baju asal.',
        ];
    }
}
