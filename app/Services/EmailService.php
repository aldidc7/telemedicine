<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Email Service
 * 
 * Handle sending notifications via email
 */
class EmailService
{
    /**
     * Send appointment reminder email
     */
    public function sendAppointmentReminder($appointment): void
    {
        try {
            $patient = $appointment->patient;
            $doctor = $appointment->doctor;
            $scheduledDate = $appointment->scheduled_at->format('d M Y H:i');

            $subject = "Pengingat: Janji Temu dengan Dr. {$doctor->nama}";
            $body = <<<HTML
            <h2>Pengingat Janji Temu</h2>
            <p>Halo {$patient->nama},</p>
            <p>Kami ingin mengingatkan Anda tentang janji temu yang akan datang:</p>
            <ul>
                <li><strong>Dokter:</strong> Dr. {$doctor->nama}</li>
                <li><strong>Tanggal & Waktu:</strong> {$scheduledDate}</li>
                <li><strong>Tipe:</strong> {$appointment->type}</li>
            </ul>
            <p>Pastikan Anda hadir tepat waktu. Jika perlu membatalkan atau menunda, silakan hubungi kami sebelumnya.</p>
            <p>Terima kasih,<br>Tim Telemedicine</p>
            HTML;

            $this->sendEmail($patient->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error("Failed to send appointment reminder email: " . $e->getMessage());
        }
    }

    /**
     * Send appointment confirmation email
     */
    public function sendAppointmentConfirmation($appointment): void
    {
        try {
            $patient = $appointment->patient;
            $doctor = $appointment->doctor;
            $scheduledDate = $appointment->scheduled_at->format('d M Y H:i');

            $subject = "Konfirmasi Janji Temu";
            $body = <<<HTML
            <h2>Janji Temu Dikonfirmasi</h2>
            <p>Halo {$patient->nama},</p>
            <p>Janji temu Anda telah dikonfirmasi dengan detail berikut:</p>
            <ul>
                <li><strong>Dokter:</strong> Dr. {$doctor->nama}</li>
                <li><strong>Spesialisasi:</strong> {$doctor->spesialisasi}</li>
                <li><strong>Tanggal & Waktu:</strong> {$scheduledDate}</li>
                <li><strong>Durasi:</strong> {$appointment->duration_minutes} menit</li>
            </ul>
            <p>Silakan login ke aplikasi kami untuk melihat detail lengkap janji temu Anda.</p>
            <p>Terima kasih,<br>Tim Telemedicine</p>
            HTML;

            $this->sendEmail($patient->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error("Failed to send appointment confirmation email: " . $e->getMessage());
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation($payment, User $user): void
    {
        try {
            $amount = number_format($payment->amount, 0, ',', '.');
            $transactionId = $payment->transaction_id;
            $date = $payment->created_at->format('d M Y H:i');

            $subject = "Konfirmasi Pembayaran";
            $body = <<<HTML
            <h2>Pembayaran Berhasil</h2>
            <p>Halo {$user->nama},</p>
            <p>Pembayaran Anda telah berhasil diproses. Berikut detail transaksi:</p>
            <ul>
                <li><strong>Nomor Transaksi:</strong> {$transactionId}</li>
                <li><strong>Jumlah:</strong> Rp {$amount}</li>
                <li><strong>Tanggal:</strong> {$date}</li>
                <li><strong>Metode Pembayaran:</strong> {$payment->payment_method}</li>
                <li><strong>Status:</strong> BERHASIL</li>
            </ul>
            <p>Invoice lengkap dapat diunduh dari aplikasi kami.</p>
            <p>Terima kasih telah menggunakan layanan kami,<br>Tim Telemedicine</p>
            HTML;

            $this->sendEmail($user->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error("Failed to send payment confirmation email: " . $e->getMessage());
        }
    }

    /**
     * Send credential verification status email
     */
    public function sendCredentialVerificationEmail($doctor, $credential, $status, $reason = null): void
    {
        try {
            $credentialType = $credential->credential_type;
            $statusLabel = $status === 'approved' ? 'DISETUJUI' : 'DITOLAK';

            $subject = "Status Verifikasi Kredensial: {$credentialType}";
            
            $reasonText = $reason ? "<p><strong>Alasan:</strong> {$reason}</p>" : '';
            
            $body = <<<HTML
            <h2>Hasil Verifikasi Kredensial</h2>
            <p>Halo Dr. {$doctor->nama},</p>
            <p>Kredensial Anda telah diproses dengan hasil sebagai berikut:</p>
            <ul>
                <li><strong>Tipe Kredensial:</strong> {$credentialType}</li>
                <li><strong>Status:</strong> <span style="color: {$this->getStatusColor($status)}">{$statusLabel}</span></li>
            </ul>
            {$reasonText}
            <p>Untuk informasi lebih lanjut atau pertanyaan, silakan hubungi tim kami.</p>
            <p>Terima kasih,<br>Tim Telemedicine</p>
            HTML;

            $this->sendEmail($doctor->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error("Failed to send credential verification email: " . $e->getMessage());
        }
    }

    /**
     * Send prescription email
     */
    public function sendPrescriptionEmail($prescription, User $patient): void
    {
        try {
            $doctor = $prescription->doctor;
            $medicines = $prescription->medicines ?? [];

            $medicinesHTML = '';
            if (is_array($medicines)) {
                foreach ($medicines as $medicine) {
                    $medicinesHTML .= "<li>{$medicine['name']} - {$medicine['dosage']} ({$medicine['duration']})</li>";
                }
            }

            $subject = "Resep Obat dari Dr. {$doctor->nama}";
            $body = <<<HTML
            <h2>Resep Obat</h2>
            <p>Halo {$patient->nama},</p>
            <p>Berikut adalah resep obat dari Dr. {$doctor->nama} untuk Anda:</p>
            <h3>Obat yang Diresepkan:</h3>
            <ul>
                {$medicinesHTML}
            </ul>
            <p><strong>Catatan:</strong> {$prescription->notes}</p>
            <p>Silakan berikan resep ini ke apotek untuk mengambil obat. Jika ada pertanyaan, hubungi Dr. {$doctor->nama}.</p>
            <p>Terima kasih,<br>Tim Telemedicine</p>
            HTML;

            $this->sendEmail($patient->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error("Failed to send prescription email: " . $e->getMessage());
        }
    }

    /**
     * Send welcome email for new user
     */
    public function sendWelcomeEmail(User $user): void
    {
        try {
            $subject = "Selamat Datang di Telemedicine RSUD dr. R. Soedarsono";
            $role = $user->role === 'doctor' ? 'Dokter' : 'Pasien';

            $body = <<<HTML
            <h2>Selamat Datang!</h2>
            <p>Halo {$user->nama},</p>
            <p>Kami senang menyambut Anda sebagai {$role} baru di platform Telemedicine RSUD dr. R. Soedarsono.</p>
            <p>Dengan aplikasi kami, Anda dapat:</p>
            <ul>
                <li>Melakukan konsultasi dengan dokter profesional</li>
                <li>Membuat janji temu dengan mudah</li>
                <li>Mengelola riwayat kesehatan Anda</li>
                <li>Menerima notifikasi real-time</li>
            </ul>
            <p>Silakan login ke aplikasi kami untuk memulai. Jika memiliki pertanyaan, hubungi tim support kami.</p>
            <p>Selamat Anda bergabung!<br>Tim Telemedicine</p>
            HTML;

            $this->sendEmail($user->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email: " . $e->getMessage());
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(User $user, string $resetToken): void
    {
        try {
            $resetLink = config('app.frontend_url') . "/reset-password?token={$resetToken}&email=" . urlencode($user->email);

            $subject = "Reset Password";
            $body = <<<HTML
            <h2>Reset Password Anda</h2>
            <p>Halo {$user->nama},</p>
            <p>Kami menerima permintaan untuk mereset password akun Anda. Klik link di bawah ini untuk membuat password baru:</p>
            <p><a href="{$resetLink}" style="background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">Reset Password</a></p>
            <p>Atau salin link ini: {$resetLink}</p>
            <p>Link ini akan berlaku selama 1 jam. Jika Anda tidak meminta ini, abaikan email ini.</p>
            <p>Terima kasih,<br>Tim Telemedicine</p>
            HTML;

            $this->sendEmail($user->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email: " . $e->getMessage());
        }
    }

    /**
     * Generic email sender
     */
    private function sendEmail(string $recipientEmail, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($message) use ($recipientEmail, $subject) {
                $message->to($recipientEmail)
                    ->subject($subject)
                    ->html();
            });

            Log::info("Email sent to {$recipientEmail}: {$subject}");
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$recipientEmail}: " . $e->getMessage());
        }
    }

    /**
     * Get color for status
     */
    private function getStatusColor(string $status): string
    {
        return $status === 'approved' ? 'green' : 'red';
    }
}
