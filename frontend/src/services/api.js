import axios from 'axios'

const getBaseURL = () => {
  let url = (import.meta.env.VITE_API_BASE_URL || '/api').trim().replace(/\/+$/, '')
  if (url.startsWith('http') && !url.endsWith('/api')) {
    url += '/api'
  }
  return url
}

const api = axios.create({
  baseURL: getBaseURL(),
  headers: {
    'Accept': 'application/json'
  }
})

api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
}, error => {
  return Promise.reject(error)
})

api.interceptors.response.use(response => {
  return response
}, error => {
  if (error.response && error.response.status === 401) {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    if (window.location.pathname !== '/login') {
      window.location.href = '/login'
    }
  }
  return Promise.reject(error)
})

export default api
