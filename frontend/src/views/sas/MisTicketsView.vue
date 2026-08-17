<template>
  <div class="page-body">
    <div class="card">
      <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <router-link to="/sas/registrar-ticket" class="btn btn-primary btn-sm">
          <Plus :size="15" />
          <span>+ Nuevo</span>
        </router-link>
      </div>

      <!-- Filtros basicos -->
      <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
          <input 
            v-model="filtroSearch" 
            @input="debouncedFetch" 
            type="text" 
            class="form-control" 
            placeholder="Buscar por N° Ticket, problema o identificador..."
          />
        </div>

        <div style="width: 180px;">
          <select v-model="filtroEstado" @change="fetchTickets" class="form-select">
            <option value="">Todos los Estados</option>
            <option value="Abierto">Abierto</option>
            <option value="Cerrado">Cerrado</option>
            <option value="No procede">No procede</option>
          </select>
        </div>
      </div>

      <!-- Tabla de Tickets -->
      <div v-if="loading" style="padding: 24px; text-align: center; color: #64748b;">
        Cargando tickets...
      </div>

      <div v-else-if="tickets.length === 0" style="padding: 32px; text-align: center; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px;">
        No se encontraron tickets registrados.
      </div>

      <div v-else class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>N° Ticket</th>
              <th>Identificador</th>
              <th>Fecha Registro</th>
              <th>Categoría / Tipo</th>
              <th>Prioridad</th>
              <th>Estado</th>
              <th>Tiempo</th>
              <th style="text-align: center;">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in tickets" :key="t.id">
              <td><strong>{{ t.cod_ticket }}</strong></td>
              <td>{{ t.identificador_interno_mi || '-' }}</td>
              <td>{{ formatFecha(t.fecha_emision) }}</td>
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
              <td>
                <span v-if="t.tiempo_resolucion">{{ formatTiempo(t.tiempo_resolucion) }}</span>
                <span v-else style="color: #94a3b8;">-</span>
              </td>
              <td style="text-align: center;">
                <button @click="verDetalle(t.id)" class="btn btn-secondary btn-sm">
                  <Eye :size="14" />
                  <span>Detalle</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de detalle de ticket -->
    <TicketDetailModal
      :is-open="modalDetalleOpen"
      :ticket-id="selectedTicketId"
      @close="modalDetalleOpen = false"
      @updated="fetchTickets"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import TicketDetailModal from '@/components/TicketDetailModal.vue'
import { Plus, Eye } from 'lucide-vue-next'

const tickets = ref([])
const loading = ref(false)
const filtroSearch = ref('')
const filtroEstado = ref('')

const modalDetalleOpen = ref(false)
const selectedTicketId = ref(null)

let debounceTimeout = null

const fetchTickets = async () => {
  loading.value = true
  try {
    const params = {}
    if (filtroEstado.value) params.estado = filtroEstado.value
    if (filtroSearch.value.trim()) params.search = filtroSearch.value.trim()

    const res = await api.get('/tickets', { params })
    if (res.data.success) {
      tickets.value = res.data.data.data || res.data.data || []
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const debouncedFetch = () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(fetchTickets, 350)
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

const formatTiempo = (str) => {
  if (!str) return '-'
  const parts = str.split(':')
  if (parts.length >= 2) return `${parts[0]}:${parts[1]}`
  return str
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
  fetchTickets()
})
</script>
