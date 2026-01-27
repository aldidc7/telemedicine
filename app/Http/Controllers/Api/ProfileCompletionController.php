<?php

namespace App\Http\Controllers\Api;

use App\Models\Dokter;
use App\Models\ActivityLog;
use App\Http\Requests\ProfileCompletionRequest;
use App\Http\Responses\ApiResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileCompletionController extends \App\Http\Controllers\Controller
{
    /**
     * Get profile completion status for current doctor
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'dokter') {
            return ApiResponseFormatter::forbidden('Hanya dokter yang dapat mengakses endpoint ini');
        }
        
        $dokter = Dokter::where('user_id', $user->id)->first();
        
        if (!$dokter) {
            return ApiResponseFormatter::notFound('Data dokter tidak ditemukan');
        }
        
        return ApiResponseFormatter::success([
            'registration_status' => $dokter->registration_status,
            'document_status' => $dokter->document_status,
            'profile_completed_at' => $dokter->profile_completed_at,
            'document_verified_at' => $dokter->document_verified_at,
            'verification_notes' => $dokter->verification_notes,
            'is_profile_complete' => $dokter->registration_status === 'completed',
            'is_verified' => $dokter->document_status === 'approved',
        ]);
    }
    
    /**
     * Submit profile completion form
     * 
     * @param ProfileCompletionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(ProfileCompletionRequest $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'dokter') {
            return ApiResponseFormatter::forbidden('Hanya dokter yang dapat mengakses endpoint ini');
        }
        
        $dokter = Dokter::where('user_id', $user->id)->first();
        
        if (!$dokter) {
            return ApiResponseFormatter::notFound('Data dokter tidak ditemukan');
        }
        
        try {
            $data = $request->validated();
            
            // Upload files
            $uploadedFiles = [];
            
            foreach (['sip_file', 'str_file', 'ktp_file', 'ijazah_file'] as $fileField) {
                if ($request->hasFile($fileField)) {
                    $file = $request->file($fileField);
                    $path = Storage::disk('public')->putFile(
                        'doctor-documents/' . $dokter->id,
                        $file
                    );
                    $uploadedFiles[$fileField] = $path;
                }
            }
            
            // Upload additional documents if provided
            $additionalDocs = [];
            if ($request->hasFile('additional_documents')) {
                foreach ($request->file('additional_documents') as $file) {
                    $path = Storage::disk('public')->putFile(
                        'doctor-documents/' . $dokter->id . '/additional',
                        $file
                    );
                    $additionalDocs[] = $path;
                }
            }
            
            // Update dokter record
            $dokter->update([
                'specialization' => $data['specialization'],
                'address' => $data['address'],
                'license_number' => $data['sip_number'],
                'sip_file_path' => $uploadedFiles['sip_file'] ?? null,
                'str_file_path' => $uploadedFiles['str_file'] ?? null,
                'ktp_file_path' => $uploadedFiles['ktp_file'] ?? null,
                'ijazah_file_path' => $uploadedFiles['ijazah_file'] ?? null,
                'additional_documents' => !empty($additionalDocs) ? $additionalDocs : null,
                'registration_status' => 'completed',
                'profile_completed_at' => now(),
                'accepted_terms' => $data['accepted_terms'] ?? false,
                'accepted_privacy_policy' => $data['accepted_privacy_policy'] ?? false,
                'accepted_informed_consent' => $data['accepted_informed_consent'] ?? false,
                'compliance_accepted_at' => now(),
            ]);
            
            // Log activity
            ActivityLog::log($user->id, 'profile_completion', 'Dokter melengkapi profil');
            
            Log::info('Dokter profile completion submitted', [
                'doctor_id' => $dokter->id,
                'user_id' => $user->id,
                'specialization' => $data['specialization'],
            ]);
            
            return ApiResponseFormatter::success(
                $dokter,
                'Profil berhasil dilengkapi. Menunggu verifikasi admin.'
            );
        } catch (\Exception $e) {
            Log::error('Profile completion error', [
                'doctor_id' => $dokter->id,
                'error' => $e->getMessage(),
            ]);
            
            return ApiResponseFormatter::error(
                'Gagal menyimpan profil. Silakan coba lagi.',
                500
            );
        }
    }
    
    /**
     * Get remaining days to complete profile (grace period)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRemainingDays()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'dokter') {
            return ApiResponseFormatter::forbidden('Hanya dokter yang dapat mengakses endpoint ini');
        }
        
        $dokter = Dokter::where('user_id', $user->id)->first();
        
        if (!$dokter) {
            return ApiResponseFormatter::notFound('Data dokter tidak ditemukan');
        }
        
        // Grace period: 7 days dari created_at
        $gracePeriodDays = 7;
        $registrationDate = $dokter->created_at;
        $dueDate = $registrationDate->addDays($gracePeriodDays);
        $remainingDays = $dueDate->diffInDays(now(), false);
        
        return ApiResponseFormatter::success([
            'remaining_days' => max(0, $remainingDays),
            'due_date' => $dueDate->toIso8601String(),
            'is_overdue' => $remainingDays < 0,
        ]);
    }
}
