<template>
  <div class="appointment-booking-page">
    <AppointmentBooking />
  </div>
</template>

<script setup>
import AppointmentBooking from '@/components/Appointment/AppointmentBooking.vue'
</script>

<style scoped>
.appointment-booking-page {
  @apply min-h-screen bg-gray-50;
}
</style>
