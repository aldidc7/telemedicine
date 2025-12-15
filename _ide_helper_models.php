<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * ============================================
 * MODEL LOGAKTIVITAS - AUDIT TRAIL AKTIVITAS
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'activity_logs' di database.
 * Mencatat semua aktivitas penting di sistem untuk audit trail.
 * Bersifat immutable (tidak bisa diubah, hanya dibaca dan ditambah).
 *
 * @property int $id - ID log
 * @property int $user_id - Siapa yang melakukan action
 * @property string $aksi - Apa yang dilakukan (create, update, delete, dll)
 * @property string $tipe_model - Jenis model yang diaffected
 * @property int $id_model - ID dari model yang diaffected
 * @property array $nilai_lama - Nilai lama sebelum update (JSON)
 * @property array $nilai_baru - Nilai baru setelah update (JSON)
 * @property string $deskripsi - Deskripsi detail activity
 * @property string $alamat_ip - IP address pengguna
 * @property string $user_agent - Browser/User Agent
 * @property \DateTime $created_at - Waktu activity terjadi
 * @author Aplikasi Telemedicine
 * @version 1.0
 * @property string $action
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $old_values
 * @property string|null $new_values
 * @property string|null $description
 * @property string|null $ip_address
 * @property-read string $nama_pengguna
 * @property-read \App\Models\User $pengguna
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog byAksi($aksi)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog byModel($tipeModel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog terbaru()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * ============================================
 * MODEL ADMIN - DATA PROFIL ADMIN SISTEM
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'admins' di database.
 * Menyimpan data profil dan permission level admin.
 * 
 * Permission Level:
 * - 1 (dasar): Hanya lihat dashboard
 * - 2 (lanjutan): Lihat dashboard, kelola user, lihat logs
 * - 3 (super): Full access ke semua fitur
 *
 * @property int $id - ID admin
 * @property int $user_id - Foreign key ke users
 * @property int $tingkat_izin - Level permission
 * @property string $catatan - Catatan tambahan
 * @author Aplikasi Telemedicine
 * @version 1.0
 * @property int $permission_level
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $nama
 * @property-read string $tingkat_izin_text
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePermissionLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUserId($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * ============================================
 * MODEL DOKTER - DATA PROFIL DOKTER
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'doctors' di database.
 * Menyimpan data profil dan spesialisasi dokter.
 * 
 * Struktur data:
 * - id: unique identifier (primary key)
 * - user_id: foreign key ke tabel users
 * - spesialisasi: spesialisasi dokter (Dokter Anak, Umum, dll)
 * - no_lisensi: nomor lisensi (SIP/STR)
 * - no_telepon: nomor telepon
 * - tersedia: status ketersediaan dokter
 * - maks_konsultasi_simultan: batasan konsultasi simultan
 * 
 * Catatan:
 * - Untuk prototipe skripsi, semua dokter adalah Dokter Anak
 * - Status ketersediaan bisa diupdate via API
 *
 * @property int $id - ID dokter
 * @property int $user_id - Foreign key ke users
 * @property string $spesialisasi - Spesialisasi dokter
 * @property string $no_lisensi - Nomor lisensi
 * @property bool $tersedia - Status ketersediaan
 * @property int $maks_konsultasi_simultan - Batas konsultasi simultan
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2024-01-10
 * @property string $specialization
 * @property string $license_number
 * @property string|null $phone_number
 * @property bool $is_available
 * @property int $max_concurrent_consultations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $profile_photo
 * @property string|null $address
 * @property string|null $place_of_birth
 * @property string|null $birthplace_city
 * @property string|null $marital_status
 * @property string|null $gender
 * @property string|null $blood_type
 * @property string|null $ethnicity
 * @property int $patient_synced
 * @property-read string $nama_lengkap
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Konsultasi> $konsultasi
 * @property-read int|null $konsultasi_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rating> $ratings
 * @property-read int|null $ratings_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter bySpesialisasi($spesialisasi)
 * @method static \Database\Factories\DokterFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter tersedia()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereBirthplaceCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereBloodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereEthnicity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereMaxConcurrentConsultations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter wherePatientSynced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter wherePlaceOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereProfilePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereSpecialization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokter whereUserId($value)
 */
	class Dokter extends \Eloquent {}
}

namespace App\Models{
/**
 * ============================================
 * MODEL KONSULTASI - DATA KONSULTASI
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'consultations' di database.
 * Menyimpan data setiap konsultasi antara pasien dan dokter.
 * 
 * Status Konsultasi:
 * - menunggu: Menunggu dokter merespons
 * - aktif: Dokter sedang melayani konsultasi
 * - selesai: Konsultasi selesai
 * - dibatalkan: Konsultasi dibatalkan
 *
 * @property int $id - ID konsultasi
 * @property int $pasien_id - Foreign key ke patients
 * @property int|null $dokter_id - Foreign key ke doctors
 * @property string $jenis_keluhan - Jenis keluhan
 * @property string $deskripsi - Deskripsi keluhan
 * @property string $status - Status konsultasi
 * @property Carbon|null $waktu_mulai - Waktu mulai
 * @property Carbon|null $waktu_selesai - Waktu selesai
 * @property Carbon|null $waktu_sinkronisasi - Waktu sinkronisasi dengan SIMRS
 * @property string|null $catatan_penutup - Catatan penutup
 * @property bool $sudah_sinkron_simrs - Sudah sync ke SIMRS?
 * @property Carbon $created_at - Waktu dibuat
 * @property Carbon $updated_at - Waktu diupdate
 * @author Aplikasi Telemedicine
 * @version 1.0
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property string $complaint_type
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string|null $closing_notes
 * @property bool $simrs_synced
 * @property \Illuminate\Support\Carbon|null $synced_at
 * @property-read \App\Models\Dokter|null $dokter
 * @property-read int|null $durasi
 * @property-read mixed $nama_dokter
 * @property-read mixed $nama_pasien
 * @property-read \App\Models\Pasien $pasien
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesanChat> $pesanChat
 * @property-read int|null $pesan_chat_count
 * @property-read \App\Models\Rating|null $rating
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi aktif()
 * @method static \Database\Factories\KonsultasiFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi menunggu()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi selesai()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi sudahSinkron()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi terbaru()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereClosingNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereComplaintType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereSimrsSynced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsultasi whereUpdatedAt($value)
 */
	class Konsultasi extends \Eloquent {}
}

namespace App\Models{
/**
 * ============================================
 * MODEL PASIEN - DATA PROFIL PASIEN
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'patients' di database.
 * Menyimpan data demografi dan informasi medis dasar pasien.
 *
 * @property int $id - ID pasien
 * @property int $user_id - Foreign key ke users
 * @property string $nik - Nomor Identitas Kependudukan
 * @property \Date $tgl_lahir - Tanggal lahir
 * @property string $jenis_kelamin - Jenis kelamin
 * @property string $alamat - Alamat tinggal
 * @property string $no_telepon - Nomor telepon
 * @property string $nama_kontak_darurat - Nama kontak darurat
 * @property string $no_kontak_darurat - No. kontak darurat
 * @property string $golongan_darah - Golongan darah
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @property \Illuminate\Support\Carbon $date_of_birth
 * @property string $gender
 * @property string $address
 * @property string $phone_number
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $blood_type
 * @property string|null $allergies
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $place_of_birth
 * @property string|null $marital_status
 * @property string|null $religion
 * @property string|null $ethnicity
 * @property-read string $nama_lengkap
 * @property-read int|null $umur
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Konsultasi> $konsultasi
 * @property-read int|null $konsultasi_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekamMedis> $rekamMedis
 * @property-read int|null $rekam_medis_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PasienFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereBloodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereEmergencyContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereEmergencyContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereEthnicity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien wherePlaceOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pasien whereUserId($value)
 */
	class Pasien extends \Eloquent {}
}

namespace App\Models{
/**
 * ============================================
 * MODEL PESANCHAT - DATA PESAN CHAT
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'chat_messages' di database.
 * Menyimpan semua pesan chat dalam konsultasi.
 *
 * @property int $id - ID pesan
 * @property int $konsultasi_id - Foreign key ke consultations
 * @property int $pengirim_id - Foreign key ke users
 * @property string $pesan - Isi pesan
 * @property string $tipe_pesan - Tipe pesan
 * @property \DateTime|null $dibaca_pada - Waktu dibaca
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @property int $consultation_id
 * @property int $sender_id
 * @property string $message
 * @property string $message_type
 * @property string|null $file_url
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $nama_pengirim
 * @property-read \App\Models\Konsultasi $konsultasi
 * @property-read \App\Models\User $pengirim
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat belumDibaca()
 * @method static \Database\Factories\PesanChatFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat sudahDibaca()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereConsultationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereMessageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanChat whereUpdatedAt($value)
 */
	class PesanChat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $dokter_id
 * @property int $konsultasi_id
 * @property int $rating
 * @property string|null $review
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Dokter $dokter
 * @property-read \App\Models\Konsultasi $konsultasi
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereDokterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereKonsultasiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereUserId($value)
 */
	class Rating extends \Eloquent {}
}

namespace App\Models{
/**
 * ============================================
 * MODEL REKAMMEDIS - DATA REKAM MEDIS
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'medical_records' di database.
 * Menyimpan riwayat medis dan rekam medis pasien dari SIMRS atau lokal.
 *
 * @property int $id - ID rekam medis
 * @property int $pasien_id - Foreign key ke patients
 * @property string $tipe_record - Tipe record (riwayat, diagnosis, dll)
 * @property array $data - Data JSON
 * @property \Date $tgl_record - Tanggal record
 * @property string $sumber - Sumber (SIMRS/LOKAL)
 * @author Aplikasi Telemedicine
 * @version 1.0
 * @property int $patient_id
 * @property string $record_type
 * @property \Illuminate\Support\Carbon $record_date
 * @property string $source
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pasien $pasien
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis byTipe($tipe)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis dariSimrs()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis lokal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis terbaru()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereRecordDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekamMedis whereUpdatedAt($value)
 */
	class RekamMedis extends \Eloquent {}
}

namespace App\Models{
/**
 * ============================================
 * MODEL USER - BASE PENGGUNA SISTEM
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'users' di database.
 * Menyimpan data login semua pengguna (Pasien, Dokter, Admin).
 * 
 * Struktur data:
 * - id: unique identifier (primary key)
 * - name: nama lengkap pengguna
 * - email: email untuk login (unique)
 * - password: password yang sudah di-hash (bcrypt)
 * - role: tipe pengguna (patient, doctor, admin)
 * - is_active: status aktif/tidak aktif akun
 * - last_login_at: kapan terakhir login
 * - created_at, updated_at: timestamp otomatis
 *
 * @property int $id - ID unik pengguna
 * @property string $name - Nama lengkap
 * @property string $email - Email (unik)
 * @property string $password - Password (di-hash)
 * @property string $role - Role: 'pasien', 'dokter', 'admin'
 * @property bool $is_active - Status aktif (true/false)
 * @property \DateTime $last_login_at - Waktu login terakhir
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @property string|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \App\Models\Dokter|null $dokter
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActivityLog> $logAktivitas
 * @property-read int|null $log_aktivitas_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Pasien|null $pasien
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesanChat> $pesanChat
 * @property-read int|null $pesan_chat_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User admin()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User aktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User dokter()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User pasien()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User tidakAktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

