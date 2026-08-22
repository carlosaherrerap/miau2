<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon-box">
          <Ticket :size="28" class="icon-svg" />
        </div>
        <h1 class="system-title">MIAU 2026</h1>
      </div>

      <div v-if="auth.error" class="alert alert-danger">
        {{ auth.error }}
      </div>

      <form @submit.prevent="handleSubmit" class="login-form">
        <div class="form-group">
          <label class="form-label" for="username">Usuario o Código:</label>
          <div class="input-with-icon">
            <User :size="16" class="input-icon" />
            <input
              id="username"
              v-model="username"
              type="text"
              class="form-control with-icon"
              placeholder="Ej: sas043, monitor1, especialista"
              required
              autofocus
            />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="clave">Contraseña:</label>
          <div class="input-with-icon">
            <Lock :size="16" class="input-icon" />
            <input
              id="clave"
              v-model="clave"
              type="password"
              class="form-control with-icon"
              placeholder="Ingrese su contraseña"
              required
            />
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" :disabled="auth.loading">
          <LogIn :size="16" />
          <span>{{ auth.loading ? 'Verificando...' : 'Ingresar al Sistema' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Ticket, User, Lock, LogIn } from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const username = ref('')
const clave = ref('')

const handleSubmit = async () => {
  const res = await auth.login(username.value, clave.value)
  if (res.success) {
    if (auth.isSAS) {
      router.push('/sas/registrar-ticket')
    } else if (auth.isMonitor) {
      router.push('/monitor/dashboard')
    } else {
      router.push('/modulo-gerencial')
    }
  }
}
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #eef2f6;
  padding: 20px;
}

.login-card {
  width: 100%;
  max-width: 440px;
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 32px;
  animation: loginCardEntry 0.3s ease-out;
}

.login-header {
  text-align: center;
  margin-bottom: 24px;
}

.login-icon-box {
  width: 48px;
  height: 48px;
  background-color: #1e3a8a;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.icon-svg {
  color: #93c5fd;
}

.system-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
}

.system-subtitle {
  font-size: 0.8125rem;
  color: #64748b;
  margin-top: 4px;
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 10px;
  color: #64748b;
  pointer-events: none;
}

.form-control.with-icon {
  padding-left: 34px;
}

.btn-block {
  width: 100%;
  padding: 10px;
  margin-top: 8px;
}
</style>
