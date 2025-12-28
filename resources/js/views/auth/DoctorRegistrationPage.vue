<template>
  <div class="min-h-screen bg-linear-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-md mx-auto">
      <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900">[DOCTOR] Daftar Dokter</h1>
          <p class="text-gray-600 mt-2">Registrasi akun dokter Anda</p>
          <p class="text-sm text-gray-500 mt-4">Admin akan memverifikasi dokumen Anda sebelum akun diaktifkan</p>
        </div>

        <!-- Success Message -->
        <div v-if="registrationSuccess" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-center">
          <p class="text-green-800 font-medium">✓ Akun berhasil dibuat!</p>
          <p class="text-green-700 text-sm mt-2">Admin akan memverifikasi dokumen Anda dalam 2-5 hari kerja.</p>
          <router-link to="/auth/login" class="inline-block mt-4 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
            Kembali ke Login
          </router-link>
        </div>

        <!-- Registration Form -->
        <form v-else @submit.prevent="submitRegistration" class="space-y-4">
          <!-- Full Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Dr. John Doe"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="formData.email"
              type="email"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="dokter@example.com"
            />
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
            <input
              v-model="formData.phone"
              type="tel"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="+62812345678"
            />
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
              v-model="formData.password"
              type="password"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Minimal 12 karakter"
            />
            <p class="text-xs text-gray-500 mt-1">Minimal 12 karakter, kombinasi huruf besar, kecil, angka, dan simbol.</p>
          </div>

          <!-- Password Confirmation -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
            <input
              v-model="formData.password_confirmation"
              type="password"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ulangi password"
            />
          </div>

          <!-- Error Messages -->
          <div v-if="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm font-medium text-red-800 mb-2">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside text-sm text-red-700">
              <li v-for="error in errors" :key="error">{{ error }}</li>
            </ul>
          </div>

          <!-- Info Box -->
          <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
              <strong>ℹ️ Catatan:</strong> Dokumen verifikasi (SIP, STR, KTP, Ijazah) akan diminta oleh admin setelah Anda mendaftar.
            </p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition"
          >
            {{ loading ? "Mendaftar..." : "Daftar Sekarang" }}
          </button>

          <!-- Link to Login -->
          <p class="text-center text-sm text-gray-600">
            Sudah punya akun?
            <router-link to="/auth/login" class="text-blue-600 hover:text-blue-700 font-medium"> Login di sini</router-link>
          </p>
        </form>
      </div>

      <!-- Footer Info -->
      <div class="mt-8 text-center text-sm text-gray-600">
        <p>Dengan mendaftar, Anda setuju dengan syarat dan ketentuan kami.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const loading = ref(false);
const errors = ref([]);
const registrationSuccess = ref(false);

const formData = ref({
  name: "",
  email: "",
  phone: "",
  password: "",
  password_confirmation: "",
});

const submitRegistration = async () => {
  loading.value = true;
  errors.value = [];

  try {
    const response = await fetch("/api/v1/dokter/register", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData.value),
    });

    const data = await response.json();

    if (data.success) {
      registrationSuccess.value = true;
      // Redirect to login after 3 seconds
      setTimeout(() => {
        router.push("/auth/login");
      }, 3000);
    } else {
      if (typeof data.message === "string") {
        errors.value = [data.message];
      } else if (data.errors) {
        errors.value = Object.values(data.errors).flat();
      } else {
        errors.value = ["Terjadi kesalahan. Silakan coba lagi."];
      }
    }
  } catch (error) {
    errors.value = [error.message || "Terjadi kesalahan jaringan"];
  } finally {
    loading.value = false;
  }
};
</script>
