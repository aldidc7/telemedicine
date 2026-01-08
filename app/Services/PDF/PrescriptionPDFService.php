<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Services\PDF;

use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Prescription PDF Service
 * 
 * Generates PDF files for prescriptions
 */
class PrescriptionPDFService
{
    /**
     * Generate prescription PDF
     */
    public function generate(Prescription $prescription): string
    {
        $data = $this->preparePrescriptionData($prescription);

        $pdf = DomPDF::loadView('pdf.prescription', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->output();
    }

    /**
     * Download prescription PDF
     */
    public function download(Prescription $prescription)
    {
        $filename = "prescription-{$prescription->id}-" . now()->format('Y-m-d') . ".pdf";

        return response()->streamDownload(
            function () use ($prescription) {
                echo $this->generate($prescription);
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Save prescription PDF to storage
     */
    public function save(Prescription $prescription): string
    {
        $filename = "prescriptions/{$prescription->id}/prescription-" . now()->timestamp . ".pdf";

        Storage::disk('private')->put($filename, $this->generate($prescription));

        return $filename;
    }

    /**
     * Send prescription via email
     */
    public function email(Prescription $prescription, $recipientEmail)
    {
        $pdfContent = $this->generate($prescription);
        $filename = "prescription-{$prescription->id}.pdf";

        Mail::send('emails.prescription', ['prescription' => $prescription], function ($message) use (
            $recipientEmail,
            $pdfContent,
            $filename,
            $prescription
        ) {
            $message->to($recipientEmail)
                ->subject("Your Prescription - {$prescription->consultation->doctor->name}")
                ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
        });

        // Log the action
        DB::table('audit_logs')->insert([
            'action' => 'prescription_emailed',
            'entity_id' => $prescription->id,
            'entity_type' => 'Prescription',
            'user_id' => Auth::id(),
            'data' => json_encode(['email' => $recipientEmail]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Prepare prescription data for PDF rendering
     */
    private function preparePrescriptionData(Prescription $prescription): array
    {
        $prescription->load('consultation.patient', 'consultation.doctor');

        $medicines = is_array($prescription->medicines)
            ? $prescription->medicines
            : json_decode($prescription->medicines, true) ?? [];

        return [
            'prescription' => $prescription,
            'patient' => $prescription->consultation->patient,
            'doctor' => $prescription->consultation->doctor,
            'consultation' => $prescription->consultation,
            'medicines' => $medicines,
            'generatedDate' => now()->format('d/m/Y H:i'),
            'companyName' => config('app.name'),
            'companyAddress' => config('app.address', ''),
            'companyPhone' => config('app.phone', ''),
        ];
    }

    /**
     * Generate bulk prescription download (ZIP)
     */
    public function generateBulkZip($prescriptions)
    {
        $zip = new \ZipArchive();
        $zipPath = storage_path('app/temp/' . uniqid() . '.zip');

        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($prescriptions as $prescription) {
                $pdfContent = $this->generate($prescription);
                $filename = "prescription-{$prescription->id}-" . $prescription->consultation->doctor->name . ".pdf";

                $zip->addFromString($filename, $pdfContent);
            }

            $zip->close();

            return $zipPath;
        }

        throw new \Exception('Failed to create ZIP file');
    }

    /**
     * Verify prescription PDF access
     */
    public function canAccessPrescription(Prescription $prescription, $user)
    {
        // Patient can access their own
        if ($user->id === $prescription->patient_id) {
            return true;
        }

        // Doctor can access their own
        if ($user->id === $prescription->doctor_id) {
            return true;
        }

        // Admin can access all
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Log prescription download
     */
    public function logDownload(Prescription $prescription, $user)
    {
        // Increment download count
        $prescription->increment('download_count');

        // Log audit trail
        DB::table('audit_logs')->insert([
            'action' => 'prescription_pdf_downloaded',
            'entity_id' => $prescription->id,
            'user_id' => $user->id,
            'entity_type' => 'prescription',
            'metadata' => json_encode([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
            'created_at' => now(),
        ]);
    }
}
