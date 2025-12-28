<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Nama Dokter</th>
            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Spesialis</th>
            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Konsultasi</th>
            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Rating Rata-rata</th>
            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Waktu Respon</th>
            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Penyelesaian</th>
            <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="doctor in doctors" :key="doctor.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <div>
                <p class="font-bold text-gray-900">{{ doctor.name }}</p>
                <p class="text-sm text-gray-600">{{ doctor.email }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-600">{{ doctor.specialist }}</td>
            <td class="px-6 py-4 font-bold text-indigo-600">{{ doctor.total_consultations }}</td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-1">
                <span class="font-bold text-yellow-600">{{ doctor.avg_rating }}</span>
                <span class="text-sm text-gray-600">{{ doctor.rating_count }} penilaian</span>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-600">{{ doctor.avg_response_time_minutes }}m</td>
            <td class="px-6 py-4">
              <div class="inline-block">
                <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-green-500 rounded-full" 
                    :style="`width: ${doctor.completion_rate}%`"
                  ></div>
                </div>
                <p class="text-xs text-gray-600 mt-1">{{ doctor.completion_rate }}%</p>
              </div>
            </td>
            <td class="px-6 py-4">
              <span :class="[
                'px-3 py-1 rounded-full text-sm font-bold',
                doctor.status === 'Available' 
                  ? 'bg-green-100 text-green-800' 
                  : 'bg-red-100 text-red-800'
              ]">
                {{ doctor.status === 'Available' ? 'Tersedia' : 'Tidak Tersedia' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  doctors: {
    type: Array,
    required: true
  }
})
</script>
