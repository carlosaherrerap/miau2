import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    rolCodigo: (state) => state.user?.rol?.cod || '',
    isSAS: (state) => state.user?.rol?.cod === 'SAS',
    isMonitor: (state) => state.user?.rol?.cod === 'MI',
    isEspecialista: (state) => state.user?.rol?.cod === 'EMI',
    isCoordinador: (state) => state.user?.rol?.cod === 'CSMI',
    isCalidad: (state) => state.user?.rol?.cod === 'ECC',
    isGuest: (state) => state.user?.rol?.cod === 'GUEST',
    nombreCompleto: (state) => state.user?.nombre_completo || state.user?.username || '',
    rolNombre: (state) => state.user?.rol?.nombre || ''
  },

  actions: {
    async login(username, clave) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/auth/login', { username, clave })
        if (response.data.success) {
          const { token, user } = response.data.data
          this.token = token
          this.user = user
          localStorage.setItem('auth_token', token)
          localStorage.setItem('auth_user', JSON.stringify(user))
          return { success: true }
        } else {
          this.error = response.data.message
          return { success: false, message: response.data.message }
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Error al iniciar sesión'
        return { success: false, message: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchUser() {
      if (!this.token) return
      try {
        const response = await api.get('/auth/me')
        if (response.data.success) {
          this.user = response.data.data
          localStorage.setItem('auth_user', JSON.stringify(this.user))
        }
      } catch (err) {
        this.logout()
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } catch (err) {
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    }
  }
})
