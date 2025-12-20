<template>
    <!-- INFORMED CONSENT MODAL 
       Komponen untuk menampilkan dan mengelola informed consent pengguna
       Mengikuti regulasi: Ryan Haight Act, India Telemedicine 2020, WHO Standards
  -->
    <div
        v-if="isOpen"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
    >
        <div
            class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
        >
            <!-- Header -->
            <div
                class="sticky top-0 bg-linear-to-r from-indigo-600 to-indigo-700 px-6 py-4 text-white"
            >
                <h2 class="text-2xl font-bold">
                    📋 Persetujuan Informed Consent
                </h2>
                <p class="text-indigo-100 text-sm mt-1">
                    Diperlukan sebelum menggunakan layanan telemedicine
                </p>
            </div>

            <!-- Content -->
            <div class="px-6 py-6">
                <!-- Progress Indicator -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">
                            Progress: {{ acceptedCount }}/{{ totalConsents }}
                        </span>
                        <span class="text-sm text-slate-500"
                            >{{ progressPercentage }}%</span
                        >
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div
                            class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                            :style="{ width: progressPercentage + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Alert untuk semua consent diterima -->
                <div
                    v-if="allAccepted"
                    class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3"
                >
                    <svg
                        class="w-6 h-6 text-green-600 shrink-0 mt-0.5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-green-900">
                            ✅ Semua Consent Diterima!
                        </h3>
                        <p class="text-green-700 text-sm">
                            Anda sekarang dapat menggunakan layanan telemedicine
                        </p>
                    </div>
                </div>

                <!-- Consent Sections -->
                <div class="space-y-4">
                    <div
                        v-for="(consent, index) in requiredConsents"
                        :key="consent.type"
                        class="border border-slate-200 rounded-lg p-4 hover:border-indigo-300 transition-colors"
                        :class="{ 'bg-green-50': consent.accepted }"
                    >
                        <!-- Header Consent -->
                        <div class="flex items-start gap-3 mb-3">
                            <div class="shrink-0">
                                <div
                                    v-if="consent.accepted"
                                    class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-green-600"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div
                                    v-else
                                    class="w-6 h-6 bg-slate-200 rounded-full flex items-center justify-center"
                                >
                                    <span
                                        class="text-xs font-semibold text-slate-600"
                                        >{{ index + 1 }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex-1">
                                <h3
                                    class="font-semibold text-slate-900 text-lg"
                                >
                                    {{ consent.name }}
                                </h3>
                                <p class="text-slate-600 text-sm mt-1">
                                    {{ consent.description }}
                                </p>
                            </div>

                            <!-- Badge Status -->
                            <div class="shrink-0">
                                <span
                                    v-if="consent.accepted"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"
                                >
                                    ✓ Diterima
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800"
                                >
                                    ⏳ Pending
                                </span>
                            </div>
                        </div>

                        <!-- Collapse Content -->
                        <button
                            @click="
                                expandedConsent =
                                    expandedConsent === consent.type
                                        ? null
                                        : consent.type
                            "
                            class="w-full text-left px-4 py-3 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors flex items-center justify-between"
                        >
                            <span class="text-sm font-medium text-slate-700">
                                {{
                                    expandedConsent === consent.type
                                        ? "Tutup"
                                        : "Baca Detail"
                                }}
                            </span>
                            <svg
                                class="w-4 h-4 text-slate-600 transition-transform"
                                :class="{
                                    'rotate-180':
                                        expandedConsent === consent.type,
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                />
                            </svg>
                        </button>

                        <!-- Detail Content -->
                        <div
                            v-if="expandedConsent === consent.type"
                            class="mt-3 pt-3 border-t border-slate-200 text-sm text-slate-700 leading-relaxed whitespace-pre-wrap"
                        >
                            {{ getConsentFullText(consent.type) }}
                        </div>

                        <!-- Checkbox untuk accept (jika belum accepted) -->
                        <div
                            v-if="!consent.accepted"
                            class="mt-4 flex items-start gap-3"
                        >
                            <input
                                :id="`consent-${consent.type}`"
                                v-model="selectedConsents"
                                type="checkbox"
                                :value="consent.type"
                                class="mt-1 w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer"
                            />
                            <label
                                :for="`consent-${consent.type}`"
                                class="text-sm text-slate-700 cursor-pointer flex-1"
                            >
                                Saya telah membaca dan memahami serta menyetujui
                                {{ consent.name.toLowerCase() }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div
                    class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-600 rounded"
                >
                    <h4 class="font-semibold text-blue-900 mb-2">
                        📌 Catatan Penting
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>
                            ✓ Anda dapat mencabut consent kapan saja dari
                            pengaturan akun
                        </li>
                        <li>
                            ✓ Data medis Anda akan disimpan dengan enkripsi
                            tingkat tinggi
                        </li>
                        <li>
                            ✓ Hanya dokter yang berwenang dapat mengakses data
                            Anda
                        </li>
                        <li>
                            ✓ Semua akses ke data Anda akan dicatat untuk audit
                            keamanan
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer / Actions -->
            <div
                class="sticky bottom-0 bg-slate-50 px-6 py-4 border-t border-slate-200 flex gap-3 justify-end"
            >
                <!-- Tombol Cancel (jika belum semua accepted) -->
                <button
                    v-if="!allAccepted"
                    @click="cancelModal"
                    class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors font-medium"
                    :disabled="isLoading"
                >
                    Batal
                </button>

                <!-- Tombol Accept untuk consent yang dipilih -->
                <button
                    v-if="!allAccepted"
                    @click="submitSelectedConsents"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="selectedConsents.length === 0 || isLoading"
                >
                    <span v-if="isLoading" class="animate-spin">⏳</span>
                    <span>{{
                        selectedConsents.length > 0
                            ? `Setujui (${selectedConsents.length})`
                            : "Pilih Consent"
                    }}</span>
                </button>

                <!-- Tombol Close setelah semua diterima -->
                <button
                    v-if="allAccepted"
                    @click="closeModal"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                >
                    ✓ Selesai
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import axios from "axios";

export default defineComponent({
    name: "InformedConsentModal",
    props: {
        /**
         * Kontrol modal dari parent component
         * Contoh: :isOpen="showConsentModal"
         */
        isOpen: {
            type: Boolean,
            required: true,
        },
        /**
         * Callback saat modal ditutup
         * Contoh: @close="showConsentModal = false"
         */
        onClose: {
            type: Function,
            default: () => {},
        },
        /**
         * Callback saat semua consent diterima
         * Contoh: @consent-complete="handleConsentComplete"
         */
        onConsentComplete: {
            type: Function,
            default: () => {},
        },
    },
    data() {
        return {
            isLoading: false,
            selectedConsents: [],
            expandedConsent: null,
            requiredConsents: [],
            acceptedCount: 0,
            totalConsents: 0,
            allAccepted: false,
            userConsents: {},
        };
    },
    computed: {
        progressPercentage() {
            return this.totalConsents > 0
                ? Math.round((this.acceptedCount / this.totalConsents) * 100)
                : 0;
        },
    },
    watch: {
        isOpen(newVal) {
            if (newVal) {
                this.loadRequiredConsents();
            }
        },
    },
    methods: {
        /**
         * Load required consents dari API
         */
        async loadRequiredConsents() {
            try {
                this.isLoading = true;
                const response = await axios.get("/api/v1/consent/required");

                if (response.data.success) {
                    const data = response.data.data;
                    this.userConsents = data.consents;
                    this.acceptedCount = data.accepted_count;
                    this.totalConsents = data.total_required;
                    this.allAccepted = data.all_consents_accepted;

                    // Siapkan array dari required consents dengan urutan
                    this.requiredConsents = [
                        {
                            type: "telemedicine",
                            name: "Persetujuan Telemedicine",
                            description:
                                "Saya memahami risiko dan keuntungan telemedicine",
                            accepted:
                                this.userConsents["telemedicine"]?.accepted ||
                                false,
                        },
                        {
                            type: "privacy_policy",
                            name: "Kebijakan Privasi",
                            description:
                                "Saya menyetujui kebijakan privasi data saya",
                            accepted:
                                this.userConsents["privacy_policy"]?.accepted ||
                                false,
                        },
                        {
                            type: "data_handling",
                            name: "Penanganan Data Medis",
                            description:
                                "Saya menyetujui penanganan data sesuai regulasi",
                            accepted:
                                this.userConsents["data_handling"]?.accepted ||
                                false,
                        },
                    ];

                    // Clear selected consents (untuk fresh start)
                    this.selectedConsents = [];
                }
            } catch (error) {
                console.error("Error loading required consents:", error);
                this.$emit(
                    "error",
                    error.response?.data?.message || "Gagal memuat data consent"
                );
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Submit selected consents
         */
        async submitSelectedConsents() {
            try {
                this.isLoading = true;

                // Kirim setiap consent yang dipilih
                for (const consentType of this.selectedConsents) {
                    const response = await axios.post(
                        "/api/v1/consent/accept",
                        {
                            consent_type: consentType,
                            accepted: true,
                        }
                    );

                    if (!response.data.success) {
                        throw new Error(`Gagal menerima ${consentType}`);
                    }
                }

                // Reload data untuk update progress
                await this.loadRequiredConsents();

                // Jika semua sudah accepted, trigger callback
                if (this.allAccepted) {
                    this.onConsentComplete();
                    this.$emit("consent-complete");
                }

                // Show success message
                this.$emit(
                    "success",
                    `${this.selectedConsents.length} consent berhasil dicatat`
                );
            } catch (error) {
                console.error("Error submitting consents:", error);
                this.$emit("error", error.message || "Gagal mencatat consent");
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Get full consent text
         */
        getConsentFullText(type) {
            const texts = {
                telemedicine: `Layanan telemedicine memungkinkan Anda berkonsultasi dengan dokter melalui video call, chat, atau store-and-forward.

POIN PENTING:
• Telemedicine mungkin tidak cocok untuk semua kondisi medis
• Anda memiliki hak untuk meminta konsultasi tatap muka sebagai gantinya
• Dalam keadaan darurat, hubungi layanan gawat darurat lokal
• Kualitas layanan tergantung pada koneksi internet Anda
• Data Anda akan dienkripsi selama transmisi

MANFAAT:
✓ Akses mudah dari rumah
✓ Efisien waktu dan biaya transportasi
✓ Rekam medis elektronik terintegrasi
✓ Resep digital dan follow-up otomatis

RISIKO:
⚠ Interpretasi diagnosis mungkin terbatas
⚠ Pemeriksaan fisik tidak dapat dilakukan
⚠ Diperlukan koneksi internet yang stabil
⚠ Privacy tergantung pada keamanan sistem

Anda dapat membatalkan telemedicine kapan saja dan memilih konsultasi tatap muka.`,

                privacy_policy: `Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data Anda.

DATA YANG DIKUMPULKAN:
• Data pribadi: nama, email, nomor telepon, alamat
• Data medis: keluhan, riwayat kesehatan, resep, hasil pemeriksaan
• Data teknis: IP address, device info, activity logs
• Data lokasi: hanya dengan izin eksplisit Anda

PENGGUNAAN DATA:
✓ Memberikan layanan telemedicine yang aman dan efektif
✓ Meningkatkan kualitas layanan dengan anonimisasi data
✓ Kepatuhan dengan regulasi kesehatan dan peraturan pemerintah
✗ TIDAK dijual ke pihak ketiga
✗ TIDAK digunakan untuk marketing tanpa izin

ENKRIPSI & KEAMANAN:
• Data transit: TLS 1.2+ encryption
• Data rest: AES-256 database encryption
• Backup: encrypted dan disimpan aman
• Access control: hanya staff yang berwenang

HAK ANDA:
→ Akses: minta copy data medis Anda kapan saja
→ Koreksi: perbaiki data yang salah atau tidak lengkap
→ Hapus: minta penghapusan data yang tidak perlu (soft-delete)
→ Download: ekspor data Anda dalam format standard
→ Revoke: tarik kembali consent kapan saja

PENYIMPANAN:
Rekam medis: disimpan 7-10 tahun sesuai regulasi
Activity logs: disimpan 1 tahun untuk audit
Backup: disimpan 90 hari untuk disaster recovery`,

                data_handling: `Penanganan data medis Anda dilakukan dengan standar keamanan internasional tertinggi.

KLASIFIKASI DATA:
🔴 CONFIDENTIAL (Sangat Rahasia): Rekam medis lengkap, hasil lab, diagnosa
🟠 PROTECTED (Rahasia): Data pasien dasar, appointment, chat history  
🟢 PUBLIC (Publik): Nama dokter, spesialisasi, jam kerja

AKSES & OTORISASI:
• Anda (pasien): akses ke data Anda sendiri
• Dokter Anda: akses untuk diagnosis dan perawatan
• Admin: akses untuk troubleshooting teknis (dengan log)
• Audit team: akses untuk compliance check (terbatas)

PROTEKSI TEKNIS:
✓ Encryption at rest (AES-256)
✓ Encryption in transit (TLS 1.2+)
✓ Hashing untuk password & sensitive data
✓ Role-based access control (RBAC)
✓ Two-factor authentication tersedia
✓ IP whitelisting untuk admin access

AUDIT & MONITORING:
• Setiap akses dicatat di AuditLog
• Aktivitas pengguna di ActivityLog (immutable)
• Real-time alert untuk unusual access patterns
• Monthly compliance review

PENGIRIMAN KE PIHAK KETIGA:
Data Anda HANYA dibagikan dengan:
→ Rumah sakit/klinik (dengan izin dan kontrak DPA)
→ Farmasi (hanya untuk resep yang relevan)
→ Lab (hanya untuk hasil pemeriksaan)
→ Asuransi kesehatan (jika diperlukan dan izin Anda)

Setiap pihak ketiga harus menandatangani Data Processing Agreement (DPA).

INCIDENT RESPONSE:
Jika terjadi breach:
1. Deteksi: automated monitoring (real-time)
2. Investigasi: dalam 1 jam
3. Notifikasi: ke Anda dalam 24 jam
4. Mitigation: sesegera mungkin
5. Review: postmortem dan perbaikan

HUKUM & REGULASI:
✓ Compliant dengan Indonesia Health Law 36/2009
✓ Compliant dengan HIPAA-equivalent standards
✓ Compliant dengan telemedicine guidelines internasional
✓ Transparent tentang data handlers (lihat Data Handler Transparency)`,
            };

            return texts[type] || "Teks consent tidak ditemukan";
        },

        /**
         * Cancel modal
         */
        cancelModal() {
            if (
                confirm(
                    "Apakah Anda yakin ingin menutup modal? Anda akan diminta lagi nanti."
                )
            ) {
                this.closeModal();
            }
        },

        /**
         * Close modal
         */
        closeModal() {
            this.selectedConsents = [];
            this.expandedConsent = null;
            this.onClose();
            this.$emit("close");
        },
    },
    mounted() {
        // Load consents saat component mount jika modal open
        if (this.isOpen) {
            this.loadRequiredConsents();
        }
    },
});
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Ensure modal is on top */
:deep(.fixed) {
    z-index: 50;
}

/* Custom scrollbar untuk modal content */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
