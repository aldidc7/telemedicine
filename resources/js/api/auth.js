import client from './client'

export const authApi = {
  register(data) {
    return client.post('/auth/register', data)
  },

  login(identifier, password) {
    return client.post('/auth/login', { identifier, password })
  },

  getProfile() {
    return client.get('/auth/me')
  },

  refreshToken() {
    return client.post('/auth/refresh')
  },

  logout() {
    return client.post('/auth/logout')
  }
}