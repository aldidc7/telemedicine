<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasienController;
use App\Http\Controllers\Api\DokterController;
use App\Http\Controllers\Api\KonsultasiController;
use App\Http\Controllers\Api\PesanChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\KonsultasiSummaryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\DoctorVerificationController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\BroadcastingController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\SimrsApi\SimrsPasienController;
use App\Http\Controllers\SimrsApi\SimrsDokterController;
use App\Http\Controllers\SimrsApi\SimrsRekamMedisController;
use App\Http\Controllers\SimrsApi\SimrsKonsultasiSyncController;

Route::get('/v1/health', function () {
    return response()->json(['status' => 'API is running']);
});

Route::prefix('v1')->middleware(['performance'])->group(function () {
    // ============ PUBLIC ROUTES (No Auth) ============

    // ============ AUTHENTICATION ROUTES ============
    /**
     * Authentikasi & Authorization
     * POST /api/v1/auth/register - Registrasi user baru
     * POST /api/v1/auth/login - Login user
     * POST /api/v1/auth/refresh - Refresh token
     * POST /api/v1/auth/logout - Logout user
     * GET /api/v1/auth/me - Get current user profile
     * GET /api/v1/auth/verify-email - Verifikasi email
     * POST /api/v1/auth/forgot-password - Request reset password
     * POST /api/v1/auth/reset-password - Reset password dengan token
     */
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // ============ PROTECTED ROUTES (Sanctum Auth) ============
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::get('/auth/profile-completion', [AuthController::class, 'profileCompletion']);

        // ========== PASIEN ENDPOINTS (Admin & Self) ==========
        /**
         * Patient Management
         * GET /api/v1/pasien - List semua pasien (Admin only)
         * POST /api/v1/pasien - Buat pasien baru (Admin only)
         * GET /api/v1/pasien/{id} - Get detail pasien (Self or Admin)
         * PUT /api/v1/pasien/{id} - Update data pasien (Self or Admin)
         * DELETE /api/v1/pasien/{id} - Delete pasien (Admin only)
         * GET /api/v1/pasien/{id}/rekam-medis - Get rekam medis pasien (Self or Admin)
         * GET /api/v1/pasien/{id}/konsultasi - Get konsultasi pasien (Self or Admin)
         */
        Route::get('/pasien', [PasienController::class, 'index']);
        Route::post('/pasien', [PasienController::class, 'store']);
        Route::get('/pasien/{id}', [PasienController::class, 'show']);
        Route::put('/pasien/{id}', [PasienController::class, 'update']);
        Route::delete('/pasien/{id}', [PasienController::class, 'destroy']);
        Route::get('/pasien/{id}/rekam-medis', [PasienController::class, 'rekamMedis']);
        Route::get('/pasien/{id}/konsultasi', [PasienController::class, 'konsultasi']);

        // ========== DOKTER ENDPOINTS (Admin & Self) ==========
        /**
         * Doctor Management
         * GET /api/v1/dokter - List dokter (Public)
         * POST /api/v1/dokter - Buat dokter baru (Admin only)
         * GET /api/v1/dokter/{id} - Detail dokter (Public)
         * PUT /api/v1/dokter/{id} - Update dokter (Self or Admin)
         * DELETE /api/v1/dokter/{id} - Delete dokter (Admin only)
         * PUT /api/v1/dokter/{id}/ketersediaan - Update availability (Self)
         * GET /api/v1/dokter/search/advanced - Advanced search dokter
         * GET /api/v1/dokter/top-rated - Dokter dengan rating tertinggi
         * GET /api/v1/dokter/specializations - Daftar spesialisasi
         */
        Route::get('/dokter', [DokterController::class, 'index']);
        Route::post('/dokter', [DokterController::class, 'store']);
        
        // Search & Filter endpoints (before specific routes to avoid conflicts)
        Route::get('/dokter/search/advanced', [DokterController::class, 'search']);
        Route::get('/dokter/top-rated', [DokterController::class, 'topRated']);
        Route::get('/dokter/specializations/list', [DokterController::class, 'specializations']);
        
        // Other routes
        Route::get('/dokter/public/terverifikasi', [DokterController::class, 'verifiedDoctors']);
        Route::get('/dokter/user/{user_id}', [DokterController::class, 'getByUserId']);
        Route::get('/dokter/{id}/detail', [DokterController::class, 'detail']);
        Route::get('/dokter/{id}', [DokterController::class, 'show']);
        Route::put('/dokter/{id}', [DokterController::class, 'update']);
        Route::delete('/dokter/{id}', [DokterController::class, 'destroy']);
        Route::post('/dokter/{id}/sync-patient', [DokterController::class, 'syncToPatient']);
        Route::put('/dokter/{id}/ketersediaan', [DokterController::class, 'updateKetersediaan']);

        // ========== KONSULTASI ENDPOINTS ==========
        /**
         * Consultation Management
         * GET /api/v1/konsultasi - List konsultasi user (Pasien/Dokter own, Admin all)
         * POST /api/v1/konsultasi - Buat konsultasi baru (Pasien only)
         * GET /api/v1/konsultasi/{id} - Detail konsultasi (Owner or Admin)
         * POST /api/v1/konsultasi/{id}/terima - Dokter terima konsultasi (Dokter only)
         * POST /api/v1/konsultasi/{id}/tolak - Dokter tolak konsultasi (Dokter only)
         * POST /api/v1/konsultasi/{id}/selesaikan - Selesaikan konsultasi (Dokter only)
         */
        Route::get('/konsultasi', [KonsultasiController::class, 'index']);
        Route::post('/konsultasi', [KonsultasiController::class, 'store']);
        Route::get('/konsultasi/{id}', [KonsultasiController::class, 'show']);
        Route::post('/konsultasi/{id}/terima', [KonsultasiController::class, 'terima']);
        Route::post('/konsultasi/{id}/tolak', [KonsultasiController::class, 'tolak']);
        Route::post('/konsultasi/{id}/selesaikan', [KonsultasiController::class, 'selesaikan']);

        // ========== KONSULTASI SUMMARY ENDPOINTS ==========
        /**
         * Consultation Summary Management
         * POST /api/v1/consultations/{id}/summary - Dokter create summary
         * GET /api/v1/consultations/{id}/summary - Get summary (Pasien/Dokter/Admin)
         * PUT /api/v1/consultations/{id}/summary - Update summary (Dokter only)
         * PUT /api/v1/consultations/{id}/summary/acknowledge - Pasien acknowledge summary
         * GET /api/v1/patient/summaries - List pasien summaries
         * GET /api/v1/doctor/summaries - List dokter summaries
         */
        Route::post('/consultations/{id}/summary', [KonsultasiSummaryController::class, 'store']);
        Route::get('/consultations/{id}/summary', [KonsultasiSummaryController::class, 'show']);
        Route::put('/consultations/{id}/summary', [KonsultasiSummaryController::class, 'update']);
        Route::put('/consultations/{id}/summary/acknowledge', [KonsultasiSummaryController::class, 'acknowledge']);
        Route::get('/patient/summaries', [KonsultasiSummaryController::class, 'patientSummaries']);
        Route::get('/doctor/summaries', [KonsultasiSummaryController::class, 'doctorSummaries']);

        // ========== PESAN CHAT ENDPOINTS ==========
        /**
         * Chat Messages Management
         * GET /api/v1/pesan/{konsultasi_id} - List pesan dalam konsultasi
         * POST /api/v1/pesan - Kirim pesan
         * GET /api/v1/pesan/{id} - Get detail pesan
         * PUT /api/v1/pesan/{id}/dibaca - Mark pesan as read
         * GET /api/v1/pesan/{konsultasi_id}/unread-count - Jumlah pesan belum dibaca
         * DELETE /api/v1/pesan/{id} - Delete pesan (Sender or Admin)
         * PUT /api/v1/pesan/{konsultasi_id}/mark-all-read - Mark all as read
         */
        Route::get('/pesan/{konsultasiId}', [PesanChatController::class, 'index']);
        Route::post('/pesan', [PesanChatController::class, 'store']);
        Route::get('/pesan/{id}', [PesanChatController::class, 'show']);
        Route::put('/pesan/{id}/dibaca', [PesanChatController::class, 'markAsDibaca']);
        Route::get('/pesan/{konsultasiId}/unread-count', [PesanChatController::class, 'unreadCount']);
        Route::delete('/pesan/{id}', [PesanChatController::class, 'destroy']);
        Route::put('/pesan/{konsultasiId}/mark-all-read', [PesanChatController::class, 'markAllAsDibaca']);

        // ========== ADMIN ENDPOINTS ==========
        /**
         * Admin Management & Monitoring
         * GET /api/v1/admin/dashboard - Admin dashboard stats (Admin only)
         * GET /api/v1/admin/pengguna - List semua user (Admin only)
         * GET /api/v1/admin/pengguna/{id} - Get user detail (Admin only)
         * PUT /api/v1/admin/pengguna/{id} - Update user (Admin only)
         * PUT /api/v1/admin/pengguna/{id}/nonaktif - Deactivate user (Admin only)
         * PUT /api/v1/admin/pengguna/{id}/aktif - Activate user (Admin only)
         * DELETE /api/v1/admin/pengguna/{id} - Delete user (Admin only)
         * GET /api/v1/admin/log-aktivitas - View activity logs (Admin only)
         * GET /api/v1/admin/statistik - System statistics (Admin only)
         */
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/admin/pengguna', [AdminController::class, 'pengguna']);
        Route::get('/admin/pengguna/{id}', [AdminController::class, 'showPengguna']);
        Route::put('/admin/pengguna/{id}', [AdminController::class, 'updatePengguna']);
        Route::put('/admin/pengguna/{id}/nonaktif', [AdminController::class, 'nonaktifkanPengguna']);
        Route::put('/admin/pengguna/{id}/aktif', [AdminController::class, 'aktifkanPengguna']);
        Route::delete('/admin/pengguna/{id}', [AdminController::class, 'hapusPengguna']);
        Route::get('/admin/log-aktivitas', [AdminController::class, 'logAktivitas']);
        Route::get('/admin/statistik', [AdminController::class, 'statistik']);

        // ========== SUPERADMIN ROUTES ==========
        /**
         * Superadmin Management
         * GET /api/v1/superadmin/system-logs - View all system audit logs (Superadmin only)
         * PUT /api/v1/admin/pengguna/{id}/status - Update user status
         */
        Route::get('/superadmin/system-logs', [AdminController::class, 'getSystemLogs']);
        Route::put('/admin/pengguna/{id}/status', [AdminController::class, 'updateUserStatus']);

        // ========== DOCTOR VERIFICATION ROUTES (Admin only) ==========
        /**
         * Doctor Verification Management
         * GET /api/v1/admin/dokter/pending - List dokter pending verifikasi
         * GET /api/v1/admin/dokter/approved - List dokter yang sudah disetujui
         * POST /api/v1/admin/dokter/{id}/approve - Approve dokter
         * POST /api/v1/admin/dokter/{id}/reject - Reject dokter
         * Legacy routes:
         * GET /api/v1/admin/doctors/pending - List dokter pending verifikasi
         * POST /api/v1/admin/doctors/{id}/approve - Approve dokter
         * POST /api/v1/admin/doctors/{id}/reject - Reject dokter
         * GET /api/v1/admin/doctors/{id}/status - Get doctor verification status
         */
        // New AdminController routes
        Route::get('/admin/dokter/pending', [AdminController::class, 'getPendingDoctors']);
        Route::get('/admin/dokter/approved', [AdminController::class, 'getApprovedDoctors']);
        Route::post('/admin/dokter/{id}/approve', [AdminController::class, 'approveDoctor']);
        Route::post('/admin/dokter/{id}/reject', [AdminController::class, 'rejectDoctor']);
        
        // Legacy DoctorVerificationController routes (for backward compatibility)
        Route::get('/admin/doctors/pending', [DoctorVerificationController::class, 'pendingDoctors']);
        Route::post('/admin/doctors/{id}/approve', [DoctorVerificationController::class, 'approvDoctor']);
        Route::post('/admin/doctors/{id}/reject', [DoctorVerificationController::class, 'rejectDoctor']);
        Route::get('/admin/doctors/{id}/status', [DoctorVerificationController::class, 'getDoctorStatus']);

        // ========== FILE UPLOAD ENDPOINTS ==========
        /**
         * File Upload Management
         * POST /api/v1/files/upload - Upload file dengan batasan ukuran
         * GET /api/v1/files/storage-info - Get storage usage info
         * DELETE /api/v1/files/{id} - Delete file (soft delete)
         * GET /api/v1/files/size-limits - Get size limits untuk setiap kategori
         */
        Route::post('/files/upload', [FileUploadController::class, 'upload']);
        Route::get('/files/storage-info', [FileUploadController::class, 'getStorageInfo']);
        Route::delete('/files/{filePath}', [FileUploadController::class, 'delete']);
        Route::get('/files/size-limits', [FileUploadController::class, 'getSizeLimits']);
    });

    // ============ SIMRS API ROUTES (Token-based, Separated) ============
    /**
     * SIMRS Integration API
     * Untuk komunikasi dengan dummy SIMRS system
     * Semua endpoint membutuhkan SIMRS token (bukan Sanctum)
     */
    Route::prefix('simrs')->group(function () {
        
        // ========== SIMRS PASIEN ENDPOINTS ==========
        /**
         * GET /api/v1/simrs/pasien/{id} - Ambil data pasien dari SIMRS
         * POST /api/v1/simrs/pasien - Buat pasien baru di SIMRS
         */
        Route::get('/pasien/{id}', [SimrsPasienController::class, 'ambil']);
        Route::post('/pasien', [SimrsPasienController::class, 'buat']);

        // ========== SIMRS DOKTER ENDPOINTS ==========
        /**
         * PENTING: Spesialisasi route HARUS didaftar SEBELUM {id} route
         * Agar /simrs/dokter/spesialisasi/{spesialisasi} tidak di-match sebagai {id}
         * GET /api/v1/simrs/dokter/spesialisasi/{spesialisasi} - Ambil dokter by spesialisasi
         * GET /api/v1/simrs/dokter/{id} - Ambil data dokter dari SIMRS
         */
        Route::get('/dokter/spesialisasi/{spesialisasi}', [SimrsDokterController::class, 'daftarBySpesialisasi']);
        Route::get('/dokter/{id}', [SimrsDokterController::class, 'ambil']);

        // ========== SIMRS REKAM MEDIS ENDPOINTS ==========
        /**
         * Rating Routes - Pasien bisa beri rating ke dokter
         * GET /api/v1/ratings/dokter/{id} - Get ratings untuk dokter tertentu
         * GET /api/v1/ratings/konsultasi/{id} - Get rating untuk konsultasi tertentu
         * POST /api/v1/ratings - Create rating (authenticated)
         * PUT /api/v1/ratings/{id} - Update rating (authenticated)
         * DELETE /api/v1/ratings/{id} - Delete rating (authenticated)
         */
        Route::get('/ratings/dokter/{dokter_id}', [RatingController::class, 'getDokterRatings']);
        Route::get('/ratings/konsultasi/{konsultasi_id}', [RatingController::class, 'getKonsultasiRating']);
        Route::apiResource('/ratings', RatingController::class, ['only' => ['store', 'update', 'destroy']]);

        // ========== MESSAGING ENDPOINTS (Chat/Conversations) ==========
        /**
         * Messaging Routes - Komunikasi antara Pasien dan Dokter
         * GET /api/v1/messages/conversations - List semua conversations
         * POST /api/v1/messages/conversations - Create/get conversation dengan user lain
         * GET /api/v1/messages/conversations/{id} - Get conversation detail
         * GET /api/v1/messages/conversations/{id}/messages - Get messages dalam conversation
         * POST /api/v1/messages/conversations/{id}/send - Send message
         * POST /api/v1/messages/conversations/{id}/read - Mark conversation as read
         * DELETE /api/v1/messages/conversations/{id} - Delete conversation
         * GET /api/v1/messages/unread-count - Get total unread message count
         */
        Route::prefix('/messages')->group(function () {
            Route::get('/conversations', [MessageController::class, 'getConversations']);
            Route::get('/conversations/{id}', [MessageController::class, 'getConversationDetail']);
            Route::post('/conversations', [MessageController::class, 'createConversation']);
            Route::get('/conversations/{id}/messages', [MessageController::class, 'getMessages']);
            Route::post('/conversations/{id}/send', [MessageController::class, 'sendMessage']);
            Route::post('/conversations/{id}/read', [MessageController::class, 'markAsRead']);
            Route::delete('/conversations/{id}', [MessageController::class, 'deleteConversation']);
            Route::get('/unread-count', [MessageController::class, 'getUnreadCount']);
        });

        // ========== NOTIFICATION ENDPOINTS ==========
        /**
         * Notification Routes - In-app notifications untuk users
         * GET /api/v1/notifications - Get user notifications (paginated)
         * GET /api/v1/notifications/unread - Get unread notifications
         * GET /api/v1/notifications/count - Get unread count
         * GET /api/v1/notifications/stats - Get notification statistics
         * POST /api/v1/notifications/{id}/read - Mark single as read
         * POST /api/v1/notifications/read-multiple - Mark multiple as read
         * POST /api/v1/notifications/read-all - Mark all as read
         * DELETE /api/v1/notifications/{id} - Delete notification
         * DELETE /api/v1/notifications/delete-multiple - Delete multiple
         * DELETE /api/v1/notifications/clear - Clear all notifications
         */
        Route::prefix('/notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread', [NotificationController::class, 'getUnread']);
            Route::get('/count', [NotificationController::class, 'getUnreadCount']);
            Route::get('/stats', [NotificationController::class, 'getStats']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-multiple', [NotificationController::class, 'markMultipleAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            Route::delete('/delete-multiple', [NotificationController::class, 'deleteMultiple']);
            Route::delete('/clear', [NotificationController::class, 'clearAll']);
        });

        // ========== APPOINTMENT ENDPOINTS ==========
        /**
         * Appointment/Booking Routes - Penjadwalan konsultasi
         * GET /api/v1/appointments - List user's appointments
         * POST /api/v1/appointments - Book appointment (patient only)
         * GET /api/v1/appointments/{id} - Get appointment detail
         * GET /api/v1/appointments/stats - Get appointment statistics
         * GET /api/v1/appointments/today - Get today's appointments
         * GET /api/v1/doctor/{id}/available-slots - Get doctor's available slots
         * POST /api/v1/appointments/{id}/confirm - Confirm (doctor only)
         * POST /api/v1/appointments/{id}/reject - Reject appointment (doctor only)
         * POST /api/v1/appointments/{id}/cancel - Cancel appointment
         * POST /api/v1/appointments/{id}/reschedule - Reschedule (patient only)
         * POST /api/v1/appointments/{id}/start - Start consultation (doctor only)
         * POST /api/v1/appointments/{id}/end - End consultation (doctor only)
         */
        Route::prefix('/appointments')->group(function () {
            Route::get('/', [AppointmentController::class, 'index']);
            Route::post('/', [AppointmentController::class, 'store']);
            Route::get('/stats', [AppointmentController::class, 'stats']);
            Route::get('/today', [AppointmentController::class, 'today']);
            Route::get('/{id}', [AppointmentController::class, 'show']);
            Route::post('/{id}/confirm', [AppointmentController::class, 'confirm']);
            Route::post('/{id}/reject', [AppointmentController::class, 'reject']);
            Route::post('/{id}/cancel', [AppointmentController::class, 'cancel']);
            Route::post('/{id}/reschedule', [AppointmentController::class, 'reschedule']);
            Route::post('/{id}/start', [AppointmentController::class, 'start']);
            Route::post('/{id}/end', [AppointmentController::class, 'end']);
        });

        // Available slots endpoint
        Route::get('/doctor/{doctorId}/available-slots', [AppointmentController::class, 'getAvailableSlots']);

        // ========== PRESCRIPTION ENDPOINTS ==========
        /**
         * Prescription Routes - Resep dokter untuk pasien
         * GET /api/v1/prescriptions - List prescriptions
         * POST /api/v1/prescriptions - Create prescription (doctor only)
         * GET /api/v1/prescriptions/{id} - Get prescription detail
         * GET /api/v1/prescriptions/active - Get active prescriptions (patient)
         * GET /api/v1/prescriptions/unacknowledged - Get unacknowledged (patient)
         * PUT /api/v1/prescriptions/{id} - Update prescription (doctor)
         * POST /api/v1/prescriptions/{id}/acknowledge - Acknowledge (patient)
         * POST /api/v1/prescriptions/{id}/complete - Mark complete (patient)
         * DELETE /api/v1/prescriptions/{id} - Delete prescription (doctor)
         * GET /api/v1/prescriptions/stats - Get statistics
         * GET /api/v1/appointments/{appointmentId}/prescriptions - Get appointment prescriptions
         * GET /api/v1/appointments/{appointmentId}/has-prescription - Check if has prescription
         */
        Route::prefix('/prescriptions')->group(function () {
            Route::get('/', [PrescriptionController::class, 'index']);
            Route::post('/', [PrescriptionController::class, 'store']);
            Route::get('/stats', [PrescriptionController::class, 'stats']);
            Route::get('/active', [PrescriptionController::class, 'active']);
            Route::get('/unacknowledged', [PrescriptionController::class, 'unacknowledged']);
            Route::get('/{id}', [PrescriptionController::class, 'show']);
            Route::put('/{id}', [PrescriptionController::class, 'update']);
            Route::post('/{id}/acknowledge', [PrescriptionController::class, 'acknowledge']);
            Route::post('/{id}/complete', [PrescriptionController::class, 'complete']);
            Route::delete('/{id}', [PrescriptionController::class, 'destroy']);
        });

        // Appointment prescription endpoints
        Route::get('/appointments/{appointmentId}/prescriptions', [PrescriptionController::class, 'byAppointment']);
        Route::get('/appointments/{appointmentId}/has-prescription', [PrescriptionController::class, 'appointmentHasPrescription']);

        // ========== ANALYTICS ENDPOINTS (Admin Only) ==========
        /**
         * Analytics Routes - Admin dashboard analytics
         * GET /api/v1/analytics/overview - Dashboard overview
         * GET /api/v1/analytics/consultations - Consultation metrics
         * GET /api/v1/analytics/doctors - Doctor performance
         * GET /api/v1/analytics/health-trends - Patient health trends
         * GET /api/v1/analytics/revenue - Revenue analytics
         * GET /api/v1/analytics/range - Analytics by date range
         * POST /api/v1/analytics/refresh - Refresh cache
         */
        Route::prefix('/analytics')->middleware('can:view-analytics')->group(function () {
            Route::get('/overview', [AnalyticsController::class, 'getDashboardOverview']);
            Route::get('/consultations', [AnalyticsController::class, 'getConsultationMetrics']);
            Route::get('/doctors', [AnalyticsController::class, 'getDoctorPerformance']);
            Route::get('/health-trends', [AnalyticsController::class, 'getPatientHealthTrends']);
            Route::get('/revenue', [AnalyticsController::class, 'getRevenueAnalytics']);
            Route::get('/range', [AnalyticsController::class, 'getAnalyticsByDateRange']);
            Route::post('/refresh', [AnalyticsController::class, 'refreshCache']);
            
            // Cache Management
            Route::get('/cache/status', [AnalyticsController::class, 'getCacheStatus']);
            Route::post('/cache/warm', [AnalyticsController::class, 'warmCache']);
            
            // Real-time Updates
            Route::get('/realtime', [AnalyticsController::class, 'getRealtimeMetrics']);

            // Enhanced Analytics Endpoints
            Route::get('/top-doctors', [AnalyticsController::class, 'getTopRatedDoctors']);
            Route::get('/active-doctors', [AnalyticsController::class, 'getMostActiveDoctors']);
            Route::get('/patient-demographics', [AnalyticsController::class, 'getPatientDemographics']);
            Route::get('/engagement', [AnalyticsController::class, 'getEngagementMetrics']);
            Route::get('/specializations', [AnalyticsController::class, 'getSpecializationDistribution']);
            Route::get('/consultation-trends', [AnalyticsController::class, 'getConsultationTrends']);
            Route::get('/user-trends', [AnalyticsController::class, 'getUserTrends']);
            Route::get('/growth', [AnalyticsController::class, 'getGrowthMetrics']);
            Route::get('/retention', [AnalyticsController::class, 'getUserRetention']);
        });

        // ========== WEBSOCKET/BROADCASTING ROUTES ==========
        /**
         * Real-time Broadcasting & WebSocket
         * POST /api/v1/broadcasting/auth - Authenticate WebSocket channels
         * GET /api/v1/broadcasting/config - Get Pusher configuration
         */
        Route::post('/broadcasting/auth', [BroadcastingController::class, 'authenticate']);
        Route::get('/broadcasting/config', [BroadcastingController::class, 'getConfig']);
    });
});