<!-- 📁 resources/js/views/dokter/ChatPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 to-slate-100">
    <div class="max-w-4xl mx-auto h-screen flex flex-col">
      <!-- Header -->
      <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white shadow-lg px-6 py-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
          </div>
          <div>
            <h1 class="text-xl font-bold">{{ konsultasi.pasien?.user?.name || 'Loading...' }}</h1>
            <p class="text-indigo-100 text-sm">{{ konsultasi.complaint_type || konsultasi.jenis_keluhan }}</p>
          </div>
        </div>
        <router-link to="/dokter/dashboard" class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg font-semibold transition text-sm">
          ← Kembali
        </router-link>
      </div>

      <!-- Messages Container -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto px-6 py-6 space-y-4">
        <div v-if="pesan.length === 0" class="flex items-center justify-center h-full">
          <div class="text-center">
            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <p class="text-gray-500">Mulai percakapan dengan pasien</p>
          </div>
        </div>

        <div v-for="msg in pesan" :key="msg.id" :class="['flex', msg.pengirim_id === authStore.user?.id ? 'justify-end' : 'justify-start']">
          <div :class="[
            'max-w-sm px-5 py-3 rounded-2xl shadow-sm',
            msg.pengirim_id === authStore.user?.id
              ? 'bg-linear-to-r from-indigo-500 to-purple-600 text-white rounded-br-none'
              : 'bg-white text-gray-900 rounded-bl-none border border-gray-200'
          ]">
            <p class="text-sm leading-relaxed wrap-break-word">{{ msg.pesan || msg.isi_pesan }}</p>
            <p :class="['text-xs mt-2 font-medium', msg.pengirim_id === authStore.user?.id ? 'text-indigo-100' : 'text-gray-500']">
              {{ new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Input Section -->
      <div class="bg-white border-t border-gray-200 px-6 py-4 shadow-lg">
        <div class="flex gap-3">
          <input
            v-model="messageBaru"
            @keyup.enter="kirimPesan"
            type="text"
            placeholder="Tulis pesan..."
            class="flex-1 px-5 py-3 bg-gray-50 border border-gray-300 rounded-full focus:outline-none focus:border-indigo-500 focus:bg-white transition"
          />
          <button
            @click="kirimPesan"
            :disabled="!messageBaru.trim()"
            class="bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-3 rounded-full font-semibold transition flex items-center gap-2 shadow-md hover:shadow-lg"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Kirim
          </button>
        </div>
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
