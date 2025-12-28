<!-- [PROFILE] resources/js/views/pasien/ProfilePage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-b from-gray-50 to-gray-100 pb-12">
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white py-16 mb-12">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-black mb-2">Profil Saya</h1>
        <p class="text-xl text-indigo-100">Kelola informasi pribadi dan kesehatan Anda dengan aman</p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Success Alert -->
      <AlertBox v-if="editSuccess" type="success" :message="editSuccess" @close="editSuccess = null" class="mb-6" />

      <div v-if="loading" class="text-center py-16">
        <div class="animate-spin w-12 h-12 border-4 border-gray-200 border-t-indigo-600 rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600 text-lg">Memuat profil...</p>
      </div>

      <div v-else class="space-y-8">
        <!-- Profile Card -->
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
          <div class="bg-linear-to-r from-indigo-600 to-purple-600 px-8 py-12 text-white relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
            <div class="relative z-10 flex items-end gap-6">
              <div class="w-24 h-24 bg-white rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                <svg class="w-12 h-12 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-3xl font-black">{{ profile.name }}</h2>
                <p class="text-indigo-100 mt-1 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                  Pasien Terdaftar
                </p>
              </div>
            </div>
          </div>

          <!-- Info Section -->
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Column 1 -->
              <div class="space-y-6">
                <!-- Nama Lengkap -->
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Nama Lengkap</p>
                  </div>
                  <p class="text-lg font-semibold text-gray-900">{{ profile.name }}</p>
                </div>

                <!-- NIK -->
                <div v-if="profile.nik" class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
                    </svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">NIK</p>
                  </div>
                  <p class="text-lg font-semibold text-gray-900">{{ profile.nik }}</p>
                </div>

                <!-- Email -->
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                      <path d="M22 6l-10 7L2 6" fill="none" stroke="currentColor" stroke-width="2" />
                    </svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Email</p>
                  </div>
                  <p class="text-lg font-semibold text-gray-900">{{ profile.email }}</p>
                </div>

                <!-- Telepon -->
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
                    </svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">No. Telepon</p>
                  </div>
                  <p class="text-lg font-semibold text-gray-900">{{ profile.phone_number }}</p>
                </div>

                <!-- Tanggal Lahir -->
                <div v-if="profile.date_of_birth" class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zm-5-5h-4v4h4z" />
                    </svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Tanggal Lahir</p>
                  </div>
                  <p class="text-lg font-semibold text-gray-900">
                    {{ new Date(profile.date_of_birth).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                  </p>
                  <p class="text-xs text-gray-600 mt-1">Umur: {{ getAge() }} tahun</p>
                </div>

                <!-- Tempat Lahir -->
                <div v-if="profile.place_of_birth" class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Tempat Lahir</p>
                  </div>
                  <p class="text-lg font-semibold text-gray-900">{{ profile.place_of_birth }}</p>
                </div>
              </div>

              <!-- Column 2 -->
              <div class="space-y-6">
                <!-- Jenis Kelamin -->
                <div v-if="profile.gender" class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                      <path v-if="profile.gender === 'M' || profile.gender === 'Laki-laki' || profile.gender === 'laki-laki'" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                      <path v-else d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Jenis Kelamin</p>
                  </div>
                  <p class="text-lg font-semibold text-gray-900">
                    {{ profile.gender === 'M' || profile.gender === 'Laki-laki' || profile.gender === 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}
                  </p>
                </div>

                <!-- Status Pernikahan -->
                <div v-if="profile.marital_status" class="bg-blue-50 rounded-2xl p-5 border border-blue-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M13 11h8V8l3 4-3 4v-3h-8v-2m-2-7a2.5 2.5 0 100 5 2.5 2.5 0 000-5M5 13c-1.65 0-3 1.35-3 3s1.35 3 3 3 3-1.35 3-3-1.35-3-3-3z" />
                    </svg>
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-wide">Status Pernikahan</p>
                  </div>
                  <p class="text-lg font-semibold text-blue-900">{{ formatMaritalStatus(profile.marital_status) }}</p>
                </div>

                <!-- Agama -->
                <div v-if="profile.religion" class="bg-purple-50 rounded-2xl p-5 border border-purple-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15h-2v-2h2v2zm0-4h-2V7h2v6zm4 4h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                    <p class="text-xs font-bold text-purple-600 uppercase tracking-wide">Agama</p>
                  </div>
                  <p class="text-lg font-semibold text-purple-900">{{ profile.religion }}</p>
                </div>

                <!-- Suku -->
                <div v-if="profile.ethnicity" class="bg-orange-50 rounded-2xl p-5 border border-orange-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                    <p class="text-xs font-bold text-orange-600 uppercase tracking-wide">Suku</p>
                  </div>
                  <p class="text-lg font-semibold text-orange-900">{{ profile.ethnicity }}</p>
                </div>

                <!-- Golongan Darah -->
                <div v-if="profile.blood_type" class="bg-red-50 rounded-2xl p-5 border border-red-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2c-1.1 0-2 .9-2 2v5H8l4 7 4-7h-2V4c0-1.1-.9-2-2-2z" />
                    </svg>
                    <p class="text-xs font-bold text-red-600 uppercase tracking-wide">Golongan Darah</p>
                  </div>
                  <p class="text-xl font-bold text-red-700">{{ profile.blood_type }}</p>
                </div>

                <!-- Kontak Darurat (Opsional) -->
                <div v-if="profile.emergency_contact_name || profile.emergency_contact_phone" class="bg-green-50 rounded-2xl p-5 border border-green-200">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.93 6 11v5l-2 2v1h16v-1l-2-2z" />
                    </svg>
                    <p class="text-xs font-bold text-green-600 uppercase tracking-wide">Kontak Darurat</p>
                  </div>
                  <p v-if="profile.emergency_contact_name" class="text-lg font-semibold text-green-900">{{ profile.emergency_contact_name }}</p>
                  <p v-if="profile.emergency_contact_phone" class="text-sm text-green-700 mt-1">{{ profile.emergency_contact_phone }}</p>
                </div>
              </div>
            </div>

            <!-- Full Width - Alamat -->
            <div v-if="profile.address" class="mt-6 bg-gray-50 rounded-2xl p-5 border border-gray-200">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                </svg>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Alamat</p>
              </div>
              <p class="text-gray-900">{{ profile.address }}</p>
            </div>

            <!-- Edit Button -->
            <button
              @click="showEditForm = true"
              class="mt-8 w-full md:w-auto px-8 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-bold flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit Profil
            </button>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div
        v-if="showEditForm"
        @click="showEditForm = false"
        class="fixed inset-0 backdrop-blur-md flex items-center justify-center z-50 p-4"
      >
        <div
          @click.stop
          class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col border border-gray-200"
        >
          <!-- Sticky Header -->
          <div class="sticky top-0 bg-linear-to-r from-indigo-600 to-purple-600 text-white px-8 py-6 flex justify-between items-center border-b-2 border-indigo-400 rounded-t-2xl z-10 shadow-md">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <div>
                <h3 class="text-xl font-bold">Edit Profil</h3>
                <p class="text-indigo-100 text-xs">Perbarui informasi pribadi Anda</p>
              </div>
            </div>
            <button
              @click="closeEditForm"
              type="button"
              class="text-indigo-100 hover:text-white p-2 rounded-lg transition"
              title="Tutup"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Scrollable Content -->
          <div class="flex-1 overflow-y-auto px-8 py-8">
            <div v-if="editError" class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
              <p class="text-red-700 font-semibold text-sm">{{ editError }}</p>
            </div>

            <form id="edit-form-pasien" @submit.prevent="saveProfile" class="space-y-8">
              <!-- Basic Info Section -->
              <div>
                <div class="flex items-center gap-3 mb-6">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <h4 class="text-lg font-bold text-gray-900">Data Dasar</h4>
                </div>
                
                <div class="space-y-5">
                  <!-- Nama -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Lengkap</label>
                    <input
                      v-model="editForm.name"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      required
                    />
                  </div>

                  <!-- Email -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
                    <input
                      v-model="editForm.email"
                      type="email"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      required
                    />
                  </div>

                  <!-- No Telepon -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">No. Telepon</label>
                    <input
                      v-model="editForm.phone_number"
                      type="tel"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      required
                    />
                  </div>

                  <!-- NIK -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">NIK</label>
                    <input
                      v-model="editForm.nik"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      placeholder="Nomor Identitas Kependudukan"
                    />
                  </div>
                </div>
              </div>

              <!-- Divider -->
              <div class="border-t border-gray-200 pt-8"></div>

              <!-- Personal Info Section -->
              <div>
                <div class="flex items-center gap-3 mb-6">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <h4 class="text-lg font-bold text-gray-900">Data Pribadi</h4>
                </div>

                <div class="space-y-5">
                  <!-- Tanggal Lahir -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Tanggal Lahir</label>
                    <input
                      v-model="editForm.date_of_birth"
                      type="date"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                    />
                  </div>

                  <!-- Tempat Lahir -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Tempat Lahir</label>
                    <input
                      v-model="editForm.place_of_birth"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      placeholder="Kota/Kabupaten lahir"
                    />
                  </div>

                  <!-- Jenis Kelamin -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Jenis Kelamin</label>
                    <select
                      v-model="editForm.gender"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                    >
                      <option value="">Pilih Jenis Kelamin</option>
                      <option value="M">Laki-laki</option>
                      <option value="F">Perempuan</option>
                    </select>
                  </div>

                  <!-- Status Pernikahan -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Status Pernikahan</label>
                    <select
                      v-model="editForm.marital_status"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                    >
                      <option value="">Pilih Status Pernikahan</option>
                      <option value="single">Belum Menikah</option>
                      <option value="married">Menikah</option>
                      <option value="divorced">Bercerai</option>
                      <option value="widowed">Duda/Janda</option>
                    </select>
                  </div>

                  <!-- Agama -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Agama</label>
                    <select
                      v-model="editForm.religion"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                    >
                      <option value="">Pilih Agama</option>
                      <option value="Islam">Islam</option>
                      <option value="Kristen">Kristen</option>
                      <option value="Katholik">Katholik</option>
                      <option value="Hindu">Hindu</option>
                      <option value="Buddha">Buddha</option>
                      <option value="Konghucu">Konghucu</option>
                    </select>
                  </div>

                  <!-- Suku -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Suku</label>
                    <input
                      v-model="editForm.ethnicity"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      placeholder="Contoh: Jawa, Sunda, Batak, dll"
                    />
                  </div>

                  <!-- Golongan Darah -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Golongan Darah</label>
                    <select
                      v-model="editForm.blood_type"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                    >
                      <option value="">Pilih Golongan Darah</option>
                      <option value="A">A</option>
                      <option value="B">B</option>
                      <option value="AB">AB</option>
                      <option value="O">O</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Divider -->
              <div class="border-t border-gray-200 pt-8"></div>

              <!-- Emergency & Address Section -->
              <div>
                <div class="flex items-center gap-3 mb-6">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <h4 class="text-lg font-bold text-gray-900">Kontak Darurat & Alamat</h4>
                </div>

                <div class="space-y-5">
                  <!-- Nama Kontak Darurat -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Kontak Darurat</label>
                    <input
                      v-model="editForm.emergency_contact_name"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      placeholder="Nama orang yang dapat dihubungi"
                    />
                  </div>

                  <!-- No Telepon Kontak Darurat -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">No. Telepon Kontak Darurat</label>
                    <input
                      v-model="editForm.emergency_contact_phone"
                      type="tel"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      placeholder="Nomor telepon kontak darurat"
                    />
                  </div>

                  <!-- Alamat -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Alamat</label>
                    <textarea
                      v-model="editForm.address"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition"
                      rows="4"
                      placeholder="Masukkan alamat lengkap Anda"
                    ></textarea>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <!-- Sticky Footer -->
          <div class="sticky bottom-0 bg-linear-to-r from-gray-50 to-white border-t border-gray-200 px-8 py-6 flex gap-3 rounded-b-2xl shadow-lg">
            <button
              form="edit-form-pasien"
              type="submit"
              :disabled="editLoading"
              class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 active:scale-95 transition disabled:opacity-50 disabled:cursor-not-allowed font-bold shadow-md hover:shadow-lg flex items-center justify-center gap-2"
            >
              <svg v-if="!editLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              {{ editLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
            <button
              type="button"
              @click="closeEditForm"
              class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 active:scale-95 transition font-bold shadow-md hover:shadow-lg flex items-center justify-center gap-2"
            >
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
import { pasienAPI } from '@/api/pasien'
import AlertBox from '@/components/AlertBox.vue'

const authStore = useAuthStore()
const loading = ref(false)
const editLoading = ref(false)
const showEditForm = ref(false)
const editError = ref(null)
const editSuccess = ref(null)
const profile = ref({})
const editForm = ref({
  name: '',
  email: '',
  phone_number: '',
  address: '',
  nik: '',
  date_of_birth: '',
  place_of_birth: '',
  gender: '',
  marital_status: '',
  religion: '',
  ethnicity: '',
  blood_type: '',
  emergency_contact_name: '',
  emergency_contact_phone: ''
})

onMounted(async () => {
  await loadProfile()
})

const loadProfile = async () => {
  loading.value = true
  try {
    profile.value = authStore.user || {}
    
    editForm.value = {
      name: profile.value.name || '',
      email: profile.value.email || '',
      phone_number: profile.value.phone_number || '',
      address: profile.value.address || '',
      nik: profile.value.nik || '',
      date_of_birth: profile.value.date_of_birth || '',
      place_of_birth: profile.value.place_of_birth || '',
      gender: profile.value.gender || '',
      marital_status: profile.value.marital_status || '',
      religion: profile.value.religion || '',
      ethnicity: profile.value.ethnicity || '',
      blood_type: profile.value.blood_type || '',
      emergency_contact_name: profile.value.emergency_contact_name || '',
      emergency_contact_phone: profile.value.emergency_contact_phone || ''
    }
  } catch (error) {
    console.error('Error loading profile:', error)
  } finally {
    loading.value = false
  }
}

const saveProfile = async () => {
  editLoading.value = true
  editError.value = null
  editSuccess.value = null
  try {
    // Validate required fields
    if (!editForm.value.name?.trim()) {
      throw new Error('Nama lengkap tidak boleh kosong')
    }
    if (!editForm.value.email?.trim()) {
      throw new Error('Email tidak boleh kosong')
    }
    if (!editForm.value.phone_number?.trim()) {
      throw new Error('No. Telepon tidak boleh kosong')
    }

    // Prepare data with proper format for backend
    const dataToSend = {
      name: editForm.value.name?.trim() || '',
      email: editForm.value.email?.trim() || '',
      phone_number: editForm.value.phone_number?.trim() || '',
      address: editForm.value.address?.trim() || '',
      nik: editForm.value.nik?.trim() || '',
      date_of_birth: editForm.value.date_of_birth || '',
      place_of_birth: editForm.value.place_of_birth?.trim() || '',
      gender: editForm.value.gender ? (editForm.value.gender === 'M' ? 'laki-laki' : 'perempuan') : '',
      marital_status: editForm.value.marital_status || '',
      religion: editForm.value.religion || '',
      ethnicity: editForm.value.ethnicity?.trim() || '',
      blood_type: editForm.value.blood_type || '',
      emergency_contact_name: editForm.value.emergency_contact_name?.trim() || '',
      emergency_contact_phone: editForm.value.emergency_contact_phone?.trim() || ''
    }

    console.log('Sending data to API:', dataToSend)
    const response = await pasienAPI.update(authStore.user.id, dataToSend)
    console.log('API Response:', response.data)
    
    // Update local profile and auth store with response data
    if (response.data.success || response.status === 200) {
      const responseData = response.data.data || response.data
      const userData = {
        name: responseData.name || editForm.value.name,
        email: responseData.email || editForm.value.email,
        phone_number: responseData.phone_number || responseData.no_telepon || editForm.value.phone_number,
        address: responseData.address || responseData.alamat || editForm.value.address,
        nik: responseData.nik || editForm.value.nik,
        date_of_birth: responseData.date_of_birth || responseData.tgl_lahir || editForm.value.date_of_birth,
        place_of_birth: responseData.place_of_birth || editForm.value.place_of_birth,
        gender: responseData.gender ? (responseData.gender === 'laki-laki' ? 'M' : 'F') : (responseData.jenis_kelamin ? (responseData.jenis_kelamin === 'laki-laki' ? 'M' : 'F') : editForm.value.gender),
        marital_status: responseData.marital_status || editForm.value.marital_status,
        religion: responseData.religion || editForm.value.religion,
        ethnicity: responseData.ethnicity || editForm.value.ethnicity,
        blood_type: responseData.blood_type || responseData.golongan_darah || editForm.value.blood_type,
        emergency_contact_name: responseData.emergency_contact_name || editForm.value.emergency_contact_name,
        emergency_contact_phone: responseData.emergency_contact_phone || editForm.value.emergency_contact_phone
      }
      
      console.log('Updated user data:', userData)
      profile.value = { ...profile.value, ...userData }
      authStore.user = { ...authStore.user, ...userData }
      
      editSuccess.value = 'Profil berhasil diperbarui!'
      showEditForm.value = false
      
      // Auto-dismiss success message
      setTimeout(() => {
        editSuccess.value = null
      }, 3000)
    } else {
      throw new Error('Respons server tidak valid')
    }
  } catch (error) {
    console.error('Update error:', error)
    const errorMessage = error.message || error.response?.data?.pesan || error.response?.data?.message || 'Gagal menyimpan profil'
    
    // Handle validation errors from API
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      editError.value = Object.values(errors).flat().join(', ')
    } else {
      editError.value = errorMessage
    }
  } finally {
    editLoading.value = false
  }
}

const closeEditForm = () => {
  showEditForm.value = false
  // Reset form ke data yang sebelumnya disimpan
  editForm.value = {
    name: profile.value.name || '',
    email: profile.value.email || '',
    phone_number: profile.value.phone_number || '',
    address: profile.value.address || '',
    nik: profile.value.nik || '',
    date_of_birth: profile.value.date_of_birth || '',
    place_of_birth: profile.value.place_of_birth || '',
    gender: profile.value.gender || '',
    marital_status: profile.value.marital_status || '',
    religion: profile.value.religion || '',
    ethnicity: profile.value.ethnicity || '',
    blood_type: profile.value.blood_type || '',
    emergency_contact_name: profile.value.emergency_contact_name || '',
    emergency_contact_phone: profile.value.emergency_contact_phone || ''
  }
  editError.value = null
}

const getAge = () => {
  if (!profile.value.date_of_birth) return '-'
  const birth = new Date(profile.value.date_of_birth)
  const today = new Date()
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  return age
}

const formatMaritalStatus = (status) => {
  const statusMap = {
    'single': 'Belum Menikah',
    'married': 'Menikah',
    'divorced': 'Bercerai',
    'widowed': 'Duda/Janda'
  }
  return statusMap[status] || status
}
</script>
