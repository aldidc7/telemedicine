<!-- Dokter Profile Page -->
<template>
  <div class="min-h-screen bg-linear-to-br from-gray-50 via-gray-50 to-gray-100 pb-12">
    <!-- Hero Profile Section -->
    <div class="bg-linear-to-r from-indigo-600 via-indigo-500 to-purple-600 text-white py-12 mb-12 shadow-lg">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-start gap-6">
          <!-- Profile Photo in Header -->
          <div class="shrink-0 hidden sm:block">
            <div v-if="profile.profile_photo && typeof profile.profile_photo === 'string' && profile.profile_photo.length > 0" class="relative">
              <img :src="getPhotoUrl(profile.profile_photo)" :alt="profile.name" class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-xl" @error="photoError = true" />
            </div>
            <div v-else class="w-24 h-24 rounded-2xl bg-white/20 border-4 border-white flex items-center justify-center shadow-xl">
              <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
              </svg>
            </div>
          </div>
          <!-- Header Text -->
          <div class="flex-1">
            <h1 class="text-4xl md:text-5xl font-black mb-2">{{ profile.name || 'Profil Dokter' }}</h1>
            <p v-if="profile.specialization" class="text-indigo-100 text-lg font-semibold mb-3">{{ profile.specialization }}</p>
            <p class="text-indigo-100 text-sm">Kelola informasi profesional dan data pribadi Anda</p>
          </div>
          <!-- Action Button in Header -->
          <button
            @click="showEditForm = true"
            class="shrink-0 px-6 py-3 bg-white text-indigo-600 rounded-xl hover:bg-indigo-50 transition font-bold shadow-lg hover:shadow-xl flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin w-12 h-12 border-4 border-gray-300 border-t-indigo-600 rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600 text-lg">Memuat profil...</p>
      </div>

      <div v-else class="space-y-8">
        <!-- Info Card -->
        <div class="bg-white rounded-3xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition">
          <!-- Header -->
          <div class="bg-linear-to-r from-indigo-600 via-indigo-500 to-purple-600 px-8 py-10 text-white">
            <h2 class="text-2xl font-bold mb-1">Informasi Profil</h2>
            <p class="text-indigo-100 text-sm">Data lengkap profil profesional Anda</p>
          </div>

          <div class="p-10">
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 mb-8">
              <button
                @click="showEditForm = true"
                class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-bold shadow-md hover:shadow-lg flex items-center justify-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Profil
              </button>
              <button
                @click="syncToPatient"
                :disabled="syncLoading"
                class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-bold shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-emerald-600 flex items-center justify-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-6"/></svg>
                {{ syncLoading ? 'Sinkronisasi...' : 'Sync ke Pasien' }}
              </button>
            </div>

            <!-- Info Badge -->
            <div class="mb-10 p-6 bg-linear-to-r from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-2xl">
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center shrink-0 mt-0.5">
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 3.27c0 2.756-2.665 5.027-5.98 5.027-.354 0-.68-.021-.988-.053a3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723c-2.312 0-4.255-1.43-5.026-3.387-.542-1.694-.542-3.866 0-5.56.77-1.957 2.714-3.387 5.026-3.387.566 0 1.108.08 1.636.212a3.066 3.066 0 001.745-.723zm0 0c-.727.127-1.432.198-2.112.198s-1.385-.071-2.112-.198" clip-rule="evenodd" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-bold text-blue-900">Lengkapi Profil Anda</p>
                  <p class="text-sm text-blue-800 mt-1">Pastikan semua informasi terisi dengan benar untuk memberikan kepercayaan maksimal kepada pasien</p>
                </div>
              </div>
            </div>

            <!-- Grid Profile Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <!-- Nama -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <p class="text-lg text-gray-900 font-bold">{{ profile.name }}</p>
              </div>

              <!-- Email -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Email</label>
                <p class="text-base text-gray-900 font-medium break-all">{{ profile.email }}</p>
              </div>

              <!-- Spesialisasi -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Spesialisasi</label>
                <div class="inline-flex items-center bg-indigo-50 text-indigo-700 px-4 py-2 rounded-xl font-semibold border border-indigo-200 group-hover:bg-indigo-100">
                  {{ profile.specialization || 'Belum diisi' }}
                </div>
              </div>

              <!-- No Lisensi -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">No. Lisensi (SIP/SIK)</label>
                <p class="text-base text-gray-900 font-medium">{{ profile.license_number || '-' }}</p>
              </div>

              <!-- No Telepon -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">No. Telepon</label>
                <p class="text-base text-gray-900 font-medium">{{ profile.phone_number || '-' }}</p>
              </div>

              <!-- Jenis Kelamin -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Jenis Kelamin</label>
                <p class="text-base text-gray-900 font-medium">{{ formatGender(profile.gender) || '-' }}</p>
              </div>

              <!-- Tempat Lahir -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Tempat Lahir</label>
                <p class="text-base text-gray-900 font-medium">{{ profile.birthplace_city || '-' }}</p>
              </div>

              <!-- Golongan Darah -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Golongan Darah</label>
                <p class="text-base text-gray-900 font-medium">{{ profile.blood_type || '-' }}</p>
              </div>

              <!-- Status Pernikahan -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Status Pernikahan</label>
                <p class="text-base text-gray-900 font-medium">{{ formatMaritalStatus(profile.marital_status) || '-' }}</p>
              </div>

              <!-- Suku -->
              <div class="group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Suku</label>
                <p class="text-base text-gray-900 font-medium">{{ profile.ethnicity || '-' }}</p>
              </div>

              <!-- Alamat -->
              <div class="md:col-span-2 group bg-linear-to-br from-gray-50 to-gray-100 hover:from-indigo-50 hover:to-purple-50 rounded-2xl p-6 border border-gray-200 hover:border-indigo-200 transition cursor-default">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Alamat</label>
                <p class="text-base text-gray-900 font-medium leading-relaxed">{{ profile.address || '-' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Ratings Card -->
        <div class="bg-white rounded-3xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition">
          <!-- Header -->
          <div class="bg-linear-to-r from-amber-500 via-orange-500 to-red-500 px-8 py-10 text-white">
            <h2 class="text-2xl font-bold mb-1">Rating & Ulasan</h2>
            <p class="text-amber-50 text-sm">Penilaian dari pasien yang telah berkonsultasi</p>
          </div>
          <div class="p-10">
            <RatingDisplay v-if="profile.dokter_id" :dokter_id="profile.dokter_id" />
            <div v-else class="text-center py-8 text-gray-500">
              <p>Memuat data rating...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div v-if="showEditForm" @click.self="showEditForm = false" class="fixed inset-0 bg-black/30 backdrop-blur-md flex items-center justify-center z-50 p-4 scroll-smooth">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] border border-gray-200 flex flex-col">
          <!-- Modal Header (Sticky) -->
          <div class="sticky top-0 bg-linear-to-r from-indigo-600 to-purple-600 text-white px-8 py-6 flex justify-between items-center border-b-2 border-indigo-400 rounded-t-2xl z-10 shadow-md">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <div>
                <h3 class="text-xl font-bold">Edit Profil</h3>
                <p class="text-indigo-100 text-xs">Perbarui informasi Anda</p>
              </div>
            </div>
            <button @click="showEditForm = false" class="text-indigo-100 hover:text-white text-2xl transition p-1 shrink-0">
              ✕
            </button>
          </div>

          <!-- Scrollable Content -->
          <div class="flex-1 overflow-y-auto px-8 py-8">
          <!-- Error Message -->
          <div v-if="editError" class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
            <p class="text-red-700 font-semibold text-sm whitespace-pre-wrap">{{ editError }}</p>
          </div>

          <!-- Form -->
          <form id="edit-form-submit" @submit.prevent="saveProfile" class="space-y-8">
            <!-- Nama -->
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Lengkap *</label>
              <input v-model="editForm.name" @focus="clearFieldError('name')" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" required />
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Email *</label>
              <input v-model="editForm.email" @focus="clearFieldError('email')" type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" required />
            </div>

            <!-- Divider -->
            <div class="pt-4 pb-6">
              <div class="flex items-center gap-3">
                <div class="flex-1 border-t border-gray-200"></div>
                <div class="flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-lg">
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Data Pribadi</p>
                </div>
                <div class="flex-1 border-t border-gray-200"></div>
              </div>
            </div>

            <!-- Grid: Personal Data - 2 columns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Jenis Kelamin -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Jenis Kelamin</label>
                <select v-model="editForm.gender" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition">
                  <option value="">Pilih Jenis Kelamin</option>
                  <option value="laki-laki">Laki-laki</option>
                  <option value="perempuan">Perempuan</option>
                </select>
              </div>

              <!-- No Telepon -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">No. Telepon</label>
                <input v-model="editForm.phone_number" type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" />
              </div>

              <!-- Tempat Lahir (Kota) -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Tempat Lahir (Kota)</label>
                <input v-model="editForm.birthplace_city" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" placeholder="Contoh: Jakarta" />
              </div>

              <!-- Tanggal Lahir -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Tanggal Lahir</label>
                <input v-model="editForm.place_of_birth" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" />
              </div>

              <!-- Golongan Darah -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Golongan Darah</label>
                <select v-model="editForm.blood_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition">
                  <option value="">Pilih Golongan Darah</option>
                  <option value="O">O</option>
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="AB">AB</option>
                </select>
              </div>

              <!-- Status Pernikahan -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Status Pernikahan</label>
                <select v-model="editForm.marital_status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition">
                  <option value="">Pilih Status</option>
                  <option value="belum_menikah">Belum Menikah</option>
                  <option value="menikah">Menikah</option>
                  <option value="cerai">Cerai</option>
                  <option value="cerai_mati">Cerai Mati</option>
                </select>
              </div>

              <!-- Suku/Etnis -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Suku/Etnis</label>
                <input v-model="editForm.ethnicity" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" placeholder="Contoh: Jawa" />
              </div>
            </div>

            <!-- Divider -->
            <div class="pt-4 pb-6">
              <div class="flex items-center gap-3">
                <div class="flex-1 border-t border-gray-200"></div>
                <div class="flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-lg">
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Data Profesional</p>
                </div>
                <div class="flex-1 border-t border-gray-200"></div>
              </div>
            </div>

            <!-- Spesialisasi -->
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Spesialisasi *</label>
              <select v-model="editForm.specialization" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition">
                <option value="">Pilih Spesialisasi</option>
                <option value="Umum">Dokter Umum</option>
                <option value="Gigi">Gigi</option>
                <option value="Anak">Anak</option>
                <option value="Kandungan">Kandungan</option>
                <option value="Jantung">Jantung</option>
                <option value="Paru-paru">Paru-paru</option>
                <option value="Kulit">Kulit</option>
                <option value="Ortopedi">Ortopedi</option>
              </select>
            </div>

            <!-- No Lisensi -->
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">No. Lisensi (SIP/SIK)</label>
              <input v-model="editForm.license_number" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" placeholder="Contoh: SIP-JE-2020-000001" />
            </div>

            <!-- Alamat -->
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Alamat</label>
              <textarea v-model="editForm.address" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition" placeholder="Jln. Merdeka No.123, Kelurahan..." />
            </div>

            <!-- Divider -->
            <div class="pt-4 pb-6">
              <div class="flex items-center gap-3">
                <div class="flex-1 border-t border-gray-200"></div>
                <div class="flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-lg">
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Foto Profil</p>
                </div>
                <div class="flex-1 border-t border-gray-200"></div>
              </div>
            </div>

            <!-- Foto Profil -->
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-3">Foto Profil</label>
              <div class="flex flex-col gap-4">
                <div class="relative">
                  <input type="file" @change="handlePhotoUpload" accept="image/jpeg,image/png,image/jpg,image/gif" class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-white hover:border-indigo-300 transition cursor-pointer" />
                </div>
                <p class="text-xs text-gray-500">Format: JPG, PNG, GIF | Ukuran maksimal: 5MB</p>

                <!-- Preview foto lama -->
                <div v-if="profile.profile_photo" class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                  <img :src="getPhotoUrl(profile.profile_photo)" :alt="profile.name" class="w-14 h-14 rounded-lg object-cover border-2 border-blue-300" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700">Foto saat ini</p>
                    <p class="text-xs text-gray-500 truncate">Upload foto baru untuk mengganti</p>
                  </div>
                </div>

                <!-- Preview foto baru -->
                <div v-if="photoPreview" class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-200">
                  <img :src="photoPreview" alt="preview" class="w-14 h-14 rounded-lg object-cover border-2 border-green-300" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-green-700">Foto baru</p>
                    <p class="text-xs text-gray-500 truncate">{{ photoFile?.name }}</p>
                  </div>
                </div>
              </div>
            </div>

            </form>
          </div>

          <!-- Form Buttons (Sticky Footer) -->
          <div class="sticky bottom-0 bg-linear-to-r from-gray-50 to-white border-t border-gray-200 px-8 py-6 flex gap-3 rounded-b-2xl shadow-lg">
            <button form="edit-form-submit" type="submit" :disabled="editLoading" class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 active:scale-95 transition disabled:opacity-50 disabled:cursor-not-allowed font-bold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <svg v-if="!editLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              {{ editLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
            <button type="button" @click="showEditForm = false" class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 active:scale-95 transition font-bold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { dokterAPI } from '@/api/dokter'
import RatingDisplay from '@/components/RatingDisplay.vue'

const authStore = useAuthStore()
const loading = ref(false)
const editLoading = ref(false)
const syncLoading = ref(false)
const showEditForm = ref(false)
const editError = ref(null)
const fieldErrors = ref({})
const photoError = ref(false)
const profile = ref({})
const photoFile = ref(null)
const photoPreview = ref(null)
const editForm = ref({
  name: '',
  email: '',
  phone_number: '',
  gender: '',
  birthplace_city: '',
  place_of_birth: '',
  blood_type: '',
  marital_status: '',
  ethnicity: '',
  specialization: '',
  license_number: '',
  address: '',
  profile_photo: ''
})

const formatGender = (value) => {
  const map = {
    'laki-laki': 'Laki-laki',
    'perempuan': 'Perempuan'
  }
  return map[value] || value
}

const formatMaritalStatus = (value) => {
  const map = {
    'belum_menikah': 'Belum Menikah',
    'menikah': 'Menikah',
    'cerai': 'Cerai',
    'cerai_mati': 'Cerai Mati'
  }
  return map[value] || value
}

onMounted(async () => {
  await loadProfile()
})

const loadProfile = async () => {
  loading.value = true
  editError.value = null
  fieldErrors.value = {}
  try {
    // First, try to use data from authStore (no network call needed)
    let dokterData = null
    
    if (authStore.user?.dokter) {
      // Data already available from login response - use it immediately
      console.log('Using dokter data from authStore (instant)')
      dokterData = authStore.user.dokter
    } else {
      // If not in authStore, fetch from API with timeout
      console.log('Fetching dokter data from API...')
      const timeoutPromise = new Promise((resolve) => {
        setTimeout(() => {
          console.warn('Profile load timeout')
          resolve(null)
        }, 5000)
      })

      const dokterResponse = await Promise.race([
        dokterAPI.getByUserId(authStore.user.id),
        timeoutPromise
      ])

      if (!dokterResponse) {
        editError.value = 'Gagal memuat profil - Request timeout'
        loading.value = false
        return
      }

      dokterData = dokterResponse?.data?.data
    }

    if (!dokterData || !dokterData.id) {
      editError.value = 'Data dokter tidak valid atau tidak ditemukan'
      loading.value = false
      return
    }

    // Properly merge user data dan dokter data
    profile.value = {
      // User data
      id: authStore.user?.id,
      name: authStore.user?.name || '',
      email: authStore.user?.email || '',
      role: authStore.user?.role || 'dokter',
      
      // Dokter data
      dokter_id: dokterData.id,
      specialization: dokterData.specialization || 'Belum diisi',
      license_number: dokterData.license_number || '-',
      phone_number: dokterData.phone_number || '-',
      address: dokterData.address || '',
      gender: dokterData.gender || '',
      birthplace_city: dokterData.birthplace_city || '',
      place_of_birth: dokterData.place_of_birth || '',
      blood_type: dokterData.blood_type || '',
      marital_status: dokterData.marital_status || '',
      ethnicity: dokterData.ethnicity || '',
      profile_photo: dokterData.profile_photo || '',
      is_available: dokterData.is_available || false
    }

    editForm.value = {
      name: profile.value.name,
      email: profile.value.email,
      phone_number: profile.value.phone_number || '',
      gender: profile.value.gender || '',
      birthplace_city: profile.value.birthplace_city || '',
      place_of_birth: profile.value.place_of_birth || '',
      blood_type: profile.value.blood_type || '',
      marital_status: profile.value.marital_status || '',
      ethnicity: profile.value.ethnicity || '',
      specialization: profile.value.specialization || '',
      license_number: profile.value.license_number || '',
      address: profile.value.address || '',
      profile_photo: profile.value.profile_photo || ''
    }
  } catch (error) {
    console.error('Error loading profile:', error)
    editError.value = error?.response?.data?.pesan || error?.message || 'Gagal memuat data profil'
  } finally {
    loading.value = false
  }
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  const maxSize = 5 * 1024 * 1024
  if (file.size > maxSize) {
    editError.value = 'Ukuran file terlalu besar. Maksimal 5MB.'
    return
  }

  // Check file extension
  const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']
  const fileExtension = file.name.split('.').pop().toLowerCase()
  if (!allowedExtensions.includes(fileExtension)) {
    editError.value = 'Format file tidak didukung. Gunakan JPEG, PNG, atau GIF.'
    return
  }

  // Check MIME type
  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif']
  if (!allowedTypes.includes(file.type)) {
    editError.value = `Format file tidak valid. MIME type: ${file.type}. Harus image/jpeg, image/png, atau image/gif.`
    return
  }

  photoFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    photoPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
  editError.value = null
}

const getPhotoUrl = (photoPath) => {
  if (!photoPath || photoPath === '' || typeof photoPath !== 'string') {
    console.log('Invalid photo path:', photoPath, typeof photoPath)
    return ''
  }
  // Photo path dari API sudah include /storage/ prefix
  // Jangan add prefix lagi kalau sudah ada
  const url = photoPath.startsWith('/storage/') ? photoPath : `/storage/${photoPath}`
  console.log('Photo URL:', url)
  return url
}

const clearFieldError = (fieldName) => {
  delete fieldErrors.value[fieldName]
  if (Object.keys(fieldErrors.value).length === 0) {
    editError.value = null
  }
}

const saveProfile = async () => {
  if (!profile.value.dokter_id) {
    editError.value = 'Data dokter tidak valid. Silakan refresh halaman.'
    return
  }

  editLoading.value = true
  editError.value = null
  fieldErrors.value = {}
  try {
    // IMPORTANT: Jangan gunakan object form data langsung, selalu gunakan FormData
    // untuk menangani file upload dengan benar
    const data = new FormData()
    
    // List of fields yang TIDAK boleh di-append ke FormData
    const skipFields = ['profile_photo', 'dokter_id']
    
    // Append semua field YANG BOLEH
    Object.keys(editForm.value).forEach(key => {
      // Skip fields yang tidak boleh dikirim
      if (skipFields.includes(key)) {
        console.log(`Skipping field: ${key}`)
        return
      }
      
      const value = editForm.value[key]
      // Only append non-empty values to reduce payload
      if (value !== null && value !== undefined && value !== '') {
        data.append(key, value)
      }
    })
    
    // Append file HANYA jika ada file baru yang dipilih
    if (photoFile.value) {
      data.append('profile_photo', photoFile.value)
      console.log('Appending new photo file:', photoFile.value.name, photoFile.value.size, 'bytes')
    }
    
    // Debug: Log FormData contents
    console.log('=== FormData contents being sent ===')
    for (let pair of data.entries()) {
      if (pair[0] === 'profile_photo' && pair[1] instanceof File) {
        console.log(pair[0], ': File -', pair[1].name)
      } else {
        console.log(pair[0], ':', pair[1])
      }
    }
    console.log('====================================')
    
    const response = await dokterAPI.update(profile.value.dokter_id, data)

    // Update profile dengan data dari response
    profile.value = { ...profile.value, ...response.data.data }
    // Pastikan profile_photo ter-update
    if (response.data.data.profile_photo) {
      profile.value.profile_photo = response.data.data.profile_photo
      console.log('Photo updated successfully:', profile.value.profile_photo)
    } else {
      console.log('No photo in response')
    }
    authStore.user = { ...authStore.user, name: response.data.data.name, email: response.data.data.email }

    photoFile.value = null
    photoPreview.value = null

    showEditForm.value = false
  } catch (error) {
    console.error('Save error:', error)
    
    // Handle validation errors (field-specific)
    if (error.response?.status === 422 && error.response?.data?.errors) {
      fieldErrors.value = error.response.data.errors
      const errorFields = Object.keys(fieldErrors.value)
      const fieldNames = {
        'name': 'Nama Lengkap',
        'email': 'Email',
        'phone_number': 'No. Telepon',
        'gender': 'Jenis Kelamin',
        'birthplace_city': 'Tempat Lahir',
        'place_of_birth': 'Tanggal Lahir',
        'blood_type': 'Golongan Darah',
        'marital_status': 'Status Pernikahan',
        'ethnicity': 'Suku/Etnis',
        'specialization': 'Spesialisasi',
        'license_number': 'No. Lisensi',
        'address': 'Alamat',
        'profile_photo': 'Foto Profil'
      }
      
      const errorMessages = errorFields.map(field => {
        const fieldLabel = fieldNames[field] || field
        const errors = Array.isArray(fieldErrors.value[field]) ? fieldErrors.value[field] : [fieldErrors.value[field]]
        return `${fieldLabel}: ${errors[0]}`
      })
      
      editError.value = errorMessages.join(' | ')
    } else {
      editError.value = error.response?.data?.pesan || 'Gagal menyimpan profil'
    }
  } finally {
    editLoading.value = false
  }
}

const syncToPatient = async () => {
  syncLoading.value = true
  try {
    await dokterAPI.syncToPatient(profile.value.dokter_id, {
      gender: editForm.value.gender,
      birthplace_city: editForm.value.birthplace_city,
      blood_type: editForm.value.blood_type,
      address: editForm.value.address,
      ethnicity: editForm.value.ethnicity
    })

    editError.value = null
    alert('Data berhasil disinkronisasi ke profil pasien!')
  } catch (error) {
    console.error('Sync error:', error)
    editError.value = error.response?.data?.pesan || 'Gagal sinkronisasi data'
  } finally {
    syncLoading.value = false
  }
}
</script>
