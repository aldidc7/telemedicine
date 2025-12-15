export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

export const USER_ROLES = {
  PASIEN: 'pasien',
  DOKTER: 'dokter',
  ADMIN: 'admin'
}

export const CONSULTATION_STATUS = {
  MENUNGGU: 'menunggu',
  AKTIF: 'aktif',
  SELESAI: 'selesai',
  DIBATALKAN: 'dibatalkan'
}

export const MESSAGE_TYPES = {
  TEXT: 'text',
  IMAGE: 'image',
  FILE: 'file',
  AUDIO: 'audio'
}

export const BLOOD_TYPES = ['A', 'B', 'AB', 'O']

export const GENDERS = ['Laki-laki', 'Perempuan']

export const SPECIALIZATION = [
  'Dokter Umum',
  'Dokter Anak',
  'Dokter Kandungan',
  'Dokter Gigi',
  'Dokter Mata',
  'Dokter Jantung',
  'Dokter Saraf',
  'Dokter Kulit'
]