<?php

namespace App\Http\Requests;

/**
 * Consultation Request Validation
 * 
 * Validasi untuk membuat dan update konsultasi
 */
class ConsultationRequest extends ApiRequest
{
    public function rules(): array
    {
        $rules = [
            'dokter_id' => 'required|exists:users,id',
            'keluhan' => 'required|string|min:10|max:1000',
            'tipe_layanan' => 'required|in:online,offline',
            'tanggal_konsultasi' => 'nullable|date|after_or_equal:today',
            'waktu_mulai' => 'nullable|date_format:H:i',
        ];

        // Add status field only if updating (PUT/PATCH)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['status'] = 'nullable|in:pending,active,completed,cancelled';
            $rules['catatan_dokter'] = 'nullable|string|max:1000';
            $rules['diagnosis'] = 'nullable|string|max:1000';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'dokter_id' => 'Dokter',
            'keluhan' => 'Keluhan',
            'tipe_layanan' => 'Tipe Layanan',
            'tanggal_konsultasi' => 'Tanggal Konsultasi',
            'waktu_mulai' => 'Waktu Mulai',
            'status' => 'Status',
            'catatan_dokter' => 'Catatan Dokter',
            'diagnosis' => 'Diagnosis',
        ];
    }
}
