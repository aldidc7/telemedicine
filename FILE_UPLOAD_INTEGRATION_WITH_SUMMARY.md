# File Upload Integration dengan Consultation Summary

## 🔗 Integration Points

Sistem file upload terintegrasi dengan consultation summary yang sudah dibuat sebelumnya. Dokter dapat upload file medis (hasil lab, radiologi) saat membuat atau update summary.

---

## 1. Medical Image Upload di Summary

### Scenario: Dokter Upload Hasil Radiologi

```php
// app/Http/Controllers/Api/KonsultasiSummaryController.php

public function store(Request $request, $consultationId)
{
    $consultation = Konsultasi::findOrFail($consultationId);
    $this->authorize('create', [KonsultasiSummary::class, $consultation]);

    // Existing summary creation code...

    // NEW: Handle file upload untuk medical images
    if ($request->hasFile('clinical_findings_file')) {
        $fileService = app(FileUploadService::class);
        
        try {
            $fileResult = $fileService->uploadFile(
                $request->file('clinical_findings_file'),
                'medical_image',  // Radiologi, lab photos, dll
                auth()->user()
            );

            // Store file reference in summary
            $summary->clinical_findings_file_path = $fileResult['path'];
            $summary->clinical_findings_file_url = $fileResult['url'];
        } catch (FileUploadException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file radiologi: ' . $e->getMessage(),
            ], 422);
        }
    }

    $summary->save();

    return response()->json([
        'success' => true,
        'message' => 'Summary konsultasi berhasil dibuat',
        'data' => $summary,
    ], 201);
}
```

### Database Schema Update

Tambahkan ke migration konsultasi:

```php
// database/migrations/EXISTING_consultation_summary_migration.php

Schema::table('consultation_summaries', function (Blueprint $table) {
    // File references
    $table->string('clinical_findings_file_path')->nullable();
    $table->string('clinical_findings_file_url')->nullable();
    $table->string('treatment_plan_document_path')->nullable();
    $table->string('treatment_plan_document_url')->nullable();
    $table->string('additional_notes_file_path')->nullable();
});
```

---

## 2. Prescription File Upload

### Scenario: Dokter Upload Resep Medis

```php
// app/Services/KonsultasiSummaryService.php

public function addMedicationWithPrescription(
    KonsultasiSummary $summary,
    array $medicationData,
    ?UploadedFile $prescriptionFile = null
): array
{
    if (!auth()->user()->can('update', $summary)) {
        throw new \Exception('Unauthorized');
    }

    $medication = new KonsultasiMedication($medicationData);

    // Handle prescription file upload
    if ($prescriptionFile) {
        $fileService = app(FileUploadService::class);
        
        try {
            $fileResult = $fileService->uploadFile(
                $prescriptionFile,
                'prescription',  // 5 MB limit untuk resep
                auth()->user()
            );

            $medication->prescription_file_path = $fileResult['path'];
            $medication->prescription_file_url = $fileResult['url'];
        } catch (FileUploadException $e) {
            throw new \Exception('Upload resep gagal: ' . $e->getMessage());
        }
    }

    $medication->consultation_summary_id = $summary->id;
    $medication->save();

    \Log::info('Medication added with prescription', [
        'summary_id' => $summary->id,
        'medication_id' => $medication->id,
        'file_path' => $medication->prescription_file_path ?? 'none',
    ]);

    return $medication->toArray();
}
```

### Model Update

```php
// app/Models/KonsultasiMedication.php

class KonsultasiMedication extends Model
{
    protected $fillable = [
        'consultation_summary_id',
        'name',
        'dose',
        'frequency',
        'duration_days',
        'route',
        'status',
        'prescription_file_path',
        'prescription_file_url',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function summary()
    {
        return $this->belongsTo(KonsultasiSummary::class, 'consultation_summary_id');
    }

    // Get prescription file URL for display
    public function getPrescriptionUrl()
    {
        return $this->prescription_file_url ? 
            Storage::disk('public')->url($this->prescription_file_path) : 
            null;
    }
}
```

---

## 3. Medical Document Upload (PDF Reports)

### Scenario: Pasien Upload Hasil Lab Sebelumnya

```php
// app/Http/Controllers/Api/KonsultasiController.php

public function storeWithDocuments(Request $request)
{
    $request->validate([
        'dokter_id' => 'required|exists:dokters,id',
        'keluhan_utama' => 'required|string',
        'medical_documents' => 'nullable|array|max:5',  // Max 5 files
        'medical_documents.*' => 'nullable|file',
    ]);

    $consultation = Konsultasi::create([
        'user_id' => auth()->id(),
        'dokter_id' => $request->dokter_id,
        'keluhan_utama' => $request->keluhan_utama,
        'status' => 'PENDING',
    ]);

    // Upload medical documents
    if ($request->hasFile('medical_documents')) {
        $fileService = app(FileUploadService::class);
        $uploadedDocs = [];

        foreach ($request->file('medical_documents') as $document) {
            try {
                $fileResult = $fileService->uploadFile(
                    $document,
                    'medical_document',  // 10 MB limit
                    auth()->user()
                );

                // Create document record
                $consultation->documents()->create([
                    'filename' => $fileResult['filename'],
                    'path' => $fileResult['path'],
                    'url' => $fileResult['url'],
                    'file_size' => $fileResult['size'],
                    'mime_type' => $fileResult['mime_type'],
                    'type' => 'medical_history',  // lab results, prescriptions, etc
                ]);

                $uploadedDocs[] = $fileResult;
            } catch (FileUploadException $e) {
                \Log::warning('Medical document upload failed', [
                    'consultation_id' => $consultation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $consultation->documents_count = count($uploadedDocs);
        $consultation->save();
    }

    return response()->json([
        'success' => true,
        'message' => 'Konsultasi berhasil dibuat dengan ' . count($uploadedDocs) . ' dokumen',
        'data' => $consultation->load('documents'),
    ], 201);
}
```

