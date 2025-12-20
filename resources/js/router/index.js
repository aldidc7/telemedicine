import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomePage.vue')
  },

  // ===== AUTH ROUTES =====
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginPage.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterChoosePage.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/register/pasien',
    name: 'register-pasien',
    component: () => import('@/views/auth/RegisterPage.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/register/dokter',
    name: 'register-dokter',
    component: () => import('@/views/auth/RegisterPage.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: () => import('@/views/auth/VerifyEmailPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/auth/ForgotPasswordPage.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@/views/auth/ResetPasswordPage.vue'),
    meta: { requiresGuest: true }
  },

  // ===== PASIEN ROUTES =====
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/pasien/DashboardPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/cari-dokter',
    name: 'cari-dokter',
    component: () => import('@/views/pasien/CariDokterPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/konsultasi',
    name: 'konsultasi',
    component: () => import('@/views/pasien/KonsultasiPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/konsultasi/:id',
    name: 'konsultasi-detail',
    component: () => import('@/views/pasien/KonsultasiDetailPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/chat/:konsultasiId',
    name: 'chat',
    component: () => import('@/views/pasien/ChatPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/pasien/ProfilePage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/views/pasien/SettingsPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/sessions',
    name: 'sessions',
    component: () => import('@/views/settings/SessionManagementPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/riwayat-konsultasi',
    name: 'riwayat-konsultasi',
    component: () => import('@/views/pasien/RiwayatKonsultasiPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/rating-review',
    name: 'rating-review',
    component: () => import('@/views/pasien/RatingReviewPage.vue'),
    meta: { requiresAuth: true }
  },

  // ===== DOKTER ROUTES =====
  {
    path: '/dokter/dashboard',
    name: 'dokter-dashboard',
    component: () => import('@/views/dokter/DashboardPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'dokter' }
  },
  {
    path: '/dokter/konsultasi',
    name: 'dokter-konsultasi',
    component: () => import('@/views/dokter/DaftarKonsultasiPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'dokter' }
  },
  {
    path: '/dokter/konsultasi/:id',
    name: 'dokter-konsultasi-detail',
    component: () => import('@/views/dokter/DetailKonsultasiPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'dokter' }
  },
  {
    path: '/dokter/chat/:konsultasiId',
    name: 'dokter-chat',
    component: () => import('@/views/dokter/ChatPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'dokter' }
  },
  {
    path: '/dokter/profile',
    name: 'dokter-profile',
    component: () => import('@/views/dokter/ProfilePage.vue'),
    meta: { requiresAuth: true, requiresRole: 'dokter' }
  },
  {
    path: '/dokter/settings',
    name: 'dokter-settings',
    component: () => import('@/views/dokter/SettingsPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'dokter' }
  },

  // ===== ADMIN ROUTES =====
  {
    path: '/admin/dashboard',
    name: 'admin-dashboard',
    component: () => import('@/views/admin/DashboardPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/pasien',
    name: 'admin-pasien',
    component: () => import('@/views/admin/ManagePasienPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/pasien/:id',
    name: 'admin-pasien-profile',
    component: () => import('@/views/admin/PasienProfilePage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/pasien/:id/edit',
    name: 'admin-pasien-edit',
    component: () => import('@/views/admin/PasienEditPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/dokter',
    name: 'admin-dokter',
    component: () => import('@/views/admin/ManageDokterPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/dokter/:id',
    name: 'admin-dokter-profile',
    component: () => import('@/views/admin/DokterProfilePage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/dokter/:id/edit',
    name: 'admin-dokter-edit',
    component: () => import('@/views/admin/DokterEditPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/log',
    name: 'admin-log',
    component: () => import('@/views/admin/LogAktivitasPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/statistik',
    name: 'admin-statistik',
    component: () => import('@/views/admin/StatistikPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/analytics',
    name: 'admin-analytics',
    component: () => import('@/views/admin/AnalyticsPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/settings',
    name: 'admin-settings',
    component: () => import('@/views/admin/SettingsPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },

  // ===== SUPERADMIN ROUTES =====
  {
    path: '/superadmin/system-logs',
    name: 'superadmin-system-logs',
    component: () => import('@/views/superadmin/SystemLogsPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'superadmin' }
  },
  {
    path: '/superadmin/manage-users',
    name: 'superadmin-manage-users',
    component: () => import('@/views/superadmin/ManageUserPage.vue'),
    meta: { requiresAuth: true, requiresRole: 'superadmin' }
  },

  // ===== DEBUG ROUTES =====
  {
    path: '/diagnostic',
    name: 'diagnostic',
    component: () => import('@/views/DiagnosticPage.vue')
  },
  {
    path: '/debug/konsultasi',
    name: 'debug-konsultasi',
    component: () => import('@/views/DebugKonsultasiPage.vue'),
    meta: { requiresAuth: true }
  },

  // ===== ERROR ROUTES =====
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundPage.vue')
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Flag untuk track apakah sudah initialize
let authInitialized = false

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Initialize auth hanya sekali saat app first load
  if (!authInitialized) {
    authInitialized = true
    try {
      // Tunggu auth store initialization selesai
      if (!authStore.initialized) {
        await authStore.initializeAuth()
      }
    } catch (error) {
      // Silent error, just mark as initialized
      authStore.initialized = true
    }
  } else {
    // Jika masih belum initialized dan route butuh auth/guest, tunggu dengan timeout
    if ((to.meta.requiresAuth || to.meta.requiresRole || to.meta.requiresGuest) && !authStore.initialized) {
      let waitCount = 0
      while (!authStore.initialized && waitCount < 50) {
        await new Promise(resolve => setTimeout(resolve, 100))
        waitCount++
      }
    }
  }

  // Check authorization
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return next('/dashboard')
  } else if (to.meta.requiresRole && authStore.userRole !== to.meta.requiresRole) {
    return next('/dashboard')
  } else {
    return next()
  }
})

export default router