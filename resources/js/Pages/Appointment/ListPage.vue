<template>
  <div class="appointment-list-page">
    <AppointmentList />
  </div>
</template>

<script setup>
import AppointmentList from '@/components/Appointment/AppointmentList.vue'
</script>

<style scoped>
.appointment-list-page {
  @apply min-h-screen bg-gray-50;
}
</style>
