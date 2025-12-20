<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-2xl mx-auto">
      <!-- Progress Indicator -->
      <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
          <div
            v-for="(step, idx) in registrationSteps"
            :key="idx"
            :class="[
              'flex-1 text-center pb-2',
              idx < currentStep ? 'text-green-600' : idx === currentStep ? 'text-blue-600' : 'text-gray-400',
            ]"
          >
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-2 font-bold',
                idx < currentStep ? 'bg-green-600 text-white' : idx === currentStep ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600',
              ]"
            >
              {{ idx + 1 }}
            </div>
            <p class="text-sm font-medium">{{ step }}</p>
          </div>
        </div>
        <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
          <div :style="{ width: `${(currentStep / 3) * 100}%` }" class="h-full bg-blue-600 transition-all duration-300"></div>
        </div>
      </div>

      <!-- Registration Card -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Stage 1: Basic Information -->
        <div v-if="currentStep === 0">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">📝 Pendaftaran Dokter - Tahap 1</h2>
          <p class="text-gray-600 mb-6">Silakan isi informasi dasar Anda untuk memulai proses pendaftaran.</p>

          <form @submit.prevent="submitStage1" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
              <input v-model="formData.name" type="text" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Dr. John Doe" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="formData.email" type="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="dokter@example.com" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
              <input v-model="formData.phone" type="tel" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="+62812345678" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Spesialisasi</label>
              <select v-model="formData.specialization" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Pilih Spesialisasi</option>
                <option value="Umum">Dokter Umum</option>
                <option value="Anak">Dokter Anak</option>
                <option value="Kandungan">Dokter Kandungan</option>
                <option value="Jantung">Dokter Jantung</option>
                <option value="Bedah">Dokter Bedah</option>
                <option value="Saraf">Dokter Saraf</option>
                <option value="Mata">Dokter Mata</option>
                <option value="Gigi">Dokter Gigi</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input v-model="formData.password" type="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Minimal 12 karakter" />
              <p class="text-xs text-gray-500 mt-1">Minimal 12 karakter, kombinasi huruf besar, kecil, angka, dan simbol.</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
              <input v-model="formData.password_confirmation" type="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ulangi password" />
            </div>

            <div v-if="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm font-medium text-red-800 mb-2">Terjadi kesalahan:</p>
              <ul class="list-disc list-inside text-sm text-red-700">
                <li v-for="error in errors" :key="error">{{ error }}</li>
              </ul>
            </div>

            <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition">
              {{ loading ? "Memproses..." : "Lanjut ke Tahap 2" }}
            </button>
          </form>
        </div>

        <!-- Stage 2: Upload Documents -->
        <div v-if="currentStep === 1">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">📄 Tahap 2: Upload Dokumen Verifikasi</h2>
          <p class="text-gray-600 mb-6">Silakan upload dokumen-dokumen berikut untuk verifikasi: SIP, STR, KTP, dan Ijazah.</p>

          <form @submit.prevent="submitStage2" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">SIP (Surat Izin Praktek) *</label>
              <input type="file" @change="(e) => (formData.sip = e.target.files[0])" accept="image/*,application/pdf" class="w-full" />
              <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, atau PNG (Maks 5MB)</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nomor SIP</label>
              <input v-model="formData.sip_number" type="text" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">STR (Surat Tanda Registrasi) *</label>
              <input type="file" @change="(e) => (formData.str = e.target.files[0])" accept="image/*,application/pdf" class="w-full" />
              <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, atau PNG (Maks 5MB)</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nomor STR</label>
              <input v-model="formData.str_number" type="text" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">KTP (Kartu Tanda Penduduk)</label>
              <input type="file" @change="(e) => (formData.ktp = e.target.files[0])" accept="image/*,application/pdf" class="w-full" />
              <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, atau PNG (Maks 5MB)</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ijazah (Diploma)</label>
              <input type="file" @change="(e) => (formData.ijazah = e.target.files[0])" accept="image/*,application/pdf" class="w-full" />
              <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, atau PNG (Maks 5MB)</p>
            </div>

            <div v-if="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm font-medium text-red-800 mb-2">Terjadi kesalahan:</p>
              <ul class="list-disc list-inside text-sm text-red-700">
                <li v-for="error in errors" :key="error">{{ error }}</li>
              </ul>
            </div>

            <div class="flex gap-4">
              <button type="button" @click="currentStep--" class="flex-1 bg-gray-400 text-white font-bold py-2 rounded-lg hover:bg-gray-500 transition">
                Kembali
              </button>
              <button type="submit" :disabled="loading" class="flex-1 bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition">
                {{ loading ? "Mengupload..." : "Lanjut ke Tahap 3" }}
              </button>
            </div>
          </form>
        </div>

        <!-- Stage 3: Profile Completion -->
        <div v-if="currentStep === 2">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">👤 Tahap 3: Lengkapi Profil</h2>
          <p class="text-gray-600 mb-6">Silakan lengkapi informasi profil Anda.</p>

          <form @submit.prevent="submitStage3" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Spesialisasi Detail</label>
              <input v-model="formData.specialization_detail" type="text" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Misal: Spesialis Mata Anak" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Fasilitas Kesehatan</label>
              <input v-model="formData.facility_name" type="text" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="RSUD / Klinik / Rumah Sakit" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                <input v-model="formData.is_available" type="checkbox" class="mr-2" />
                Saya siap menerima konsultasi
              </label>
            </div>

            <div v-if="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm font-medium text-red-800 mb-2">Terjadi kesalahan:</p>
              <ul class="list-disc list-inside text-sm text-red-700">
                <li v-for="error in errors" :key="error">{{ error }}</li>
              </ul>
            </div>

            <div class="flex gap-4">
              <button type="button" @click="currentStep--" class="flex-1 bg-gray-400 text-white font-bold py-2 rounded-lg hover:bg-gray-500 transition">
                Kembali
              </button>
              <button type="submit" :disabled="loading" class="flex-1 bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition">
                {{ loading ? "Memproses..." : "Lanjut ke Tahap 4" }}
              </button>
            </div>
          </form>
        </div>

        <!-- Stage 4: Compliance -->
        <div v-if="currentStep === 3">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">✅ Tahap 4: Penerimaan Persyaratan</h2>
          <p class="text-gray-600 mb-6">Silakan baca dan terima semua persyaratan untuk menyelesaikan pendaftaran.</p>

          <form @submit.prevent="submitStage4" class="space-y-4">
            <div class="bg-blue-50 p-4 rounded-lg mb-6 h-32 overflow-y-auto border border-blue-200">
              <h4 class="font-bold text-blue-900 mb-2">Syarat dan Ketentuan</h4>
              <p class="text-sm text-blue-800">
                Dengan mendaftar sebagai dokter di platform telemedicine ini, Anda setuju untuk mengikuti semua peraturan yang berlaku sesuai dengan UU Nomor 29 Tahun 2004 tentang Praktik Kedokteran dan peraturan pelaksanaannya. Anda berkomitmen untuk memberikan layanan kesehatan yang profesional dan etis.
              </p>
            </div>

            <label class="flex items-center p-3 border-2 border-blue-200 rounded-lg hover:bg-blue-50">
              <input v-model="formData.accepted_terms" type="checkbox" class="mr-3" />
              <span class="text-sm text-gray-700">Saya setuju dengan Syarat dan Ketentuan</span>
            </label>

            <div class="bg-purple-50 p-4 rounded-lg mb-6 h-32 overflow-y-auto border border-purple-200">
              <h4 class="font-bold text-purple-900 mb-2">Kebijakan Privasi</h4>
              <p class="text-sm text-purple-800">
                Data pribadi Anda akan dienkripsi dan disimpan dengan aman sesuai dengan standar keamanan data medis internasional. Kami tidak akan membagikan data Anda kepada pihak ketiga tanpa persetujuan Anda.
              </p>
            </div>

            <label class="flex items-center p-3 border-2 border-purple-200 rounded-lg hover:bg-purple-50">
              <input v-model="formData.accepted_privacy" type="checkbox" class="mr-3" />
              <span class="text-sm text-gray-700">Saya setuju dengan Kebijakan Privasi</span>
            </label>

            <div class="bg-green-50 p-4 rounded-lg mb-6 h-32 overflow-y-auto border border-green-200">
              <h4 class="font-bold text-green-900 mb-2">Informed Consent</h4>
              <p class="text-sm text-green-800">
                Anda memahami bahwa layanan telemedicine memiliki keterbatasan teknis dan medis. Anda bertanggung jawab atas keputusan medis yang diambil berdasarkan konsultasi virtual. Emergency 24/7 harus ditangani di fasilitas kesehatan terdekat.
              </p>
            </div>

            <label class="flex items-center p-3 border-2 border-green-200 rounded-lg hover:bg-green-50">
              <input v-model="formData.accepted_informed_consent" type="checkbox" class="mr-3" />
              <span class="text-sm text-gray-700">Saya memahami dan setuju dengan Informed Consent</span>
            </label>

            <div v-if="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm font-medium text-red-800 mb-2">Terjadi kesalahan:</p>
              <ul class="list-disc list-inside text-sm text-red-700">
                <li v-for="error in errors" :key="error">{{ error }}</li>
              </ul>
            </div>

            <div class="flex gap-4">
              <button type="button" @click="currentStep--" class="flex-1 bg-gray-400 text-white font-bold py-2 rounded-lg hover:bg-gray-500 transition">
                Kembali
              </button>
              <button
                type="submit"
                :disabled="loading || !formData.accepted_terms || !formData.accepted_privacy || !formData.accepted_informed_consent"
                class="flex-1 bg-green-600 text-white font-bold py-2 rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition"
              >
                {{ loading ? "Menyelesaikan..." : "Selesaikan Pendaftaran" }}
              </button>
            </div>
          </form>
        </div>

        <!-- Success Message -->
        <div v-if="currentStep === 4" class="text-center">
          <div class="mb-6">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold text-green-600 mb-2">Pendaftaran Berhasil!</h2>
            <p class="text-gray-600">Terima kasih telah mendaftar. Akun Anda sedang menunggu verifikasi dari admin kami.</p>
          </div>

          <div class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-200">
            <h4 class="font-bold text-blue-900 mb-2">Apa Selanjutnya?</h4>
            <ol class="text-sm text-blue-800 text-left space-y-2">
              <li>✓ Admin kami akan mereview dokumen Anda dalam 2-5 hari kerja</li>
              <li>✓ Anda akan menerima email notifikasi untuk setiap tahap verifikasi</li>
              <li>✓ Setelah disetujui, Anda dapat mulai menerima konsultasi dari pasien</li>
            </ol>
          </div>

          <router-link to="/dokter/dashboard" class="inline-block bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition">
            Kembali ke Dashboard
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { authApi } from "@/api/auth";

