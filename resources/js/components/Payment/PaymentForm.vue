<template>
  <div class="payment-form-container">
    <div class="payment-header">
      <h2 class="text-2xl font-bold text-gray-800">Pembayaran Konsultasi</h2>
      <p class="text-gray-600 mt-2">Lengkapi informasi pembayaran Anda dengan aman</p>
    </div>

    <!-- Payment Summary -->
    <div class="payment-summary bg-blue-50 rounded-lg p-6 mb-6 border border-blue-200">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-gray-600">Subtotal</p>
          <p class="text-xl font-semibold text-gray-800">{{ formatCurrency(amount) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Pajak (PPh 15% + PPN 11%)</p>
          <p class="text-xl font-semibold text-blue-600">{{ formatCurrency(taxAmount) }}</p>
        </div>
        <div class="col-span-2 border-t border-blue-200 pt-4 mt-4">
          <p class="text-sm text-gray-600">Total Pembayaran</p>
          <p class="text-3xl font-bold text-blue-700">{{ formatCurrency(amount + taxAmount) }}</p>
        </div>
      </div>
    </div>

    <!-- Payment Form -->
    <form @submit.prevent="submitPayment" class="space-y-6">
      <!-- Consultation Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Konsultasi
          <span class="text-red-500">*</span>
        </label>
        <select
          v-model="form.consultation_id"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          @change="onConsultationChange"
        >
          <option value="">-- Pilih Konsultasi --</option>
          <option v-for="consultation in consultations" :key="consultation.id" :value="consultation.id">
            {{ consultation.patient_name }} - {{ consultation.status }} ({{ formatDate(consultation.created_at) }})
          </option>
        </select>
        <span v-if="errors.consultation_id" class="text-red-500 text-sm mt-1">{{ errors.consultation_id }}</span>
      </div>

      <!-- Amount -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Jumlah Pembayaran (IDR)
          <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <span class="absolute left-4 top-3 text-gray-500">Rp</span>
          <input
            v-model.number="form.amount"
            type="number"
            min="1000"
            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @input="updateTaxAmount"
          />
        </div>
        <p class="text-xs text-gray-500 mt-1">Minimum Rp 1.000</p>
        <span v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</span>
      </div>

      <!-- Payment Method -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
          Metode Pembayaran
          <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-2 gap-3">
          <label
            v-for="method in paymentMethods"
            :key="method.value"
            class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
            :class="
              form.payment_method === method.value
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-300 hover:border-gray-400'
            "
          >
            <input
              v-model="form.payment_method"
              type="radio"
              :value="method.value"
              class="h-4 w-4 text-blue-600"
            />
            <div class="ml-3">
              <p class="font-medium text-gray-900">{{ method.label }}</p>
              <p class="text-xs text-gray-600">{{ method.description }}</p>
            </div>
          </label>
        </div>
        <span v-if="errors.payment_method" class="text-red-500 text-sm mt-1">{{ errors.payment_method }}</span>
      </div>

      <!-- Stripe Card Element (show only for stripe) -->
      <div v-if="form.payment_method === 'stripe'" class="space-y-4">
        <div id="card-element" class="border border-gray-300 rounded-lg p-4"></div>
        <div id="card-errors" class="text-red-500 text-sm"></div>
      </div>

      <!-- Bank Transfer Instructions (show only for bank_transfer) -->
      <div
        v-if="form.payment_method === 'bank_transfer'"
        class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-2"
      >
        <p class="font-semibold text-amber-900">Instruksi Transfer Bank</p>
        <ul class="text-sm text-amber-800 space-y-1">
          <li>✓ Bank BCA 1234567890 a.n PT Telemedicine Indonesia</li>
          <li>✓ Transfer sejumlah: {{ formatCurrency(amount + taxAmount) }}</li>
          <li>✓ Bukti transfer otomatis terverifikasi dalam 5 menit</li>
          <li>✓ Hubungi support jika belum terverifikasi</li>
        </ul>
      </div>

      <!-- Terms & Conditions -->
      <div class="flex items-start space-x-3">
        <input
          v-model="form.agree_terms"
          type="checkbox"
          id="agree_terms"
          class="h-4 w-4 text-blue-600 rounded mt-1"
        />
        <label for="agree_terms" class="text-sm text-gray-700">
          Saya setuju dengan
          <router-link to="/terms" class="text-blue-600 hover:underline">syarat dan ketentuan</router-link>
          dan
          <router-link to="/privacy" class="text-blue-600 hover:underline">kebijakan privasi</router-link>
          <span class="text-red-500">*</span>
        </label>
      </div>
      <span v-if="errors.agree_terms" class="text-red-500 text-sm">{{ errors.agree_terms }}</span>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg transition duration-200"
      >
        <span v-if="!loading">Bayar Sekarang - {{ formatCurrency(amount + taxAmount) }}</span>
        <span v-else class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Memproses...
        </span>
      </button>
    </form>

    <!-- Success Message -->
    <div v-if="successMessage" class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePaymentStore } from '@/stores/paymentStore'
import { formatCurrency, formatDate } from '@/utils/formatters'

const router = useRouter()
const paymentStore = usePaymentStore()

const loading = ref(false)
const successMessage = ref('')
const stripe = ref(null)
const elements = ref(null)
const cardElement = ref(null)

const consultations = ref([])

const form = ref({
  consultation_id: '',
  amount: 0,
  payment_method: 'stripe',
  agree_terms: false,
})

const errors = ref({})

const paymentMethods = [
  { value: 'stripe', label: 'Kartu Kredit', description: 'Visa, Mastercard, Amex' },
  { value: 'gcash', label: 'GCash', description: 'Digital wallet (Philippines)' },
  { value: 'bank_transfer', label: 'Transfer Bank', description: 'Verifikasi otomatis' },
  { value: 'e_wallet', label: 'E-Wallet', description: 'OVO, Dana, LinkAja' },
]

const taxAmount = computed(() => {
  // PPh 15% + PPN 11% = 26% total
  return Math.round(form.value.amount * 0.26)
})

const updateTaxAmount = () => {
  // This will trigger computed property update
}

const onConsultationChange = async () => {
  const selected = consultations.value.find(c => c.id == form.value.consultation_id)
  if (selected) {
    form.value.amount = selected.consultation_fee || 100000
  }
}

const fetchConsultations = async () => {
  try {
    const response = await fetch('/api/v1/consultations/my', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })
    const data = await response.json()
    consultations.value = data.data || []
  } catch (error) {
    console.error('Error fetching consultations:', error)
  }
}

