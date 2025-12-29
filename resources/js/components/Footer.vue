<template>
  <footer class="bg-gray-900 text-gray-300 mt-20">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
        <!-- Company Info -->
        <div class="space-y-4">
          <h3 class="text-white font-bold text-lg mb-4">[HOSPITAL] Telemedicine</h3>
          <p class="text-sm text-gray-400">
            Platform kesehatan digital yang menghubungkan pasien dengan dokter berpengalaman untuk konsultasi online kapan saja, di mana saja.
          </p>
        </div>

        <!-- Untuk Pasien -->
        <div class="space-y-4">
          <h3 class="text-white font-bold text-lg">Untuk Pasien</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <router-link to="/dashboard" class="text-gray-400 hover:text-white transition">
                Dashboard
              </router-link>
            </li>
            <li>
              <a href="#" class="text-gray-400 hover:text-white transition">
                Resep Digital
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-400 hover:text-white transition">
                Bantuan & FAQ
              </a>
            </li>
          </ul>
        </div>

        <!-- Untuk Dokter -->
        <div class="space-y-4">
          <h3 class="text-white font-bold text-lg">Untuk Dokter</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <router-link to="/register/dokter" class="text-gray-400 hover:text-white transition">
                Daftar Sekarang
              </router-link>
            </li>
            <li>
              <router-link to="/login" class="text-gray-400 hover:text-white transition">
                Login Dashboard
              </router-link>
            </li>
            <li>
              <a href="#" class="text-gray-400 hover:text-white transition">
                Bantuan Dokter
              </a>
            </li>
          </ul>
        </div>

        <!-- Legal & Dukungan -->
        <div class="space-y-4">
          <h3 class="text-white font-bold text-lg">Legal</h3>
          <ul class="space-y-3 text-sm">
            <li>
              <router-link to="/terms" class="inline-flex items-center text-gray-400 hover:text-white hover:underline transition">
                Syarat & Ketentuan
              </router-link>
            </li>
            <li>
              <router-link to="/privacy" class="inline-flex items-center text-gray-400 hover:text-white hover:underline transition">
                Kebijakan Privasi
              </router-link>
            </li>
            <li>
              <router-link to="/cookie-policy" class="inline-flex items-center text-gray-400 hover:text-white hover:underline transition">
                Kebijakan Cookie
              </router-link>
            </li>
            <li>
              <router-link to="/compliance" class="inline-flex items-center text-gray-400 hover:text-white hover:underline transition">
                Compliance
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Kontak & Dukungan -->
        <div class="space-y-4">
          <h3 class="text-white font-bold text-lg">Kontak</h3>
          <ul class="space-y-3 text-sm">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-indigo-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <div>
                <p class="font-semibold text-white text-xs">Email</p>
                <a href="mailto:angguny03@gmail.com" class="text-gray-400 hover:text-indigo-400 transition break-all">
                  angguny03@gmail.com
                </a>
              </div>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-indigo-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <div>
                <p class="font-semibold text-white text-xs">WhatsApp</p>
                <a href="https://wa.me/628885773781" target="_blank" class="text-gray-400 hover:text-indigo-400 transition">
                  +62 888 5773 781
                </a>
              </div>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-indigo-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <div>
                <p class="font-semibold text-white text-xs">Alamat</p>
                <p class="text-gray-400">JL KH Al Masyhuri Abdulloh, Sambirejo, Pasuruan, Jawa Timur</p>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-gray-700 mt-12 pt-8">
        <!-- Bottom Footer -->
        <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
          <p>&copy; {{ currentYear }} Platform Telemedicine. Kesehatan Anda adalah prioritas kami</p>
          <div class="flex gap-6 mt-4 md:mt-0">
            <span class="text-xs text-indigo-400">[CHECK] Data Terenkripsi</span>
            <span class="text-xs text-indigo-400">[CHECK] Keamanan HIPAA</span>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue'

const currentYear = ref(new Date().getFullYear())
</script>

<style scoped>
footer a {
  text-decoration: none;
}

footer a:hover {
  text-decoration: none;
}
</style>
