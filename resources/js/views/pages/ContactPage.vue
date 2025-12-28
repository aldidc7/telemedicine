<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white py-16 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl font-black mb-4">Hubungi Kami</h1>
        <p class="text-xl text-indigo-100">Kami siap membantu Anda. Hubungi kami melalui berbagai saluran komunikasi</p>
      </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 py-16">
      <div class="grid md:grid-cols-3 gap-8 mb-16">
        <!-- Contact Info 1 -->
        <div class="bg-linear-to-br from-indigo-50 to-purple-50 p-8 rounded-xl text-center hover:shadow-lg transition">
          <div class="text-5xl mb-4">[EMAIL]</div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Email</h3>
          <p class="text-gray-700 font-semibold mb-2">angguny03@gmail.com</p>
          <p class="text-sm text-gray-600">Balas email dalam 24 jam</p>
        </div>

        <!-- Contact Info 2 -->
        <div class="bg-linear-to-br from-pink-50 to-red-50 p-8 rounded-xl text-center hover:shadow-lg transition">
          <div class="text-5xl mb-4">[CHAT]</div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">WhatsApp</h3>
          <p class="text-gray-700 font-semibold mb-2">08885773781</p>
          <p class="text-sm text-gray-600">Hubungi kami kapan saja</p>
        </div>

        <!-- Contact Info 3 -->
        <div class="bg-linear-to-br from-green-50 to-emerald-50 p-8 rounded-xl text-center hover:shadow-lg transition">
          <div class="text-5xl mb-4">📍</div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Alamat</h3>
          <p class="text-gray-700 font-semibold mb-2">Pasuruan, Jawa Timur</p>
          <p class="text-sm text-gray-600">JL KH Al Masyhuri Abdulloh</p>
        </div>
      </div>

      <!-- Contact Form & Info -->
      <div class="grid md:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div>
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Kirim Pesan Kami</h2>
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Name -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Nama Anda"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                required
              />
            </div>

            <!-- Email -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Email</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="Email Anda"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                required
              />
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Nomor Telepon (Opsional)</label>
              <input
                v-model="form.phone"
                type="tel"
                placeholder="Nomor WhatsApp atau HP Anda"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              />
            </div>

            <!-- Subject -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Subjek</label>
              <select
                v-model="form.subject"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                required
              >
                <option value="">Pilih Subjek</option>
                <option value="general">Pertanyaan Umum</option>
                <option value="technical">Masalah Teknis</option>
                <option value="medical">Konsultasi Medis</option>
                <option value="feedback">Saran & Masukan</option>
                <option value="other">Lainnya</option>
              </select>
            </div>

            <!-- Message -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Pesan</label>
              <textarea
                v-model="form.message"
                placeholder="Tuliskan pesan Anda di sini..."
                rows="6"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                required
              ></textarea>
            </div>

            <!-- Checkbox -->
            <div class="flex items-start gap-3">
              <input
                v-model="form.agreeTerms"
                type="checkbox"
                id="agree"
                class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                required
              />
              <label for="agree" class="text-sm text-gray-600">
                Saya setuju dengan kebijakan privasi dan syarat penggunaan kami
              </label>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
            >
              {{ isSubmitting ? 'Mengirim...' : 'Kirim Pesan' }}
            </button>

            <!-- Success Message -->
            <div v-if="submitSuccess" class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
              ✓ Pesan Anda telah berhasil dikirim. Terima kasih! Kami akan menghubungi Anda segera.
            </div>

            <!-- Error Message -->
            <div v-if="submitError" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
              ✕ Terjadi kesalahan. Silakan coba lagi atau hubungi kami langsung.
            </div>
          </form>
        </div>

        <!-- Contact Info & FAQ -->
        <div>
          <!-- Quick Info -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Informasi Kontak</h2>
            <div class="space-y-4">
              <div class="flex gap-4">
                <div class="text-2xl">[EMAIL]</div>
                <div>
                  <h3 class="font-bold text-gray-900">Email</h3>
                  <p class="text-gray-600">angguny03@gmail.com</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="text-2xl">[CHAT]</div>
                <div>
                  <h3 class="font-bold text-gray-900">WhatsApp</h3>
                  <p class="text-gray-600">08885773781</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="text-2xl">📍</div>
                <div>
                  <h3 class="font-bold text-gray-900">Alamat</h3>
                  <p class="text-gray-600">JL KH Al Masyhuri Abdulloh<br/>Sambirejo, Pasuruan<br/>Jawa Timur</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="text-2xl">⏰</div>
                <div>
                  <h3 class="font-bold text-gray-900">Jam Kerja</h3>
                  <p class="text-gray-600">Senin - Jumat: 09:00 - 18:00<br/>Sabtu: 09:00 - 14:00<br/>Minggu: Tutup</p>
                </div>
              </div>
            </div>
          </div>

          <!-- FAQ -->
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Pertanyaan Umum</h2>
            <div class="space-y-4">
              <!-- FAQ Item 1 -->
              <div
                class="border border-gray-200 rounded-lg overflow-hidden"
                @click="toggleFaq(0)"
              >
                <button class="w-full p-4 text-left font-semibold text-gray-900 hover:bg-gray-50 flex justify-between items-center">
                  <span>Berapa lama respons kami?</span>
                  <span class="text-indigo-600">{{ expandedFaq === 0 ? '−' : '+' }}</span>
                </button>
                <div v-if="expandedFaq === 0" class="p-4 bg-gray-50 border-t border-gray-200">
                  <p class="text-gray-600">
                    Kami berusaha merespons setiap pertanyaan dalam waktu 24 jam kerja. 
                    Untuk pertanyaan mendesak, hubungi kami melalui WhatsApp.
                  </p>
                </div>
              </div>

              <!-- FAQ Item 2 -->
              <div
                class="border border-gray-200 rounded-lg overflow-hidden"
                @click="toggleFaq(1)"
              >
                <button class="w-full p-4 text-left font-semibold text-gray-900 hover:bg-gray-50 flex justify-between items-center">
                  <span>Apakah ada biaya konsultasi?</span>
                  <span class="text-indigo-600">{{ expandedFaq === 1 ? '−' : '+' }}</span>
                </button>
                <div v-if="expandedFaq === 1" class="p-4 bg-gray-50 border-t border-gray-200">
                  <p class="text-gray-600">
                    Biaya konsultasi tergantung jenis layanan yang Anda minta. 
                    Hubungi kami untuk informasi detail dan penawaran terbaik.
                  </p>
                </div>
              </div>

              <!-- FAQ Item 3 -->
              <div
                class="border border-gray-200 rounded-lg overflow-hidden"
                @click="toggleFaq(2)"
              >
                <button class="w-full p-4 text-left font-semibold text-gray-900 hover:bg-gray-50 flex justify-between items-center">
                  <span>Apakah data saya aman?</span>
                  <span class="text-indigo-600">{{ expandedFaq === 2 ? '−' : '+' }}</span>
                </button>
                <div v-if="expandedFaq === 2" class="p-4 bg-gray-50 border-t border-gray-200">
                  <p class="text-gray-600">
                    Ya, kami menjaga privasi dan keamanan data Anda dengan enkripsi tingkat enterprise. 
                    Lihat Kebijakan Privasi kami untuk detail lebih lanjut.
                  </p>
                </div>
              </div>

              <!-- FAQ Item 4 -->
              <div
                class="border border-gray-200 rounded-lg overflow-hidden"
                @click="toggleFaq(3)"
              >
                <button class="w-full p-4 text-left font-semibold text-gray-900 hover:bg-gray-50 flex justify-between items-center">
                  <span>Bagaimana cara mendaftar?</span>
                  <span class="text-indigo-600">{{ expandedFaq === 3 ? '−' : '+' }}</span>
                </button>
                <div v-if="expandedFaq === 3" class="p-4 bg-gray-50 border-t border-gray-200">
                  <p class="text-gray-600">
                    Pendaftaran mudah dan gratis. Kunjungi halaman pendaftaran kami, isi formulir dengan data Anda, 
                    dan verifikasi email Anda. Setelah itu, Anda siap menggunakan semua layanan kami.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Map Section (Optional) -->
    <div class="bg-slate-100 py-12 px-4 mt-12">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Lokasi Kami</h2>
        <div class="bg-gray-300 rounded-lg overflow-hidden h-96 flex items-center justify-center">
          <div class="text-center">
            <div class="text-6xl mb-4">🗺️</div>
            <p class="text-gray-700 font-semibold">Google Maps Integration</p>
            <p class="text-sm text-gray-600 mt-2">Pasuruan, Jawa Timur</p>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="max-w-4xl mx-auto px-4 py-16 text-center">
      <h2 class="text-3xl font-bold text-gray-900 mb-4">Butuh Bantuan Lebih Lanjut?</h2>
      <p class="text-lg text-gray-600 mb-8">
        Tim kami siap membantu Anda dengan respons cepat dan profesional
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a
          href="mailto:angguny03@gmail.com"
          class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition"
        >
          Kirim Email
        </a>
        <a
          href="https://wa.me/628885773781"
          target="_blank"
          rel="noopener noreferrer"
          class="px-8 py-3 border-2 border-indigo-600 text-indigo-600 font-bold rounded-lg hover:bg-indigo-50 transition"
        >
          Chat WhatsApp
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
  agreeTerms: false
})

const isSubmitting = ref(false)
const submitSuccess = ref(false)
const submitError = ref(false)
const expandedFaq = ref(null)

const handleSubmit = async () => {
  try {
    isSubmitting.value = true
    submitError.value = false
    submitSuccess.value = false

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))

    // Reset form
    form.value = {
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: '',
      agreeTerms: false
    }

    submitSuccess.value = true

    // Hide success message after 5 seconds
    setTimeout(() => {
      submitSuccess.value = false
    }, 5000)
  } catch (error) {
    submitError.value = true
    console.error('Error submitting form:', error)
  } finally {
    isSubmitting.value = false
  }
}

const toggleFaq = (index) => {
  expandedFaq.value = expandedFaq.value === index ? null : index
}
</script>

<style scoped>
</style>
