import client from './client'

export const pesanAPI = {
  getList(konsultasiId, params = {}) {
    return client.get(`/pesan/${konsultasiId}`, { params })
  },

  create(data) {
    return client.post('/pesan', data)
  },

  getDetail(id) {
    return client.get(`/pesan/${id}`)
  },

  markAsDibaca(id) {
    return client.put(`/pesan/${id}/dibaca`)
  },

  getUnreadCount(konsultasiId) {
    return client.get(`/pesan/${konsultasiId}/unread-count`)
  },

  delete(id) {
    return client.delete(`/pesan/${id}`)
  },

  markAllAsDibaca(konsultasiId) {
    return client.put(`/pesan/${konsultasiId}/mark-all-read`)
  }
}
