<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-3xl font-bold text-gray-900">Manajemen User</h1>
        <p class="text-gray-600 mt-1">Kelola semua pengguna sistem: Admin, Dokter, Pasien</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Cari nama, email, nomor identitas..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
            <div v-if="searching" class="absolute right-3 top-2.5">
              <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
            </div>
          </div>

          <!-- Filter by Role -->
          <select
            v-model="filterRole"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Semua Role</option>
            <option value="superadmin">Superadmin</option>
            <option value="admin">Admin</option>
            <option value="dokter">Dokter</option>
            <option value="pasien">Pasien</option>
          </select>

          <!-- Filter by Status -->
          <select
            v-model="filterStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>

        <!-- Clear Filters Button -->
        <div class="mt-3">
          <button
            @click="clearFilters"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition text-sm font-semibold"
          >
            Reset Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Error Alert -->
      <ErrorAlert v-if="errorMessage" :message="errorMessage" />
      <SuccessAlert v-if="successMessage" :message="successMessage" />

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <LoadingSpinner />
      </div>

      <!-- Users Table -->
      <div v-else-if="users.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">User</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Bergabung</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-sm">
                  <div class="flex flex-col gap-1">
                    <span class="font-semibold text-gray-900">{{ user.name }}</span>
                    <span v-if="user.nomor_identitas" class="text-xs text-gray-500 font-mono">ID: {{ user.nomor_identitas }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
                <td class="px-6 py-4 text-sm">
                  <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getRoleBadgeColor(user.role)]">
                    {{ user.role.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <button
                    @click="toggleUserStatus(user)"
                    :disabled="togglingStatus"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition"
                    :class="user.is_active 
                      ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                      : 'bg-red-100 text-red-800 hover:bg-red-200'"
                  >
                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                  </button>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(user.created_at) }}</td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex gap-2 flex-wrap">
                    <button
                      @click="viewUser(user)"
                      class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-lg hover:bg-indigo-200 transition text-xs font-semibold"
                    >
                      Lihat
                    </button>
                    <button
                      @click="editUser(user)"
                      class="px-3 py-1 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition text-xs font-semibold"
                    >
                      Edit
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-white rounded-lg">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
        <p class="text-gray-500 text-lg font-semibold">Tidak ada user ditemukan</p>
      </div>

      <!-- Pagination -->
      <div v-if="users.length > 0" class="mt-8 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="text-gray-600 text-sm">
          Halaman <span class="font-semibold">{{ currentPage }}</span> dari <span class="font-semibold">{{ totalPages }}</span>
        </div>
        <div class="flex gap-2">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            ← Sebelumnya
          </button>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            Selanjutnya →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { adminAPI } from '@/api/admin'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import SuccessAlert from '@/components/SuccessAlert.vue'
import ErrorHandler from '@/utils/ErrorHandler'

const users = ref([])
const loading = ref(false)
const togglingStatus = ref(false)
const errorMessage = ref(null)
const successMessage = ref(null)
const searching = ref(false)
const search = ref('')
const filterRole = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

/**
 * Load users
 */
const loadUsers = async () => {
  loading.value = true
  errorMessage.value = null
  searching.value = true

  try {
    const response = await adminAPI.getUsers({
      page: currentPage.value,
      per_page: 25,
      search: search.value || undefined,
      role: filterRole.value || undefined,
      status: filterStatus.value || undefined,
    })

    users.value = response.data.data
    currentPage.value = response.data.current_page
    totalPages.value = response.data.last_page
  } catch (error) {
    errorMessage.value = ErrorHandler.getUserMessage(error)
  } finally {
    loading.value = false
    searching.value = false
  }
}

/**
 * Format date
 */
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

/**
 * Get role badge color
 */
const getRoleBadgeColor = (role) => {
  const colors = {
    superadmin: 'bg-red-100 text-red-800',
    admin: 'bg-orange-100 text-orange-800',
    dokter: 'bg-indigo-100 text-indigo-800',
    pasien: 'bg-green-100 text-green-800',
  }
  return colors[role] || 'bg-gray-100 text-gray-800'
}

/**
 * Toggle user status
 */
const toggleUserStatus = async (user) => {
  if (!confirm(`Yakin ingin mengubah status user ini?`)) return

  togglingStatus.value = true
  errorMessage.value = null

  try {
    await adminAPI.updateUserStatus(user.id, { is_active: !user.is_active })
    successMessage.value = `Status user berhasil diubah`
    user.is_active = !user.is_active
  } catch (error) {
    errorMessage.value = ErrorHandler.getUserMessage(error)
  } finally {
    togglingStatus.value = false
  }
}

/**
 * View user details
 */
const viewUser = (user) => {
  // Navigate based on role
  const routes = {
    dokter: `/admin/dokter/${user.dokter_id}`,
    pasien: `/admin/pasien/${user.pasien_id}`,
  }
  
  if (routes[user.role]) {
    window.location.href = routes[user.role]
  }
}

/**
 * Edit user
 */
const editUser = (user) => {
  const routes = {
    dokter: `/admin/dokter/${user.dokter_id}/edit`,
    pasien: `/admin/pasien/${user.pasien_id}/edit`,
  }
  
  if (routes[user.role]) {
    window.location.href = routes[user.role]
  }
}

/**
 * Clear filters
 */
const clearFilters = async () => {
  search.value = ''
  filterRole.value = ''
  filterStatus.value = ''
  currentPage.value = 1
  await loadUsers()
}

/**
 * Pagination
 */
const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    await loadUsers()
  }
}

const prevPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--
    await loadUsers()
  }
}

/**
 * Debounce search
 */
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

const debouncedSearch = debounce(async () => {
  currentPage.value = 1
  await loadUsers()
}, 500)

// Watchers
watch(search, () => { debouncedSearch() })
watch(filterRole, async () => { currentPage.value = 1; await loadUsers() })
watch(filterStatus, async () => { currentPage.value = 1; await loadUsers() })

onMounted(() => {
  loadUsers()
})
</script>
