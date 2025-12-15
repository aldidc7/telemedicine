<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimrsApi\PatientController;
use App\Http\Controllers\SimrsApi\DoctorController;
use App\Http\Controllers\SimrsApi\MedicalRecordController;
use App\Http\Controllers\SimrsApi\ConsultationController;

Route::prefix('api/simrs')->group(function () {
    
    // Dummy authentication
    Route::post('auth/token', function () {
        return response()->json([
            'token' => 'simrs-dummy-token-' . now()->timestamp,
            'expires_in' => 3600,
        ]);
    });

    // Protected routes menggunakan middleware
    Route::middleware('simrs.auth')->group(function () {
        
        // ============ PATIENTS ============
        Route::get('patients/{patient_id}', [PatientController::class, 'show'])
            ->name('simrs.patients.show');
        
        Route::get('patients/{patient_id}/medical-records', [MedicalRecordController::class, 'index'])
            ->name('simrs.medical-records.index');
        
        // ============ DOCTORS ============
        Route::get('doctors/{doctor_id}', [DoctorController::class, 'show'])
            ->name('simrs.doctors.show');
        
        // ============ CONSULTATIONS ============
        Route::post('consultations/{consultation_id}/sync', [ConsultationController::class, 'sync'])
            ->name('simrs.consultations.sync');
        
        Route::get('health-check', function () {
            return response()->json([
                'status' => 'ok',
                'timestamp' => now(),
            ]);
        })->name('simrs.health-check');
    });
});