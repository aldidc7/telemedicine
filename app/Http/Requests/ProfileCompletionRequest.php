<?php

namespace App\Http\Requests;

/**
 * Profile Completion Request Validation
 * 
 * Validasi untuk melengkapi profil dokter setelah registrasi
 * Dokter harus mengisi data ini sebelum bisa praktek
 */
class ProfileCompletionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'specialization' => 'required|string|in:Umum,Anak,Kandungan,Jantung,Mata,THT,Saraf,Kulit,Gigi',
            'address' => 'required|string|min:10|max:500',
            'sip_number' => 'required|string|max:255|unique:doctors,license_number',
            'str_number' => 'required|string|max:255',
            'sip_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'str_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ktp_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ijazah_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'additional_documents' => 'nullable|array',
            'additional_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
            'accepted_terms' => 'required|boolean|accepted',
            'accepted_privacy_policy' => 'required|boolean|accepted',
            'accepted_informed_consent' => 'required|boolean|accepted',
        ];
    }

    public function attributes(): array
    {
        return [
            'specialization' => 'Spesialisasi',
            'address' => 'Alamat',
            'sip_number' => 'Nomor SIP',
            'str_number' => 'Nomor STR',
            'sip_file' => 'File SIP',
            'str_file' => 'File STR',
            'ktp_file' => 'File KTP',
            'ijazah_file' => 'File Ijazah',
            'additional_documents' => 'Dokumen Tambahan',
            'accepted_terms' => 'Persetujuan Syarat & Ketentuan',
            'accepted_privacy_policy' => 'Persetujuan Kebijakan Privasi',
            'accepted_informed_consent' => 'Informed Consent',
        ];
    }

    public function messages(): array
    {
        return [
            'sip_number.unique' => 'Nomor SIP sudah terdaftar di sistem',
            'sip_file.required' => 'File SIP harus diunggah',
            'sip_file.mimes' => 'File SIP harus format PDF, JPG, atau PNG',
            'sip_file.max' => 'File SIP maksimal 5MB',
            'str_file.required' => 'File STR harus diunggah',
            'str_file.mimes' => 'File STR harus format PDF, JPG, atau PNG',
            'str_file.max' => 'File STR maksimal 5MB',
            'ktp_file.required' => 'File KTP harus diunggah',
            'ktp_file.mimes' => 'File KTP harus format PDF, JPG, atau PNG',
            'ktp_file.max' => 'File KTP maksimal 5MB',
            'ijazah_file.required' => 'File Ijazah harus diunggah',
            'ijazah_file.mimes' => 'File Ijazah harus format PDF, JPG, atau PNG',
            'ijazah_file.max' => 'File Ijazah maksimal 5MB',
            'accepted_terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan',
            'accepted_privacy_policy.accepted' => 'Anda harus menyetujui Kebijakan Privasi',
            'accepted_informed_consent.accepted' => 'Anda harus menyetujui Informed Consent',
        ];
    }
}
