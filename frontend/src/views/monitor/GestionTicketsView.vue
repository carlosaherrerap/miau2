<template>
  <div class="page-body">
    <div class="card">
      <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button @click="exportarExcel" class="btn btn-success btn-sm" :disabled="exporting || tickets.length === 0">
          <Download :size="15" />
          <span>{{ exporting ? 'Exportando...' : 'Exportar Excel' }}</span>
        </button>
      </div>

      <!-- PANEL DE FILTROS AVANZADOS -->
      <div class="filters-panel">
        <div class="grid-4">
          <div class="form-group">
            <label class="form-label">Sede Regional:</label>
            <select v-model="filtros.id_sedereg" @change="onSedeRegionalChange" class="form-select">
              <option value="">Todas las Sedes Regionales</option>
              <option v-for="sr in sedesRegionales" :key="sr.id" :value="sr.id">{{ sr.nombre }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Sede Jurisdiccional:</label>
            <select v-model="filtros.id_sedejuris" @change="fetchTickets" class="form-select">
              <option value="">Todas las Sedes Jurisdiccionales</option>
              <option v-for="sj in sedesJurisDisponibles" :key="sj.id" :value="sj.id">{{ sj.nombre }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Estado:</label>
            <select v-model="filtros.estado" @change="fetchTickets" class="form-select">
              <option value="">Todos los Estados</option>
              <option value="Abierto">Abierto</option>
              <option value="Cerrado">Cerrado</option>
              <option value="No procede">No procede</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Prioridad:</label>
            <select v-model="filtros.prioridad" @change="fetchTickets" class="form-select">
              <option value="">Todas las Prioridades</option>
              <option value="Alta">Alta</option>
              <option value="Media">Media</option>
              <option value="Baja">Baja</option>
            </select>
          </div>
        </div>

        <div class="grid-4">
          <div class="form-group">
            <label class="form-label">Categoría:</label>
            <select v-model="filtros.id_categoria" @change="onCategoriaFilterChange" class="form-select">
              <option value="">Todas las Categorías</option>
              <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Tipo de Atención:</label>
            <select v-model="filtros.id_tipo_atencion" @change="fetchTickets" class="form-select">
              <option value="">Todos los Tipos</option>
              <option v-for="tipo in tiposFiltroDisponibles" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Fecha Desde:</label>
            <input v-model="filtros.fecha_desde" @change="fetchTickets" type="date" class="form-control" />
          </div>

          <div class="form-group">
            <label class="form-label">Fecha Hasta:</label>
            <input v-model="filtros.fecha_hasta" @change="fetchTickets" type="date" class="form-control" />
          </div>
        </div>

        <!-- Buscador y Limpiar -->
        <div style="display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 250px;">
            <input 
              v-model="filtros.search" 
              @input="debouncedFetch" 
              type="text" 
              class="form-control" 
              placeholder="Buscar por N° Ticket, identificador, problema o solicitante..."
            />
          </div>

          <button @click="resetFiltros" class="btn btn-secondary btn-sm">
            <RotateCcw :size="14" />
            <span>Limpiar</span>
          </button>
        </div>
      </div>

      <!-- TABLA DE RESULTADOS -->
      <div v-if="loading" style="padding: 24px; text-align: center; color: #64748b;">
        Cargando resultados...
      </div>

      <div v-else-if="tickets.length === 0" style="padding: 32px; text-align: center; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px;">
        No se encontraron tickets con los criterios de búsqueda seleccionados.
      </div>

      <div v-else class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th @click="cambiarOrden('cod_ticket')" class="sortable-th">
                N° Ticket <span v-if="sortBy === 'cod_ticket'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="cambiarOrden('identificador_interno_mi')" class="sortable-th">
                Identificador <span v-if="sortBy === 'identificador_interno_mi'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="cambiarOrden('fecha_emision')" class="sortable-th">
                Fecha Registro <span v-if="sortBy === 'fecha_emision'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th>Sede</th>
              <th>Solicitante</th>
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
                <div>{{ t.sede_jurisdiccional?.nombre }}</div>
                <small style="color: #64748b;">{{ t.sede_jurisdiccional?.sede_regional?.nombre }}</small>
              </td>
              <td>{{ t.solicitante ? `${t.solicitante.nombres} ${t.solicitante.ape_pat}` : '-' }}</td>
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
                  <span>Gestionar</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginacion -->
      <div v-if="pagination.total > pagination.per_page" style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 0.875rem; color: #64748b;">
        <span>Página {{ pagination.current_page }} de {{ pagination.last_page }} ({{ pagination.total }} registros)</span>
        <div style="display: flex; gap: 6px;">
          <button 
            @click="cambiarPagina(pagination.current_page - 1)" 
            class="btn btn-secondary btn-sm" 
            :disabled="pagination.current_page <= 1"
          >
            Anterior
          </button>
          <button 
            @click="cambiarPagina(pagination.current_page + 1)" 
            class="btn btn-secondary btn-sm" 
            :disabled="pagination.current_page >= pagination.last_page"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Detalle -->
    <TicketDetailModal
      :is-open="modalDetalleOpen"
      :ticket-id="selectedTicketId"
      @close="modalDetalleOpen = false"
      @updated="fetchTickets"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import * as XLSX from 'xlsx'
import TicketDetailModal from '@/components/TicketDetailModal.vue'
import { Download, RotateCcw, Eye } from 'lucide-vue-next'

const route = useRoute()

const tickets = ref([])
const loading = ref(false)
const exporting = ref(false)

const sedesRegionales = ref([])
const categorias = ref([])

const sortBy = ref('fecha_emision')
const sortDir = ref('desc')

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const filtros = ref({
  id_sedereg: '',
  id_sedejuris: '',
  estado: '',
  id_categoria: '',
  id_tipo_atencion: '',
  prioridad: '',
  fecha_desde: '',
  fecha_hasta: '',
  search: ''
})

const modalDetalleOpen = ref(false)
const selectedTicketId = ref(null)
let debounceTimeout = null

const sedesJurisDisponibles = computed(() => {
  if (!filtros.value.id_sedereg) {
    return sedesRegionales.value.flatMap(sr => sr.sedes_juris || [])
  }
  const sr = sedesRegionales.value.find(s => s.id === filtros.value.id_sedereg)
  return sr ? (sr.sedes_juris || []) : []
})

const tiposFiltroDisponibles = computed(() => {
  if (!filtros.value.id_categoria) {
    return categorias.value.flatMap(c => c.tipos || [])
  }
  const cat = categorias.value.find(c => c.id === filtros.value.id_categoria)
  return cat ? (cat.tipos || []) : []
})

const loadCatalogos = async () => {
  try {
    const [resSedes, resCat] = await Promise.all([
      api.get('/sedes/regionales'),
      api.get('/catalogo')
    ])
    if (resSedes.data.success) sedesRegionales.value = resSedes.data.data
    if (resCat.data.success) categorias.value = resCat.data.data
  } catch (err) {
    console.error(err)
  }
}

const fetchTickets = async (pagina = 1) => {
  loading.value = true
  try {
    const params = {
      page: pagina,
      sort_by: sortBy.value,
      sort_dir: sortDir.value
    }

    if (filtros.value.id_sedereg) params.id_sedereg = filtros.value.id_sedereg
    if (filtros.value.id_sedejuris) params.id_sedejuris = filtros.value.id_sedejuris
    if (filtros.value.estado) params.estado = filtros.value.estado
    if (filtros.value.id_categoria) params.id_categoria = filtros.value.id_categoria
    if (filtros.value.id_tipo_atencion) params.id_tipo_atencion = filtros.value.id_tipo_atencion
    if (filtros.value.prioridad) params.prioridad = filtros.value.prioridad
    if (filtros.value.fecha_desde) params.fecha_desde = filtros.value.fecha_desde
    if (filtros.value.fecha_hasta) params.fecha_hasta = filtros.value.fecha_hasta
    if (filtros.value.search.trim()) params.search = filtros.value.search.trim()

    const res = await api.get('/tickets', { params })
    if (res.data.success) {
      const data = res.data.data
      if (data.data) {
        tickets.value = data.data
        pagination.value = {
          current_page: data.current_page,
          last_page: data.last_page,
          per_page: data.per_page,
          total: data.total
        }
      } else {
        tickets.value = data
      }
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const debouncedFetch = () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => fetchTickets(1), 350)
}

const onSedeRegionalChange = () => {
  filtros.value.id_sedejuris = ''
  fetchTickets(1)
}

const onCategoriaFilterChange = () => {
  filtros.value.id_tipo_atencion = ''
  fetchTickets(1)
}

const cambiarOrden = (columna) => {
  if (sortBy.value === columna) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = columna
    sortDir.value = 'asc'
  }
  fetchTickets(pagination.value.current_page)
}

const cambiarPagina = (p) => {
  fetchTickets(p)
}

const resetFiltros = () => {
  filtros.value = {
    id_sedereg: '',
    id_sedejuris: '',
    estado: '',
    id_categoria: '',
    id_tipo_atencion: '',
    prioridad: '',
    fecha_desde: '',
    fecha_hasta: '',
    search: ''
  }
  fetchTickets(1)
}

const verDetalle = (id) => {
  selectedTicketId.value = id
  modalDetalleOpen.value = true
}

const exportarExcel = async () => {
  exporting.value = true
  try {
    const params = {
      all: 'true',
      sort_by: sortBy.value,
      sort_dir: sortDir.value
    }
    if (filtros.value.id_sedereg) params.id_sedereg = filtros.value.id_sedereg
    if (filtros.value.id_sedejuris) params.id_sedejuris = filtros.value.id_sedejuris
    if (filtros.value.estado) params.estado = filtros.value.estado
    if (filtros.value.id_categoria) params.id_categoria = filtros.value.id_categoria
    if (filtros.value.id_tipo_atencion) params.id_tipo_atencion = filtros.value.id_tipo_atencion
    if (filtros.value.prioridad) params.prioridad = filtros.value.prioridad
    if (filtros.value.fecha_desde) params.fecha_desde = filtros.value.fecha_desde
    if (filtros.value.fecha_hasta) params.fecha_hasta = filtros.value.fecha_hasta
    if (filtros.value.search.trim()) params.search = filtros.value.search.trim()

    const res = await api.get('/tickets', { params })
    const dataList = res.data.success ? res.data.data : []

    const filasExcel = dataList.map(t => ({
      'N° Ticket': t.cod_ticket,
      'Identificador MI': t.identificador_interno_mi || '',
      'Fecha Emisión': formatFecha(t.fecha_emision),
      'Sede Regional': t.sede_jurisdiccional?.sede_regional?.nombre || '',
      'Sede Jurisdiccional': t.sede_jurisdiccional?.nombre || '',
      'Solicitante SAS': t.solicitante ? `${t.solicitante.nombres} ${t.solicitante.ape_pat}` : '',
      'Monitor Responsable': t.monitor_responsable ? `${t.monitor_responsable.nombres} ${t.monitor_responsable.ape_pat}` : '',
      'Categoría': t.categoria?.nombre || '',
      'Tipo de Atención': t.tipo_atencion?.nombre || '',
      'Prioridad': t.prioridad,
      'Estado': t.estado,
      'Tiempo Resolución (hh:mm)': formatTiempo(t.tiempo_resolucion),
      'Descripción del Problema': t.descripcion_problema,
      'Resolución': t.descripcion_resolucion || '',
      'Justificación No Procede': t.justificacion_no_procede || '',
      'Fecha Cierre': formatFecha(t.fecha_cierre)
    }))

    const ws = XLSX.utils.json_to_sheet(filasExcel)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Tickets')
    XLSX.writeFile(wb, `Reporte_Tickets_ENLA_${new Date().toISOString().slice(0, 10)}.xlsx`)
  } catch (err) {
    alert('Error al exportar datos a Excel.')
  } finally {
    exporting.value = false
  }
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

watch(() => route.query.estado, (newEstado) => {
  if (newEstado) {
    filtros.value.estado = newEstado
    fetchTickets(1)
  }
})

onMounted(async () => {
  if (route.query.estado) {
    filtros.value.estado = route.query.estado
  }
  await loadCatalogos()
  fetchTickets(1)
})
</script>

<style scoped>
.filters-panel {
  padding: 16px;
  background-color: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  margin-bottom: 20px;
}

.sortable-th {
  cursor: pointer;
  user-select: none;
}
.sortable-th:hover {
  background-color: #e2e8f0;
}
</style>
