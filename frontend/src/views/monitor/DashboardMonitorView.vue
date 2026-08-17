<template>
  <div class="page-body">
    <div class="card">
      <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <router-link to="/tickets/consulta" class="btn btn-primary btn-sm">
          <ListFilter :size="15" />
          <span>Ver Tickets</span>
        </router-link>
      </div>

      <!-- Tarjetas interactivas de KPIs -->
      <div class="grid-4" style="margin-bottom: 24px;">
        <div class="kpi-card kpi-abiertos" @click="filtrarPorEstado('Abierto')">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-label">Tickets Abiertos</span>
            <Clock :size="16" style="color: #b45309;" />
          </div>
          <span class="kpi-number">{{ kpis.abiertos }}</span>
          <span class="kpi-hint">Pendientes &rarr;</span>
        </div>

        <div class="kpi-card kpi-cerrados" @click="filtrarPorEstado('Cerrado')">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-label">Tickets Cerrados</span>
            <CheckCircle :size="16" style="color: #15803d;" />
          </div>
          <span class="kpi-number">{{ kpis.cerrados }}</span>
          <span class="kpi-hint">Resueltos &rarr;</span>
        </div>

        <div class="kpi-card kpi-noprocede" @click="filtrarPorEstado('No procede')">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-label">No Procede</span>
            <XCircle :size="16" style="color: #b91c1c;" />
          </div>
          <span class="kpi-number">{{ kpis.no_procede }}</span>
          <span class="kpi-hint">Rechazados &rarr;</span>
        </div>

        <div class="kpi-card kpi-total" @click="filtrarPorEstado('')">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-label">Total Tickets</span>
            <Ticket :size="16" style="color: #1e40af;" />
          </div>
          <span class="kpi-number">{{ kpis.total }}</span>
          <span class="kpi-hint">Ver todos &rarr;</span>
        </div>
      </div>

      <!-- Ultimos tickets recibidos -->
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a;">Últimos Tickets Recibidos</h3>
          <router-link to="/tickets/consulta" class="btn btn-secondary btn-sm">
            <span>Ver Todos</span>
          </router-link>
        </div>

        <div v-if="loading" style="padding: 20px; text-align: center; color: #64748b;">
          Cargando tickets...
        </div>

        <div v-else-if="ultimosTickets.length === 0" style="padding: 24px; text-align: center; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px;">
          No hay tickets registrados en sus sedes asignadas.
        </div>

        <div v-else class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>N° Ticket</th>
                <th>Identificador</th>
                <th>Fecha Emisión</th>
                <th>Sede Jurisdiccional</th>
                <th>Categoría / Tipo</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th style="text-align: center;">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in ultimosTickets" :key="t.id">
                <td><strong>{{ t.cod_ticket }}</strong></td>
                <td>{{ t.identificador_interno_mi || '-' }}</td>
                <td>{{ formatFecha(t.fecha_emision) }}</td>
                <td>{{ t.sede_jurisdiccional?.nombre }}</td>
                <td>
                  <div>{{ t.categoria?.nombre }}</div>
                  <small style="color: #64748b;">{{ t.tipo_atencion?.nombre }}</small>
                </td>
                <td>
                  <span class="badge" :class="prioridadClass(t.prioridad)">{{ t.prioridad }}</span>
                </td>
                <td>
                  <span class="badge" :class="badgeClass(t.estado)">{{ t.estado }}</span>
                </td>
                <td style="text-align: center;">
                  <button @click="verDetalle(t.id)" class="btn btn-secondary btn-sm">
                    <Eye :size="14" />
                    <span>Gestionar</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal de gestion / detalle de ticket -->
    <TicketDetailModal
      :is-open="modalDetalleOpen"
      :ticket-id="selectedTicketId"
      @close="modalDetalleOpen = false"
      @updated="loadData"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import TicketDetailModal from '@/components/TicketDetailModal.vue'
import { ListFilter, Clock, CheckCircle, XCircle, Ticket, Eye } from 'lucide-vue-next'

const router = useRouter()
const loading = ref(false)
const kpis = ref({
  total: 0,
  abiertos: 0,
  cerrados: 0,
  no_procede: 0
})
const ultimosTickets = ref([])

const modalDetalleOpen = ref(false)
const selectedTicketId = ref(null)

const loadData = async () => {
  loading.value = true
  try {
    const [resKpis, resTickets] = await Promise.all([
      api.get('/dashboard/kpis'),
      api.get('/tickets?per_page=6')
    ])

    if (resKpis.data.success) {
      kpis.value = resKpis.data.data
    }

    if (resTickets.data.success) {
      ultimosTickets.value = resTickets.data.data.data || resTickets.data.data || []
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const filtrarPorEstado = (estado) => {
  router.push({
    path: '/tickets/consulta',
    query: estado ? { estado } : {}
  })
}

const verDetalle = (id) => {
  selectedTicketId.value = id
  modalDetalleOpen.value = true
}

const formatFecha = (str) => {
  if (!str) return '-'
  const d = new Date(str)
  return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const badgeClass = (estado) => {
  if (estado === 'Abierto') return 'badge-abierto'
  if (estado === 'Cerrado') return 'badge-cerrado'
  if (estado === 'No procede') return 'badge-noprocede'
  return ''
}

const prioridadClass = (p) => {
  if (p === 'Alta') return 'badge-alta'
  if (p === 'Media') return 'badge-media'
  return 'badge-baja'
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.kpi-card {
  padding: 16px 18px;
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  transition: border-color 0.15s ease, background-color 0.15s ease;
}

.kpi-card:hover {
  border-color: #1e40af;
  background-color: #f8fafc;
}

.kpi-label {
  font-size: 0.8125rem;
  font-weight: 700;
  color: #475569;
}

.kpi-number {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  margin: 4px 0;
  line-height: 1;
}

.kpi-hint {
  font-size: 0.75rem;
  color: #1e40af;
  font-weight: 600;
}

.kpi-abiertos {
  border-left: 4px solid #d97706;
}
.kpi-cerrados {
  border-left: 4px solid #15803d;
}
.kpi-noprocede {
  border-left: 4px solid #b91c1c;
}
.kpi-total {
  border-left: 4px solid #1e40af;
}
</style>