const submitPayment = async () => {
  errors.value = {}
  
  // Validation
  if (!form.value.consultation_id) {
    errors.value.consultation_id = 'Silakan pilih konsultasi'
    return
  }
  if (!form.value.amount || form.value.amount < 1000) {
    errors.value.amount = 'Jumlah minimum Rp 1.000'
    return
  }
  if (!form.value.payment_method) {
    errors.value.payment_method = 'Pilih metode pembayaran'
    return
  }
  if (!form.value.agree_terms) {
    errors.value.agree_terms = 'Anda harus menyetujui syarat dan ketentuan'
    return
  }

  loading.value = true

  try {
    let paymentData = {
      consultation_id: form.value.consultation_id,
      amount: form.value.amount,
      payment_method: form.value.payment_method,
    }

    // For Stripe, get payment intent
    if (form.value.payment_method === 'stripe') {
      const { paymentIntent, error } = await stripe.value.confirmCardPayment(
        paymentData.client_secret,
        {
          payment_method: {
            card: cardElement.value,
            billing_details: { name: 'Customer' }
          }
        }
      )

      if (error) {
        errors.value.payment = error.message
        loading.value = false
        return
      }

      paymentData.transaction_id = paymentIntent.id
      paymentData.receipt_url = paymentIntent.charges.data[0]?.receipt_url
    }

    // Create payment via API
    const response = await fetch('/api/v1/payments', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(paymentData)
    })

    const result = await response.json()

    if (!response.ok) {
      errors.value.payment = result.message || 'Pembayaran gagal'
      loading.value = false
      return
    }

    successMessage.value = 'Pembayaran berhasil! Invoice telah dikirim ke email Anda.'
    
    // Update store
    paymentStore.setLastPayment(result.data)

    // Redirect to invoice after 2 seconds
    setTimeout(() => {
      router.push(`/invoices/${result.data.invoice_id}`)
    }, 2000)

  } catch (error) {
    errors.value.payment = error.message || 'Terjadi kesalahan'
    console.error('Payment error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchConsultations()

  // Initialize Stripe (only if needed)
  if (form.value.payment_method === 'stripe') {
    const { loadStripe } = await import('@stripe/stripe-js')
    stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY)
    elements.value = stripe.value.elements()
    cardElement.value = elements.value.create('card')
    cardElement.value.mount('#card-element')
  }
})
</script>

<style scoped>
.payment-form-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.payment-header {
  margin-bottom: 2rem;
  text-align: center;
}

#card-element {
  background-color: white;
}

#card-errors {
  margin-top: 0.5rem;
}
</style>
