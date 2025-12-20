<template>
  <div class="payment-success-container">
    <!-- Success Card -->
    <div class="success-card">
      <!-- Success Icon -->
      <div class="success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>

      <!-- Success Message -->
      <h1>Pembayaran Berhasil! 🎉</h1>
      <p>Terima kasih atas pembayaran Anda</p>

      <!-- Transaction Details -->
      <div class="transaction-details">
        <div class="detail-item">
          <span class="label">ID Transaksi:</span>
          <span class="value">{{ paymentId }}</span>
        </div>
        <div class="detail-item">
          <span class="label">Jumlah:</span>
          <span class="value">Rp{{ formatCurrency(amount) }}</span>
        </div>
        <div class="detail-item">
          <span class="label">Tanggal:</span>
          <span class="value">{{ formatDate(paidAt) }}</span>
        </div>
        <div class="detail-item">
          <span class="label">Status:</span>
          <span class="status-badge success">✓ Pembayaran Diterima</span>
        </div>
      </div>

      <!-- Next Steps -->
      <div class="next-steps">
        <h3>Langkah Selanjutnya:</h3>
        <ol>
          <li>Kurir akan menghubungi Anda dalam 5 menit</li>
          <li>Siapkan semua dokumen yang diperlukan</li>
          <li>Konsultasi dimulai sesuai jadwal yang telah ditetapkan</li>
          <li>Invoice dan bukti pembayaran telah dikirim ke email Anda</li>
        </ol>
      </div>

      <!-- Actions -->
      <div class="action-buttons">
        <button @click="viewInvoice" class="btn btn-primary">
          📄 Lihat Invoice
        </button>
        <button @click="backToConsultations" class="btn btn-secondary">
          ← Kembali ke Konsultasi
        </button>
      </div>

      <!-- Download Options -->
      <div class="download-options">
        <p>Unduh dokumen pembayaran:</p>
        <div class="download-buttons">
          <button @click="downloadInvoice" class="download-btn">
            📄 Invoice PDF
          </button>
          <button @click="downloadReceipt" class="download-btn">
            🧾 Receipt
          </button>
        </div>
      </div>
    </div>

    <!-- Support Section -->
    <div class="support-section">
      <h3>Butuh Bantuan?</h3>
      <p>Hubungi customer support kami di:</p>
      <ul>
        <li>📞 <a href="tel:+62xxxxxxx">+62 XXX-XXXX-XXX</a></li>
        <li>💬 <a href="https://wa.me/62xxxxxxx">WhatsApp</a></li>
        <li>📧 <a href="mailto:support@telemedicine.id">support@telemedicine.id</a></li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const paymentId = ref('')
const amount = ref(0)
const paidAt = ref(new Date())
const consultationId = ref(null)
const isLoading = ref(true)

onMounted(async () => {
  try {
    // Get payment details from route params or API
    const id = route.params.id || route.query.payment_id
    if (id) {
      paymentId.value = id
    }

    // Fetch payment details
    const response = await fetch(`/api/v1/payments/${id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      },
    })

    const data = await response.json()
    if (data.data) {
      amount.value = data.data.amount
      paidAt.value = new Date(data.data.paid_at)
      consultationId.value = data.data.consultation_id
    }
  } catch (error) {
    console.error('Error fetching payment details:', error)
  } finally {
    isLoading.value = false
  }
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const viewInvoice = () => {
  window.open(`/api/v1/invoices/${paymentId.value}/download`, '_blank')
}

const downloadInvoice = () => {
  window.location.href = `/api/v1/invoices/${paymentId.value}/download`
}

const downloadReceipt = () => {
  // Can implement receipt download separately
  alert('Fitur unduh receipt akan segera tersedia')
}

const backToConsultations = () => {
  router.push('/consultations')
}
</script>

<style scoped>
.payment-success-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.success-card {
  background: white;
  border-radius: 16px;
  padding: 40px 30px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #4caf50;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  color: white;
}

.success-icon svg {
  width: 48px;
  height: 48px;
}

.success-card h1 {
  font-size: 28px;
  color: #333;
  margin: 20px 0 8px 0;
}

.success-card > p {
  color: #666;
  margin: 0 0 30px 0;
  font-size: 16px;
}

.transaction-details {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 30px;
  text-align: left;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #e0e0e0;
}

.detail-item:last-child {
  border-bottom: none;
}

.label {
  color: #666;
  font-size: 14px;
}

.value {
  color: #333;
  font-weight: 600;
  font-size: 14px;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.success {
  background: #c8e6c9;
  color: #2e7d32;
}

.next-steps {
  background: #e3f2fd;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 30px;
  text-align: left;
}

.next-steps h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #333;
  font-weight: 600;
}

.next-steps ol {
  margin: 0;
  padding-left: 20px;
  color: #666;
  font-size: 13px;
}

.next-steps li {
  margin-bottom: 6px;
}

.action-buttons {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 14px;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5568d3;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #f0f0f0;
  color: #333;
}

.btn-secondary:hover {
  background: #e0e0e0;
}

.download-options {
  background: #fff3cd;
  border: 1px solid #ffe69c;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 30px;
}

.download-options p {
  margin: 0 0 10px 0;
  color: #856404;
  font-size: 14px;
  font-weight: 500;
}

.download-buttons {
  display: flex;
  gap: 10px;
}

.download-btn {
  flex: 1;
  padding: 8px 12px;
  background: white;
  border: 1px solid #ffc107;
  border-radius: 6px;
  color: #856404;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.download-btn:hover {
  background: #ffc107;
  color: white;
}

.support-section {
  background: white;
  border-radius: 12px;
  padding: 30px;
  text-align: center;
  max-width: 500px;
  width: 100%;
  margin-top: 20px;
}

.support-section h3 {
  font-size: 16px;
  color: #333;
  margin: 0 0 8px 0;
}

.support-section p {
  color: #666;
  font-size: 14px;
  margin: 0 0 15px 0;
}

.support-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.support-section li {
  margin: 8px 0;
  font-size: 14px;
}

.support-section a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.support-section a:hover {
  text-decoration: underline;
}

@media (max-width: 640px) {
  .payment-success-container {
    padding: 10px;
  }

  .success-card {
    padding: 30px 20px;
  }

  .action-buttons {
    flex-direction: column;
  }

  .download-buttons {
    flex-direction: column;
  }
}
</style>
