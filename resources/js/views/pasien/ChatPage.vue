<!-- 📁 resources/js/views/pasien/ChatPage.vue -->
<template>
  <div class="bg-linear-to-br from-slate-50 to-white h-screen flex flex-col rounded-2xl overflow-hidden shadow-lg">
    <!-- Header with Gradient -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white p-6 shadow-md">
      <h2 class="text-2xl font-bold mb-1">Dr. {{ konsultasi.dokter?.name }}</h2>
      <p class="text-indigo-100 text-sm">{{ konsultasi.jenis_keluhan }}</p>
    </div>

    <!-- Messages Container -->
    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
      <!-- Empty State -->
      <div v-if="pesan.length === 0" class="flex items-center justify-center h-full">
        <div class="text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <p class="text-gray-500">Tidak ada pesan. Mulai percakapan Anda sekarang!</p>
        </div>
      </div>

      <!-- Messages -->
      <div
        v-for="msg in pesan"
        :key="msg.id"
        :class="['flex', msg.pengirim_id === authStore.user?.id ? 'justify-end' : 'justify-start']"
      >
        <div
          :class="[
            'max-w-md px-4 py-3 rounded-2xl text-sm',
            msg.pengirim_id === authStore.user?.id
              ? 'bg-linear-to-r from-indigo-500 to-purple-600 text-white rounded-br-none'
              : 'bg-gray-100 text-gray-900 rounded-bl-none'
          ]"
        >
          <p class="leading-relaxed">{{ msg.pesan }}</p>
          <p :class="['text-xs mt-2', msg.pengirim_id === authStore.user?.id ? 'text-indigo-100' : 'text-gray-500']">
            {{ new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Input Area -->
    <div class="border-t border-gray-200 bg-white p-4">
      <div class="flex gap-3">
        <input
          v-model="messageBaru"
          @keyup.enter="kirimPesan"
          type="text"
          placeholder="Ketik pesan Anda..."
          class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
        />
        <button
          @click="kirimPesan"
          :disabled="!messageBaru.trim()"
          class="bg-linear-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
          Kirim
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { konsultasiAPI } from '@/api/konsultasi'
import { pesanAPI } from '@/api/pesan'

const route = useRoute()
const authStore = useAuthStore()

const konsultasi = ref({})
const pesan = ref([])
const messageBaru = ref('')
const messagesContainer = ref(null)

onMounted(() => {
  loadData()
  // Polling setiap 2 detik
  setInterval(loadData, 2000)
})

watch(pesan, () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
})

const loadData = async () => {
  try {
    const konsultasiRes = await konsultasiAPI.getDetail(route.params.konsultasiId)
    konsultasi.value = konsultasiRes.data.data

    const pesanRes = await pesanAPI.getList(route.params.konsultasiId)
    pesan.value = pesanRes.data.data
  } catch (error) {
    console.error('Error loading data:', error)
  }
}

const kirimPesan = async () => {
  if (!messageBaru.value.trim()) return

  try {
    await pesanAPI.create({
      konsultasi_id: route.params.konsultasiId,
      pesan: messageBaru.value,
      tipe_pesan: 'text'
    })
    messageBaru.value = ''
    await loadData()
  } catch (error) {
    console.error('Error sending message:', error)
  }
}
</script>