const currentStep = ref(0);
const loading = ref(false);
const errors = ref([]);

const registrationSteps = ["Informasi Dasar", "Upload Dokumen", "Profil", "Persyaratan"];

const formData = ref({
  // Stage 1
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  phone: "",
  specialization: "",

  // Stage 2
  sip: null,
  sip_number: "",
  str: null,
  str_number: "",
  ktp: null,
  ijazah: null,

  // Stage 3
  specialization_detail: "",
  facility_name: "",
  is_available: false,

  // Stage 4
  accepted_terms: false,
  accepted_privacy: false,
  accepted_informed_consent: false,
});

const submitStage1 = async () => {
  loading.value = true;
  errors.value = [];

  try {
    const response = await fetch("/api/v1/dokter/register", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: formData.value.name,
        email: formData.value.email,
        password: formData.value.password,
        password_confirmation: formData.value.password_confirmation,
        phone: formData.value.phone,
        specialization: formData.value.specialization,
      }),
    });

    const data = await response.json();

    if (data.success) {
      currentStep.value++;
      // Store user ID for next stages
      sessionStorage.setItem("doctor_user_id", data.data.user_id);
    } else {
      errors.value = [data.message || "Terjadi kesalahan"];
    }
  } catch (error) {
    errors.value = [error.message || "Terjadi kesalahan jaringan"];
  } finally {
    loading.value = false;
  }
};