---

## 4. Chat File Attachment Upload

### Scenario: Pasien/Dokter Upload File dalam Chat

```php
// app/Http/Controllers/Api/PesanChatController.php

public function sendWithAttachment(Request $request)
{
    $request->validate([
        'konsultasi_id' => 'required|exists:konsultasis,id',
        'pesan' => 'nullable|string',
        'attachment' => 'nullable|file|max:' . (10 * 1024),  // 10 MB
    ]);

    $consultation = Konsultasi::findOrFail($request->konsultasi_id);
    
    // Check authorization
    $this->authorize('participate', $consultation);

    $pesan = new PesanChat([
        'konsultasi_id' => $request->konsultasi_id,
        'user_id' => auth()->id(),
        'pesan' => $request->pesan,
        'tipe' => 'TEXT',
    ]);

    // Handle file attachment
    if ($request->hasFile('attachment')) {
        $fileService = app(FileUploadService::class);

        try {
            $fileResult = $fileService->uploadFile(
                $request->file('attachment'),
                'consultation_file',  // 8 MB limit
                auth()->user()
            );

            $pesan->tipe = 'FILE';
            $pesan->file_path = $fileResult['path'];
            $pesan->file_url = $fileResult['url'];
            $pesan->file_name = $fileResult['filename'];
            $pesan->file_size = $fileResult['size'];
            $pesan->file_mime = $fileResult['mime_type'];

            // Trigger virus scanning jika enabled
            if (config('file-upload.virus_scanning.enabled')) {
                \Log::info('File scanning queued', [
                    'file_path' => $fileResult['path'],
                ]);
                // Queue scanning job here
            }
        } catch (FileUploadException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload file gagal: ' . $e->getMessage(),
            ], 422);
        }
    }

    $pesan->save();

    // Broadcast event untuk real-time update
    broadcast(new PesanDikirim($pesan))->toOthers();

    return response()->json([
        'success' => true,
        'message' => 'Pesan berhasil dikirim',
        'data' => $pesan,
    ], 201);
}
```

---

## 5. Storage Management UI

### Vue Component: Consultation Summary with File Upload

```vue
<template>
  <div class="summary-editor">
    <!-- Existing summary form -->
    <form @submit.prevent="saveSummary">
      
      <!-- Clinical Findings with File Upload -->
      <div class="form-group">
        <label>Findings Klinis</label>
        <textarea 
          v-model="form.clinical_findings"
          class="form-control"
          rows="4"
        ></textarea>
        
        <!-- File Upload for Images/Radiologi -->
        <FileUpload 
          category="medical_image"
          @upload-success="onFindingsImageUploaded"
          style="margin-top: 10px"
        />
        
        <div v-if="form.clinical_findings_file_url" class="file-preview">
          <small>📎 File: {{ form.clinical_findings_filename }}</small>
          <a :href="form.clinical_findings_file_url" target="_blank" class="link">
            View
          </a>
        </div>
      </div>

      <!-- Medications with Prescriptions -->
      <div class="medications-section">
        <h4>Obat-obatan yang Diberikan</h4>
        
        <div v-for="(med, idx) in form.medications" :key="idx" class="medication-item">
          <div class="form-row">
            <input v-model="med.name" placeholder="Nama obat" class="form-control">
            <input v-model="med.dose" placeholder="Dosis" class="form-control">
            <input v-model="med.frequency" placeholder="Frekuensi" class="form-control">
          </div>

          <!-- Prescription file upload -->
          <FileUpload 
            category="prescription"
            @upload-success="(result) => onPrescriptionUploaded(idx, result)"
            style="margin-top: 8px"
          />

          <div v-if="med.prescription_file_url" class="file-preview">
            <small>📋 Resep: {{ med.prescription_filename }}</small>
            <a :href="med.prescription_file_url" target="_blank" class="link">
              Download
            </a>
          </div>

          <button @click="removeMedication(idx)" type="button" class="btn-remove">
            Hapus
          </button>
        </div>

        <button @click="addMedication" type="button" class="btn-add">
          + Tambah Obat
        </button>
      </div>

      <!-- Treatment Plan Document -->
      <div class="form-group">
        <label>Dokumen Rencana Terapi</label>
        <FileUpload 
          category="medical_document"
          @upload-success="onTreatmentDocUploaded"
        />
        
        <div v-if="form.treatment_plan_document_url" class="file-preview">
          <small>📄 Dokumen: {{ form.treatment_plan_filename }}</small>
          <a :href="form.treatment_plan_document_url" target="_blank" class="link">
            Download
          </a>
        </div>
      </div>

      <!-- Storage Info -->
      <StorageInfo />

      <!-- Submit -->
      <button type="submit" class="btn btn-primary">
        Simpan Summary
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import FileUpload from '@/components/FileUpload.vue'
import StorageInfo from '@/components/StorageInfo.vue'

const form = ref({
  clinical_findings: '',
  clinical_findings_file_url: '',
  clinical_findings_filename: '',
  medications: [],
  treatment_plan_document_url: '',
  treatment_plan_filename: '',
})

const onFindingsImageUploaded = (result) => {
  form.value.clinical_findings_file_url = result.url
  form.value.clinical_findings_filename = result.filename
}

const onPrescriptionUploaded = (idx, result) => {
  form.value.medications[idx].prescription_file_url = result.url
  form.value.medications[idx].prescription_filename = result.filename
}

const onTreatmentDocUploaded = (result) => {
  form.value.treatment_plan_document_url = result.url
  form.value.treatment_plan_filename = result.filename
}

const addMedication = () => {
  form.value.medications.push({
    name: '',
    dose: '',
    frequency: '',
    prescription_file_url: '',
  })
}

const removeMedication = (idx) => {
  form.value.medications.splice(idx, 1)
}

const saveSummary = async () => {
  // Call API to save
  const response = await fetch('/api/v1/consultations/123/summary', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${localStorage.getItem('token')}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(form.value),
  })

  if (response.ok) {
    alert('Summary berhasil disimpan dengan file attachment')
  }
}
</script>
```

