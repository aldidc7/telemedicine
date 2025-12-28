<!-- resources/views/pages/privacy-policy.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - Telemedicine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Navigation -->
    <nav class="sticky top-0 bg-white shadow-sm z-40">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="text-xl font-bold text-indigo-600">
                [TELEMEDICINE] Telemedicine
            </a>
            <a href="/" class="text-slate-600 hover:text-slate-900">
                ← Kembali
            </a>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-linear-to-b from-indigo-600 to-indigo-700 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-black mb-4">[PRIVACY] Kebijakan Privasi</h1>
            <p class="text-indigo-100 text-lg">
                Kami berkomitmen melindungi privasi dan data medis Anda
            </p>
            <div class="mt-6 text-indigo-100 text-sm">
                <p>Terakhir diperbarui: <span class="font-semibold">Desember 20, 2025</span></p>
            </div>
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold text-slate-900 mb-4">[CONTENTS] Daftar Isi</h2>
            <ol class="space-y-2 text-slate-700">
                <li><a href="#pendahuluan" class="text-indigo-600 hover:underline">1. Pendahuluan</a></li>
                <li><a href="#data-yang-dikumpulkan" class="text-indigo-600 hover:underline">2. Data yang Dikumpulkan</a></li>
                <li><a href="#penggunaan-data" class="text-indigo-600 hover:underline">3. Penggunaan Data</a></li>
                <li><a href="#keamanan-data" class="text-indigo-600 hover:underline">4. Keamanan Data</a></li>
                <li><a href="#hak-pasien" class="text-indigo-600 hover:underline">5. Hak Pasien</a></li>
                <li><a href="#penyimpanan-data" class="text-indigo-600 hover:underline">6. Penyimpanan Data</a></li>
                <li><a href="#pihak-ketiga" class="text-indigo-600 hover:underline">7. Pihak Ketiga</a></li>
                <li><a href="#telemedicine" class="text-indigo-600 hover:underline">8. Informasi Telemedicine</a></li>
                <li><a href="#perubahan-kebijakan" class="text-indigo-600 hover:underline">9. Perubahan Kebijakan</a></li>
                <li><a href="#kontak" class="text-indigo-600 hover:underline">10. Hubungi Kami</a></li>
            </ol>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 pb-16">
        <!-- 1. Pendahuluan -->
        <section id="pendahuluan" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">1. [INTRO] Pendahuluan</h2>
            <p class="text-slate-700 leading-relaxed mb-4">
                Selamat datang di aplikasi Telemedicine kami. Kami mengerti bahwa privasi Anda sangat penting, terutama 
                dalam hal informasi kesehatan. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, 
                melindungi, dan mengelola data pribadi dan medis Anda.
            </p>
            <p class="text-slate-700 leading-relaxed">
                Kami berkomitmen untuk mematuhi semua regulasi kesehatan internasional, termasuk HIPAA-equivalent standards, 
                Indonesia Health Law 36/2009, dan telemedicine guidelines dari WHO dan India Ministry of Health.
            </p>
        </section>

        <!-- 2. Data yang Dikumpulkan -->
        <section id="data-yang-dikumpulkan" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">2. [DATA] Data yang Dikumpulkan</h2>
            
            <div class="space-y-4">
                <div class="border-l-4 border-indigo-600 pl-4">
                    <h3 class="font-semibold text-slate-900 mb-2">[PERSONAL] Data Pribadi</h3>
                    <ul class="text-slate-700 space-y-1">
                        <li>• Nama lengkap</li>
                        <li>• Email address</li>
                        <li>• Nomor telepon</li>
                        <li>• Alamat dan lokasi</li>
                        <li>• NIK (bagi pasien)</li>
                        <li>• Tanggal lahir dan jenis kelamin</li>
                    </ul>
                </div>

                <div class="border-l-4 border-red-600 pl-4">
                    <h3 class="font-semibold text-slate-900 mb-2">[MEDICAL] Data Medis (PHI - Protected Health Information)</h3>
                    <ul class="text-slate-700 space-y-1">
                        <li>• Keluhan atau gejala</li>
                        <li>• Riwayat kesehatan</li>
                        <li>• Hasil pemeriksaan dan lab</li>
                        <li>• Resep dan obat yang digunakan</li>
                        <li>• Catatan dokter dan closing notes</li>
                        <li>• Historis konsultasi</li>
                    </ul>
                </div>

                <div class="border-l-4 border-slate-400 pl-4">
                    <h3 class="font-semibold text-slate-900 mb-2">[TECH] Data Teknis</h3>
                    <ul class="text-slate-700 space-y-1">
                        <li>• IP address</li>
                        <li>• Jenis browser dan device</li>
                        <li>• User agent information</li>
                        <li>• Cookies dan session data</li>
                        <li>• Activity logs (tindakan yang dilakukan)</li>
                    </ul>
                </div>
            </div>

            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-blue-900 text-sm">
                    <strong>ℹ️ Catatan:</strong> Data medis Anda dikumpulkan HANYA untuk tujuan diagnosis, perawatan, 
                    dan follow-up konsultasi. Data ini diklasifikasikan sebagai CONFIDENTIAL dan mendapat perlindungan tertinggi.
                </p>
            </div>
        </section>

        <!-- 3. Penggunaan Data -->
        <section id="penggunaan-data" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">3. [USE] Penggunaan Data</h2>
            
            <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-4">
                <h3 class="font-semibold text-green-900 mb-2">[YES] Kami MENGGUNAKAN data untuk:</h3>
                <ul class="text-green-800 space-y-1">
                    <li>[YES] Memberikan layanan telemedicine yang aman dan berkualitas</li>
                    <li>[YES] Diagnosis, perawatan, dan follow-up medis</li>
                    <li>[YES] Manajemen rekam medis elektronik</li>
                    <li>[YES] Pemrosesan pembayaran dan asuransi kesehatan</li>
                    <li>[YES] Kepatuhan dengan regulasi kesehatan</li>
                    <li>[YES] Peningkatan layanan dengan data yang di-anonimkan</li>
                    <li>[YES] Notifikasi penting tentang kesehatan Anda</li>
                    <li>[YES] Research dan statistics (dengan anonimisasi)</li>
                </ul>
            </div>

            <div class="bg-red-50 border-l-4 border-red-600 p-4">
                <h3 class="font-semibold text-red-900 mb-2">[NO] Kami TIDAK PERNAH:</h3>
                <ul class="text-red-800 space-y-1">
                    <li>[NO] Menjual data Anda kepada pihak ketiga komersial</li>
                    <li>[NO] Menggunakan data medis untuk marketing atau advertising</li>
                    <li>[NO] Membagikan data Anda dengan pihak yang tidak berwenang</li>
                    <li>[NO] Menggunakan data tanpa izin atau persetujuan Anda</li>
                    <li>[NO] Menyimpan data lebih lama dari yang diperlukan</li>
                </ul>
            </div>
        </section>

        <!-- 4. Keamanan Data -->
        <section id="keamanan-data" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">4. [SECURITY] Keamanan Data</h2>
            
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="text-3xl shrink-0">[ENC]</div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Enkripsi Transmisi</h3>
                        <p class="text-slate-700">Semua data dikirim menggunakan HTTPS/TLS 1.2+ encryption. 
                        Anda dapat memverifikasi dengan melihat lock icon di browser.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-3xl shrink-0">[DB]</div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Enkripsi Database</h3>
                        <p class="text-slate-700">Data medis dienkripsi di database menggunakan AES-256 encryption. 
                        Hanya orang dengan decryption key yang dapat membaca data.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-3xl shrink-0">[KEY]</div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Access Control</h3>
                        <p class="text-slate-700">Role-based access control (RBAC) memastikan hanya staff yang 
                        berwenang dapat mengakses data Anda. Anda dapat melihat siapa yang akses data Anda.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-3xl shrink-0">[LOG]</div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Audit Logging</h3>
                        <p class="text-slate-700">Setiap akses ke data Anda dicatat (immutable logs). Anda dapat 
                        melihat riwayat lengkap akses dalam "Activity Log" Anda.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-3xl shrink-0">[BACKUP]</div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Backup & Disaster Recovery</h3>
                        <p class="text-slate-700">Data di-backup secara regular dan disimpan aman di multiple locations. 
                        Backup juga terenkripsi untuk perlindungan maksimal.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Hak Pasien -->
        <section id="hak-pasien" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">5. 👤 Hak Pasien</h2>
            
            <div class="space-y-4">
                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">📥 Hak Akses (Right to Access)</h3>
                    <p class="text-indigo-800 text-sm">Anda berhak mengakses dan melihat semua data medis Anda kapan saja. 
                    Gunakan menu "Rekam Medis Saya" atau hubungi support untuk download lengkap.</p>
                </div>

                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">✏️ Hak Koreksi (Right to Correct)</h3>
                    <p class="text-indigo-800 text-sm">Jika ada data yang salah atau tidak lengkap, Anda dapat mengajukan 
                    permintaan koreksi. Kami akan mengubah dan mencatat perubahan tersebut dalam audit trail.</p>
                </div>

                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">📤 Hak Portabilitas (Right to Portability)</h3>
                    <p class="text-indigo-800 text-sm">Anda berhak mengunduh semua data medis Anda dalam format standar 
                    (PDF, CSV) dan membawanya ke provider kesehatan lain. Gunakan fitur "Export Data".</p>
                </div>

                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">🚫 Hak Penghapusan (Right to be Forgotten)</h3>
                    <p class="text-indigo-800 text-sm">Anda dapat meminta penghapusan data pribadi yang tidak relevan 
                    (dengan catatan: data medis disimpan 7-10 tahun sesuai regulasi). Kami menggunakan soft-delete 
                    (data tetap aman, tidak benar-benar dihapus).</p>
                </div>

                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <h3 class="font-semibold text-amber-900 mb-2">⏸️ Hak Withdraw Consent (Tarik Kembali Persetujuan)</h3>
                    <p class="text-amber-800 text-sm">Anda dapat mencabut consent kapan saja dari menu "Pengaturan > 
                    Consent". Jika ditarik, kami tidak lagi dapat memberikan layanan telemedicine sampai Anda 
                    memberikan consent baru.</p>
                </div>
            </div>
        </section>

        <!-- 6. Penyimpanan Data -->
        <section id="penyimpanan-data" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">6. [RETENTION] Penyimpanan Data</h2>
            
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="text-left p-3 font-semibold text-slate-900">Jenis Data</th>
                        <th class="text-left p-3 font-semibold text-slate-900">Periode Penyimpanan</th>
                        <th class="text-left p-3 font-semibold text-slate-900">Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="p-3 text-slate-700">Rekam Medis Lengkap</td>
                        <td class="p-3 text-slate-700">7-10 tahun</td>
                        <td class="p-3 text-slate-700">Regulasi Indonesia Health Law 36/2009 & JCI Standard</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3 text-slate-700">Data Resep & Obat</td>
                        <td class="p-3 text-slate-700">7-10 tahun</td>
                        <td class="p-3 text-slate-700">Tracking efek samping & drug interactions</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3 text-slate-700">Activity Logs</td>
                        <td class="p-3 text-slate-700">1-2 tahun</td>
                        <td class="p-3 text-slate-700">Audit trail & security monitoring</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3 text-slate-700">Backup Data</td>
                        <td class="p-3 text-slate-700">90 hari</td>
                        <td class="p-3 text-slate-700">Disaster recovery & business continuity</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3 text-slate-700">Chat & Messaging</td>
                        <td class="p-3 text-slate-700">Sesuai retensi rekam medis</td>
                        <td class="p-3 text-slate-700">Bagian dari rekam medis elektronik</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-900 text-sm">
                    <strong>[SOFTDELETE] SOFT-DELETE POLICY:</strong> Ketika data dihapus, kami menggunakan "soft-delete" - 
                    data tetap ada di database (aman dari kehilangan) tapi tidak bisa diakses user normal. 
                    Ini adalah best practice telemedicine untuk compliance & disaster recovery.
                </p>
            </div>
        </section>

        <!-- 7. Pihak Ketiga -->
        <section id="pihak-ketiga" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">7. [THIRD-PARTY] Pihak Ketiga</h2>
            
            <p class="text-slate-700 mb-4">
                Data Anda mungkin dibagikan dengan pihak ketiga dalam situasi berikut:
            </p>

            <div class="space-y-3">
                <div class="p-4 border border-slate-200 rounded-lg">
                    <h3 class="font-semibold text-slate-900">[PROVIDER] Penyedia Kesehatan</h3>
                    <p class="text-slate-700 text-sm">Dokter, rumah sakit, atau klinik yang terlibat dalam perawatan Anda. 
                    Semua harus menandatangani Data Processing Agreement (DPA).</p>
                </div>

                <div class="p-4 border border-slate-200 rounded-lg">
                    <h3 class="font-semibold text-slate-900">[PHARMACY] Apotek & Lab</h3>
                    <p class="text-slate-700 text-sm">Untuk memproses resep atau hasil lab. Data dibatasi hanya apa yang 
                    relevan untuk transaksi tersebut.</p>
                </div>

                <div class="p-4 border border-slate-200 rounded-lg">
                    <h3 class="font-semibold text-slate-900">[INSURANCE] Asuransi Kesehatan (BPJS/Private)</h3>
                    <p class="text-slate-700 text-sm">Untuk klaim dan billing. Hanya dengan izin eksplisit Anda dan 
                    sesuai dengan kontrak asuransi Anda.</p>
                </div>

                <div class="p-4 border border-slate-200 rounded-lg">
                    <h3 class="font-semibold text-slate-900">[VENDOR] Service Providers</h3>
                    <p class="text-slate-700 text-sm">Cloud hosting, email service, payment gateway. Semua di-vet untuk 
                    keamanan dan compliance. Lihat "Data Handler Transparency" untuk daftar lengkap.</p>
                </div>

                <div class="p-4 border border-slate-200 rounded-lg">
                    <h3 class="font-semibold text-slate-900">[LEGAL] Hukum & Regulasi</h3>
                    <p class="text-slate-700 text-sm">Jika diminta oleh hukum, subpoena, atau otoritas kesehatan. 
                    Kami akan memberitahu Anda kecuali dilarang oleh hukum.</p>
                </div>
            </div>

            <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-amber-900 text-sm">
                    <strong>[IMPORTANT]</strong> Semua pihak ketiga yang menerima data Anda harus mematuhi 
                    standar keamanan yang sama dan menandatangani Data Processing Agreement (DPA).
                </p>
            </div>
        </section>

        <!-- 8. Telemedicine -->
        <section id="telemedicine" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">8. [TELEMEDICINE-INFO] Informasi Khusus Telemedicine</h2>
            
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 border-l-4 border-blue-600">
                    <h3 class="font-semibold text-blue-900 mb-2">Video Call Privacy</h3>
                    <p class="text-blue-800 text-sm">Video call dilakukan melalui Pusher (encrypted). 
                    Data video tidak disimpan; hanya transkrip/catatan yang disimpan sebagai bagian rekam medis.</p>
                </div>

                <div class="p-4 bg-blue-50 border-l-4 border-blue-600">
                    <h3 class="font-semibold text-blue-900 mb-2">Chat History</h3>
                    <p class="text-blue-800 text-sm">Chat messages disimpan untuk dokumentasi medis dan continuity of care. 
                    Anda dapat melihat riwayat lengkap, dan dapat di-download kapan saja.</p>
                </div>

                <div class="p-4 bg-blue-50 border-l-4 border-blue-600">
                    <h3 class="font-semibold text-blue-900 mb-2">Informed Consent Telemedicine</h3>
                    <p class="text-blue-800 text-sm">Sebelum konsultasi pertama, Anda harus memberikan informed consent 
                    yang menyetujui risiko dan keuntungan telemedicine vs konsultasi tatap muka.</p>
                </div>

                <div class="p-4 bg-blue-50 border-l-4 border-blue-600">
                    <h3 class="font-semibold text-blue-900 mb-2">Doctor-Patient Relationship</h3>
                    <p class="text-blue-800 text-sm">Telemedicine memerlukan doctor-patient relationship yang valid. 
                    Ini tercatat dan diverifikasi dalam sistem kami (sesuai Ryan Haight Act & India Guidelines).</p>
                </div>

                <div class="p-4 bg-blue-50 border-l-4 border-blue-600">
                    <h3 class="font-semibold text-blue-900 mb-2">Emergency Situations</h3>
                    <p class="text-blue-800 text-sm">Jika dalam keadaan darurat medis, hubungi layanan gawat darurat 
                    lokal (ambulans/IGD) bukan telemedicine.</p>
                </div>
            </div>
        </section>

        <!-- 9. Perubahan Kebijakan -->
        <section id="perubahan-kebijakan" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">9. 📝 Perubahan Kebijakan</h2>
            
            <p class="text-slate-700 mb-4">
                Kami dapat mengubah Kebijakan Privasi ini dari waktu ke waktu. Jika ada perubahan signifikan, kami akan:
            </p>

            <ul class="space-y-2 text-slate-700">
                <li>✓ Memberitahu Anda via email</li>
                <li>✓ Menampilkan banner di aplikasi</li>
                <li>✓ Meminta informed consent baru untuk perubahan material</li>
                <li>✓ Memberikan 30 hari notice sebelum perubahan berlaku</li>
            </ul>

            <p class="mt-4 text-slate-700 text-sm">
                <strong>Versi saat ini:</strong> v1.0  
                <br/><strong>Terakhir diubah:</strong> Desember 20, 2025
            </p>
        </section>

        <!-- 10. Hubungi Kami -->
        <section id="kontak" class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">10. 📞 Hubungi Kami</h2>
            
            <p class="text-slate-700 mb-4">
                Jika Anda memiliki pertanyaan, keluhan, atau ingin menggunakan hak Anda (akses, koreksi, hapus, portabilitas), 
                silakan hubungi:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">[EMAIL] Email Support</h3>
                    <p class="text-indigo-800">privacy@telemedicine.com</p>
                    <p class="text-indigo-700 text-sm mt-1">Response time: 24-48 jam</p>
                </div>

                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">[WHATSAPP] WhatsApp Support</h3>
                    <p class="text-indigo-800">+62 812-3456-7890</p>
                    <p class="text-indigo-700 text-sm mt-1">Jam kerja: Senin-Jumat 08:00-17:00 WIB</p>
                </div>

                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">[OFFICE] Alamat Kantor</h3>
                    <p class="text-indigo-800">Telemedicine Indonesia</p>
                    <p class="text-indigo-700 text-sm">Jakarta, Indonesia</p>
                </div>

                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h3 class="font-semibold text-indigo-900 mb-2">[DPO] Data Protection Officer</h3>
                    <p class="text-indigo-800">dpo@telemedicine.com</p>
                    <p class="text-indigo-700 text-sm">Untuk compliance & regulatory issues</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <div class="text-center mt-8 p-4 bg-slate-100 rounded-lg text-slate-700">
            <p class="text-sm">
                Kebijakan Privasi ini berlaku efektif sejak Desember 20, 2025.<br/>
                Terima kasih telah mempercayai kami untuk kesehatan Anda. [TRUST]
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 py-8 mt-8">
        <div class="max-w-4xl mx-auto px-4 text-center text-sm">
            <p>© 2025 Telemedicine. Semua hak dilindungi.</p>
            <p class="mt-2">
                <a href="/" class="text-indigo-400 hover:text-indigo-300">Home</a> | 
                <a href="/privacy-policy" class="text-indigo-400 hover:text-indigo-300">Privacy Policy</a> | 
                <a href="/terms" class="text-indigo-400 hover:text-indigo-300">Terms of Service</a>
            </p>
        </div>
    </footer>
</body>
</html>
