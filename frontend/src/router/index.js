import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import LoginView from '@/views/LoginView.vue'
import RegistroTicketView from '@/views/sas/RegistroTicketView.vue'
import MisTicketsView from '@/views/sas/MisTicketsView.vue'
import IncidenciasInformativasView from '@/views/sas/IncidenciasInformativasView.vue'
import MisPublicacionesView from '@/views/sas/MisPublicacionesView.vue'
import DashboardMonitorView from '@/views/monitor/DashboardMonitorView.vue'
import GestionTicketsView from '@/views/monitor/GestionTicketsView.vue'
import ModuloGerencialView from '@/views/gerencial/ModuloGerencialView.vue'
import AdministracionCatalogoView from '@/views/especialista/AdministracionCatalogoView.vue'
import GestionPublicacionesView from '@/views/especialista/GestionPublicacionesView.vue'
import CargaUsuariosView from '@/views/especialista/CargaUsuariosView.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true }
  },
  {
    path: '/',
    redirect: () => {
      const auth = useAuthStore()
      if (!auth.isAuthenticated) return '/login'
      if (auth.isSAS) return '/sas/registrar-ticket'
      if (auth.isMonitor) return '/monitor/dashboard'
      return '/modulo-gerencial'
    }
  },
  {
    path: '/sas/registrar-ticket',
    name: 'sas-registrar-ticket',
    component: RegistroTicketView,
    meta: { requiresAuth: true, roles: ['SAS'] }
  },
  {
    path: '/sas/mis-tickets',
    name: 'sas-mis-tickets',
    component: MisTicketsView,
    meta: { requiresAuth: true, roles: ['SAS'] }
  },
  {
    path: '/sas/incidencias',
    name: 'sas-incidencias',
    component: IncidenciasInformativasView,
    meta: { requiresAuth: true, roles: ['SAS', 'MI', 'EMI', 'CSMI', 'ECC', 'GUEST'] }
  },
  {
    path: '/sas/publicaciones',
    name: 'sas-publicaciones',
    component: MisPublicacionesView,
    meta: { requiresAuth: true, roles: ['SAS'] }
  },
  {
    path: '/monitor/dashboard',
    name: 'monitor-dashboard',
    component: DashboardMonitorView,
    meta: { requiresAuth: true, roles: ['MI'] }
  },
  {
    path: '/tickets/consulta',
    name: 'tickets-consulta',
    component: GestionTicketsView,
    meta: { requiresAuth: true, roles: ['MI', 'EMI', 'CSMI', 'ECC', 'GUEST'] }
  },
  {
    path: '/modulo-gerencial',
    name: 'modulo-gerencial',
    component: ModuloGerencialView,
    meta: { requiresAuth: true, roles: ['MI', 'EMI', 'CSMI', 'ECC', 'GUEST'] }
  },
  {
    path: '/administracion/catalogo',
    name: 'admin-catalogo',
    component: AdministracionCatalogoView,
    meta: { requiresAuth: true, roles: ['EMI'] }
  },
  {
    path: '/administracion/publicaciones',
    name: 'admin-publicaciones',
    component: GestionPublicacionesView,
    meta: { requiresAuth: true, roles: ['EMI', 'CSMI', 'ECC', 'GUEST'] }
  },
  {
    path: '/administracion/usuarios',
    name: 'admin-usuarios',
    component: CargaUsuariosView,
    meta: { requiresAuth: true, roles: ['EMI', 'CSMI'] }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return next('/')
  }

  if (to.meta.roles && !to.meta.roles.includes(auth.rolCodigo)) {
    return next('/')
  }

  next()
})

export default router