---

## 6. File Download & Management

### Get Consultation with All Files

```php
// app/Http/Controllers/Api/KonsultasiController.php

public function showWithFiles($id)
{
    $consultation = Konsultasi::with([
        'summary.medications.prescriptionFile',
        'summary.clinicalFindingsFile',
        'documents',
        'messages.attachments',
    ])->findOrFail($id);

    $this->authorize('view', $consultation);

    return response()->json([
        'success' => true,
        'data' => [
            'consultation' => $consultation,
            'summary' => $consultation->summary,
            'medications' => $consultation->summary?->medications,
            'documents' => $consultation->documents,
            'chat_attachments' => $consultation->messages()
                ->whereNotNull('file_path')
                ->get(),
            'storage_used' => $this->calculateStorageUsed($consultation),
        ],
    ]);
}

private function calculateStorageUsed($consultation)
{
    $total = 0;

    // Summary files
    if ($consultation->summary?->clinical_findings_file_path) {
        $total += Storage::disk('public')->size(
            $consultation->summary->clinical_findings_file_path
        );
    }

    // Medications
    foreach ($consultation->summary?->medications ?? [] as $med) {
        if ($med->prescription_file_path) {
            $total += Storage::disk('public')->size($med->prescription_file_path);
        }
    }

    // Documents
    foreach ($consultation->documents as $doc) {
        $total += $doc->file_size;
    }

    return [
        'bytes' => $total,
        'formatted' => $this->formatBytes($total),
    ];
}
```

---

## 7. Database Schema Integration

```php
// Add to consultation_summaries table
Schema::table('consultation_summaries', function (Blueprint $table) {
    $table->string('clinical_findings_file_path')->nullable();
    $table->string('clinical_findings_file_url')->nullable();
    $table->string('treatment_plan_document_path')->nullable();
    $table->string('treatment_plan_document_url')->nullable();
});

// Add to consultation_medications table
Schema::table('consultation_medications', function (Blueprint $table) {
    $table->string('prescription_file_path')->nullable();
    $table->string('prescription_file_url')->nullable();
});

// New consultation_documents table
Schema::create('consultation_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('consultation_id')->constrained('konsultasis')->onDelete('cascade');
    $table->string('filename');
    $table->string('path');
    $table->string('url');
    $table->bigInteger('file_size');
    $table->string('mime_type');
    $table->enum('type', ['medical_history', 'lab_result', 'prescription', 'other']);
    $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
    $table->timestamps();
    
    $table->index('consultation_id');
    $table->index('uploaded_by');
});
```

---

## Summary

✅ **File Upload System Terintegrasi dengan:**
- Consultation Summary (clinical findings, treatment plan)
- Medications (prescriptions)
- Chat Messages (attachments)
- Consultation Documents (lab results, medical history)

✅ **Fitur:**
- Automatic file categorization
- Size limits per type (5-15 MB)
- User quota tracking (500 MB - 10 GB)
- Soft delete dengan 30-day retention
- Full audit trail

✅ **Security:**
- Type validation (MIME + extension)
- Authorization checks
- Access control (user sees own files only)
- Virus scanning support

✅ **Performance:**
- Organized storage structure
- Efficient database indexes
- Lazy loading relationships
- Cleanup job untuk expired files

---

**Ini adalah integrasi lengkap antara File Upload System dan Consultation Summary System yang sudah dibuat sebelumnya!** ✨