const submitStage2 = async () => {
  loading.value = true;
  errors.value = [];

  try {
    const formDataObj = new FormData();

    if (formData.value.sip) formDataObj.append("sip", formData.value.sip);
    if (formData.value.sip_number) formDataObj.append("sip_number", formData.value.sip_number);
    if (formData.value.str) formDataObj.append("str", formData.value.str);
    if (formData.value.str_number) formDataObj.append("str_number", formData.value.str_number);
    if (formData.value.ktp) formDataObj.append("ktp", formData.value.ktp);
    if (formData.value.ijazah) formDataObj.append("ijazah", formData.value.ijazah);

    const token = localStorage.getItem("token");
    const response = await fetch("/api/v1/dokter/verification/documents", {
      method: "POST",
      headers: { Authorization: `Bearer ${token}` },
      body: formDataObj,
    });

    const data = await response.json();

    if (data.success) {
      currentStep.value++;
    } else {
      errors.value = [data.message || "Terjadi kesalahan"];
    }
  } catch (error) {
    errors.value = [error.message || "Terjadi kesalahan jaringan"];
  } finally {
    loading.value = false;
  }
};

const submitStage3 = async () => {
  loading.value = true;
  errors.value = [];

  try {
    const token = localStorage.getItem("token");
    const response = await fetch("/api/v1/dokter/profile/complete", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        specialization: formData.value.specialization,
        phone: formData.value.phone,
        facility_name: formData.value.facility_name,
        is_available: formData.value.is_available,
      }),
    });

    const data = await response.json();

    if (data.success) {
      currentStep.value++;
    } else {
      errors.value = [data.message || "Terjadi kesalahan"];
    }
  } catch (error) {
    errors.value = [error.message || "Terjadi kesalahan jaringan"];
  } finally {
    loading.value = false;
  }
};

const submitStage4 = async () => {
  loading.value = true;
  errors.value = [];

  try {
    const token = localStorage.getItem("token");
    const response = await fetch("/api/v1/dokter/compliance/accept", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        accepted_terms: formData.value.accepted_terms,
        accepted_privacy: formData.value.accepted_privacy,
        accepted_informed_consent: formData.value.accepted_informed_consent,
      }),
    });

    const data = await response.json();

    if (data.success) {
      currentStep.value++;
    } else {
      errors.value = [data.message || "Terjadi kesalahan"];
    }
  } catch (error) {
    errors.value = [error.message || "Terjadi kesalahan jaringan"];
  } finally {
    loading.value = false;
  }
};
</script>
