import client from './client'

export const adminAPI = {
  getDashboard() {
    return client.get('/admin/dashboard')
  },

  getUsers(params = {}) {
    return client.get('/admin/pengguna', { params })
  },

  getUserDetail(id) {
    return client.get(`/admin/pengguna/${id}`)
  },

  updateUser(id, data) {
    return client.put(`/admin/pengguna/${id}`, data)
  },

  deactivateUser(id) {
    return client.put(`/admin/pengguna/${id}/nonaktif`)
  },

  activateUser(id) {
    return client.put(`/admin/pengguna/${id}/aktif`)
  },

  deleteUser(id) {
    return client.delete(`/admin/pengguna/${id}`)
  },

  getLogs(params = {}) {
    return client.get('/admin/log-aktivitas', { params })
  },

  getStatistik() {
    return client.get('/admin/statistik')
  }
}
