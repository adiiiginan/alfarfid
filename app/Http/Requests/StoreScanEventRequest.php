<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScanEventRequest extends FormRequest
{
    /**
     * Otorisasi sudah ditangani oleh middleware esp.device (AuthenticateEspDevice),
     * jadi di sini cukup return true — device yang tidak sah sudah ditolak
     * sebelum request sampai ke sini.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'tag_uid'    => ['required', 'string'],
            'event_type' => ['nullable', 'string', 'in:pre_autoclave,post_autoclave,usage_checkpoint,manual_reentry'],
            'session_id' => ['nullable', 'uuid'],
            'scanned_at' => ['nullable', 'date'],
            'location'   => ['nullable', 'string'],

        ];
    }

    /**
     * Pesan error dalam Bahasa Indonesia, biar konsisten dengan
     * pesan lain di ScanEventController.
     */
    public function messages(): array
    {
        return [
            'tag_uid.required'    => 'tag_uid wajib diisi.',
            'event_type.required' => 'event_type wajib diisi.',
            'event_type.in'       => 'event_type tidak valid.',
        ];
    }
}
