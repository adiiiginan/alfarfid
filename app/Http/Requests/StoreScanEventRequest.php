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

    protected function prepareForValidation(): void
    {
        if (! $this->has('tag_uid') || empty($this->input('tag_uid'))) {
            $fallback = $this->input('epc')
                ?? $this->input('uid')
                ?? $this->input('rfid_uid')
                ?? $this->input('tag')
                ?? $this->input('rfid');

            if (! $fallback && $this->has('tags')) {
                $tags = $this->input('tags');
                if (is_array($tags) && count($tags) > 0) {
                    $fallback = is_string($tags[0]) ? $tags[0] : null;
                }
            }

            if ($fallback) {
                $this->merge(['tag_uid' => trim($fallback)]);
            }
        }
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
