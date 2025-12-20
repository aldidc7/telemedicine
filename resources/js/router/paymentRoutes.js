/**
 * Payment Routes
 * Routes untuk Payment, Invoice, dan terkait payment system
 */

export const paymentRoutes = [
  {
    path: '/payment',
    component: () => import('@/pages/Payment/PaymentPage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Pembayaran Konsultasi',
      breadcrumb: 'Pembayaran',
    },
  },
  {
    path: '/payment/:id',
    component: () => import('@/pages/Payment/PaymentDetailsPage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Detail Pembayaran',
      breadcrumb: 'Detail Pembayaran',
    },
  },
  {
    path: '/invoices',
    component: () => import('@/pages/Invoice/InvoiceListPage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Daftar Invoice',
      breadcrumb: 'Invoice',
    },
  },
  {
    path: '/invoices/:id',
    component: () => import('@/pages/Invoice/InvoicePage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Invoice',
      breadcrumb: 'Invoice',
    },
  },
  {
    path: '/payment-history',
    component: () => import('@/pages/Payment/PaymentHistoryPage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Riwayat Pembayaran',
      breadcrumb: 'Riwayat Pembayaran',
    },
  },
  {
    path: '/payment-success',
    component: () => import('@/pages/Payment/PaymentSuccessPage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Pembayaran Berhasil',
      breadcrumb: 'Sukses',
    },
  },
  {
    path: '/payment-failed',
    component: () => import('@/pages/Payment/PaymentFailedPage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Pembayaran Gagal',
      breadcrumb: 'Gagal',
    },
  },
]

export default paymentRoutes
