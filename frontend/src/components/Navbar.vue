<template>
  <header class="app-header">
    <div class="header-top">
      <div class="brand">
        <div class="brand-icon-box">
          <Ticket :size="20" class="brand-svg" />
        </div>
        <div>
          <span class="brand-title">MIAU 2026</span>
          <span class="brand-badge" v-if="auth.rolCodigo">{{ auth.rolNombre }}</span>
        </div>
      </div>

      <div class="user-actions">
        <!-- Indicador de nuevos tickets pendientes para el Monitor Informatico -->
        <div v-if="auth.isMonitor" class="pending-indicator-container">
          <button 
            @click="irATicketsAbiertos"
            class="pending-badge-btn"
            :class="{ 'has-pending': pendingCount > 0 }"
            title="Tickets abiertos pendientes de atención"
          >
            <Clock :size="14" />
            <span class="indicator-dot" v-if="pendingCount > 0"></span>
            <span>Pendientes: <strong>{{ pendingCount }}</strong></span>
          </button>
        </div>

        <div class="user-info">
          <span class="user-name">{{ auth.nombreCompleto }}</span>
          <span class="user-sede" v-if="sedeInfo">{{ sedeInfo }}</span>
        </div>

        <button @click="handleLogout" class="btn-logout" title="Cerrar Sesión">
          <LogOut :size="15" />
          <span>Salir</span>
        </button>
      </div>
    </div>

    <!-- Barra de navegacion por pestanas / links segun rol -->
    <nav class="header-nav">
      <!-- Navegacion SAS -->
      <template v-if="auth.isSAS">
        <router-link to="/sas/registrar-ticket" class="nav-item" active-class="active">
          <PlusCircle :size="16" />
          <span>Registrar Ticket</span>
        </router-link>
        <router-link to="/sas/mis-tickets" class="nav-item" active-class="active">
          <ListChecks :size="16" />
          <span>Mis Tickets</span>
        </router-link>
        <router-link to="/sas/incidencias" class="nav-item" active-class="active">
          <AlertCircle :size="16" />
          <span>Avisos e Incidencias</span>
        </router-link>
        <router-link to="/sas/publicaciones" class="nav-item" active-class="active">
          <FileText :size="16" />
          <span>Publicaciones y Credenciales</span>
        </router-link>
      </template>

      <!-- Navegacion Monitor Informatico -->
      <template v-if="auth.isMonitor">
        <router-link to="/monitor/dashboard" class="nav-item" active-class="active">
          <LayoutDashboard :size="16" />
          <span>Inicio</span>
        </router-link>
        <router-link to="/tickets/consulta" class="nav-item" active-class="active">
          <ListFilter :size="16" />
          <span>Tickets</span>
        </router-link>
        <router-link to="/sas/incidencias" class="nav-item" active-class="active">
          <AlertCircle :size="16" />
          <span>Incidencias</span>
        </router-link>
        <router-link to="/modulo-gerencial" class="nav-item" active-class="active">
          <BarChart3 :size="16" />
          <span>Módulo Gerencial</span>
        </router-link>
      </template>

      <!-- Navegacion Especialista de Monitoreo -->
      <template v-if="auth.isEspecialista">
        <router-link to="/modulo-gerencial" class="nav-item" active-class="active">
          <BarChart3 :size="16" />
          <span>Módulo Gerencial</span>
        </router-link>
        <router-link to="/tickets/consulta" class="nav-item" active-class="active">
          <ListFilter :size="16" />
          <span>Tickets</span>
        </router-link>
        <router-link to="/administracion/catalogo" class="nav-item" active-class="active">
          <Layers :size="16" />
          <span>Catálogo</span>
        </router-link>
        <router-link to="/administracion/publicaciones" class="nav-item" active-class="active">
          <FileText :size="16" />
          <span>Publicaciones</span>
        </router-link>
        <router-link to="/sas/incidencias" class="nav-item" active-class="active">
          <AlertCircle :size="16" />
          <span>Incidencias</span>
        </router-link>
        <router-link to="/administracion/usuarios" class="nav-item" active-class="active">
          <Users :size="16" />
          <span>Carga Usuarios</span>
        </router-link>
      </template>

      <!-- Navegacion Coordinador / Calidad / Guest -->
      <template v-if="auth.isCoordinador || auth.isCalidad || auth.isGuest">
        <router-link to="/modulo-gerencial" class="nav-item" active-class="active">
          <BarChart3 :size="16" />
          <span>Módulo Gerencial</span>
        </router-link>
        <router-link to="/tickets/consulta" class="nav-item" active-class="active">
          <ListFilter :size="16" />
          <span>Tickets</span>
        </router-link>
        <router-link to="/administracion/publicaciones" class="nav-item" active-class="active">
          <FileText :size="16" />
          <span>Publicaciones</span>
        </router-link>
        <router-link to="/sas/incidencias" class="nav-item" active-class="active">
          <AlertCircle :size="16" />
          <span>Incidencias</span>
        </router-link>
      </template>
    </nav>
  </header>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import {
  Ticket,
  Clock,
  LogOut,
  PlusCircle,
  ListChecks,
  ListFilter,
  AlertCircle,
  FileText,
  LayoutDashboard,
  BarChart3,
  Layers,
  Users
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const pendingCount = ref(0)
let pollInterval = null

const sedeInfo = computed(() => {
  if (auth.user?.sede_jurisdiccional) {
    return auth.user.sede_jurisdiccional.nombre
  }
  if (auth.user?.sede_regional) {
    return auth.user.sede_regional.nombre
  }
  if (auth.user?.codigo_monitor) {
    return `Monitor: ${auth.user.codigo_monitor}`
  }
  return ''
})

const fetchPending = async () => {
  if (!auth.isMonitor) return
  try {
    const res = await api.get('/tickets/indicador-pendientes')
    if (res.data.success) {
      pendingCount.value = res.data.data.pendientes
    }
  } catch (err) {
    // Silencioso
  }
}

const irATicketsAbiertos = () => {
  router.push({ path: '/tickets/consulta', query: { estado: 'Abierto' } })
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  if (auth.isMonitor) {
    fetchPending()
    pollInterval = setInterval(fetchPending, 15000)
  }
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<style scoped>
.app-header {
  background-color: #1e3a8a;
  border-bottom: 1px solid #172554;
  color: #ffffff;
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-icon-box {
  width: 34px;
  height: 34px;
  background-color: #172554;
  border: 1px solid #3b82f6;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-svg {
  color: #93c5fd;
}

.brand-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: -0.01em;
  display: block;
}

.brand-badge {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 1px 8px;
  background-color: #1e40af;
  color: #dbeafe;
  border: 1px solid #3b82f6;
  border-radius: 4px;
  margin-top: 2px;
}

.user-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.pending-indicator-container {
  display: flex;
  align-items: center;
}

.pending-badge-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background-color: #172554;
  border: 1px solid #3b82f6;
  border-radius: 6px;
  font-size: 0.8125rem;
  color: #dbeafe;
  cursor: pointer;
  transition: all 0.15s ease;
}

.pending-badge-btn.has-pending {
  background-color: #78350f;
  border-color: #f59e0b;
  color: #fef3c7;
}

.indicator-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #f59e0b;
}

.user-info {
  display: flex;
  flex-direction: column;
  text-align: right;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #ffffff;
}

.user-sede {
  font-size: 0.75rem;
  color: #bfdbfe;
}

.btn-logout {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background-color: transparent;
  color: #ffffff;
  border: 1px solid #93c5fd;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-logout:hover {
  background-color: #1e40af;
  border-color: #ffffff;
}

.header-nav {
  display: flex;
  gap: 4px;
  padding: 0 24px;
  overflow-x: auto;
}

.nav-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 14px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #bfdbfe;
  border-bottom: 3px solid transparent;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.nav-item:hover {
  color: #ffffff;
  background-color: rgba(255, 255, 255, 0.06);
  text-decoration: none;
}

.nav-item.active {
  color: #ffffff;
  border-bottom-color: #ffffff;
  font-weight: 700;
  background-color: rgba(255, 255, 255, 0.08);
}
</style>
