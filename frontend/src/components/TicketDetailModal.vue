<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="cerrar">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h3 class="card-title">Detalle del Ticket N° {{ ticket?.cod_ticket }}</h3>
          <span class="badge" :class="badgeClass(ticket?.estado)" style="margin-top: 4px;">{{ ticket?.estado }}</span>
        </div>
        <button @click="cerrar" class="btn btn-secondary btn-sm">Cerrar</button>
      </div>

      <div v-if="loading" style="padding: 20px; text-align: center;">
        Cargando información del ticket...
      </div>

      <div v-else-if="ticket" class="ticket-detail-body">
        <div class="detail-grid">
          <div>
            <span class="detail-label">Identificador Interno MI:</span>
            <span class="detail-val"><strong>{{ ticket.identificador_interno_mi || 'Pendiente' }}</strong></span>
          </div>
          <div>
            <span class="detail-label">Fecha de Emisión:</span>
            <span class="detail-val">{{ formatFecha(ticket.fecha_emision) }}</span>
          </div>
          <div>
            <span class="detail-label">Usuario Solicitante (SAS):</span>
            <span class="detail-val">{{ ticket.solicitante ? `${ticket.solicitante.nombres} ${ticket.solicitante.ape_pat} (${ticket.solicitante.cod_usuario})` : '-' }}</span>
          </div>
          <div>
            <span class="detail-label">Sede Jurisdiccional:</span>
            <span class="detail-val">{{ ticket.sede_jurisdiccional?.nombre }} ({{ ticket.sede_jurisdiccional?.sede_regional?.nombre }})</span>
          </div>
          <div>
            <span class="detail-label">Monitor Responsable:</span>
            <span class="detail-val">{{ ticket.monitor_responsable ? `${ticket.monitor_responsable.nombres} ${ticket.monitor_responsable.ape_pat} (${ticket.monitor_responsable.codigo_monitor || 'MI'})` : 'Por Asignar' }}</span>
          </div>
          <div>
            <span class="detail-label">Prioridad:</span>
            <span class="badge" :class="prioridadClass(ticket.prioridad)">{{ ticket.prioridad }}</span>
          </div>
          <div>
            <span class="detail-label">Categoría:</span>
            <span class="detail-val">{{ ticket.categoria?.nombre }}</span>
          </div>
          <div>
            <span class="detail-label">Tipo de Atención:</span>
            <span class="detail-val">{{ ticket.tipo_atencion?.nombre }}</span>
          </div>
          <div v-if="ticket.tiempo_resolucion">
            <span class="detail-label">Tiempo de Resolución:</span>
            <span class="detail-val"><strong>{{ formatTiempo(ticket.tiempo_resolucion) }}</strong></span>
          </div>
          <div v-if="ticket.fecha_cierre">
            <span class="detail-label">Fecha de Cierre:</span>
            <span class="detail-val">{{ formatFecha(ticket.fecha_cierre) }}</span>
          </div>
        </div>

        <div class="form-group" style="margin-top: 16px;">
          <label class="detail-label">Descripción del Problema / Solicitud:</label>
          <div class="detail-box">{{ ticket.descripcion_problema }}</div>
        </div>

        <div class="form-group" v-if="ticket.archivos && ticket.archivos.length > 0">
          <label class="detail-label">Archivos Adjuntos ({{ ticket.archivos.length }}):</label>
          <ul class="file-list">
            <li v-for="archivo in ticket.archivos" :key="archivo.id" class="file-item">
              <span>{{ archivo.nombre_original }}</span>
              <a :href="`/storage/${archivo.ruta}`" target="_blank" download class="btn btn-secondary btn-sm">
                Descargar
              </a>
            </li>
          </ul>
        </div>

        <div v-if="ticket.descripcion_resolucion" class="alert alert-success" style="margin-top: 16px;">
          <strong>Resolución Registrada:</strong>
          <p style="margin-top: 4px;">{{ ticket.descripcion_resolucion }}</p>
        </div>

        <div v-if="ticket.justificacion_no_procede" class="alert alert-danger" style="margin-top: 16px;">
          <strong>Justificación de No Procede:</strong>
          <p style="margin-top: 4px;">{{ ticket.justificacion_no_procede }}</p>
        </div>

        <div v-if="canManage" class="monitor-actions-section">
          <div v-if="ticket.estado === 'Abierto'">
            <h4 class="section-subtitle">Gestión y Resolución de Ticket</h4>
            
            <div class="form-group">
              <label class="form-label">Acción de Estado:</label>
              <select v-model="formAtencion.estado" class="form-select">
                <option value="Cerrado">Cerrar Ticket (Atendido y Resuelto)</option>
                <option value="No procede">No Procede (Rechazar con Justificación)</option>
              </select>
            </div>

            <div class="form-group" v-if="formAtencion.estado === 'Cerrado'">
              <label class="form-label">Descripción de la Solución / Resolución:</label>
              <textarea v-model="formAtencion.descripcion_resolucion" class="form-control" placeholder="Ingrese el detalle de la solución técnica aplicada..."></textarea>
            </div>

            <div class="form-group" v-if="formAtencion.estado === 'No procede'">
              <label class="form-label">Justificación de No Procede:</label>
              <textarea v-model="formAtencion.justificacion_no_procede" class="form-control" placeholder="Ingrese el motivo por el cual no procede la atención..."></textarea>
            </div>

            <div v-if="errorMsg" class="alert alert-danger">{{ errorMsg }}</div>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
              <button @click="guardarAtencion" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Guardando...' : 'Registrar Atención' }}
              </button>
            </div>
          </div>

          <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <h4 class="section-subtitle" style="margin: 0;">Edición de Clasificación (Categoría y Tipo)</h4>
              <button v-if="!editandoClasificacion" @click="activarEdicionClasificacion" class="btn btn-secondary btn-sm">
                Modificar Clasificación
              </button>
            </div>

            <div v-if="editandoClasificacion">
              <div class="grid-2">
                <div class="form-group">
                  <label class="form-label">Categoría:</label>
                  <select v-model="formClasificacion.id_categoria" @change="onCategoriaChange" class="form-select">
                    <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                  </select>
                </div>

                <div class="form-group">
                  <label class="form-label">Tipo de Atención:</label>
                  <select v-model="formClasificacion.id_tipo_atencion" class="form-select">
                    <option v-for="tipo in tiposDisponibles" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
                  </select>
                </div>
              </div>

              <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button @click="editandoClasificacion = false" class="btn btn-secondary btn-sm">Cancelar</button>
                <button @click="guardarClasificacion" class="btn btn-primary btn-sm" :disabled="savingClasif">
                  {{ savingClasif ? 'Guardando...' : 'Guardar Nueva Clasificación' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const props = defineProps({
  isOpen: Boolean,
  ticketId: [Number, String]
})

const emit = defineEmits(['close', 'updated'])

const auth = useAuthStore()
const ticket = ref(null)
const loading = ref(false)
const saving = ref(false)
const savingClasif = ref(false)
const errorMsg = ref('')

const categorias = ref([])
const editandoClasificacion = ref(false)

const formAtencion = ref({
  estado: 'Cerrado',
  descripcion_resolucion: '',
  justificacion_no_procede: ''
})

const formClasificacion = ref({
  id_categoria: null,
  id_tipo_atencion: null
})

const canManage = computed(() => {
  return auth.isMonitor || auth.isEspecialista
})

const tiposDisponibles = computed(() => {
  if (!formClasificacion.value.id_categoria) return []
  const cat = categorias.value.find(c => c.id === formClasificacion.value.id_categoria)
  return cat ? cat.tipos : []
})

const loadTicket = async () => {
  if (!props.ticketId) return
  loading.value = true
  errorMsg.value = ''
  try {
    const res = await api.get(`/tickets/${props.ticketId}`)
    if (res.data.success) {
      ticket.value = res.data.data
      formClasificacion.value.id_categoria = ticket.value.id_categoria
      formClasificacion.value.id_tipo_atencion = ticket.value.id_tipo_atencion
    }
  } catch (err) {
    errorMsg.value = 'Error al cargar los datos del ticket.'
  } finally {
    loading.value = false
  }
}

const loadCategorias = async () => {
  try {
    const res = await api.get('/catalogo')
    if (res.data.success) {
      categorias.value = res.data.data
    }
  } catch (err) {
  }
}

const onCategoriaChange = () => {
  if (tiposDisponibles.value.length > 0) {
    formClasificacion.value.id_tipo_atencion = tiposDisponibles.value[0].id
  } else {
    formClasificacion.value.id_tipo_atencion = null
  }
}

const activarEdicionClasificacion = () => {
  editandoClasificacion.value = true
  if (categorias.value.length === 0) {
    loadCategorias()
  }
}

const guardarAtencion = async () => {
  errorMsg.value = ''
  if (formAtencion.value.estado === 'Cerrado' && !formAtencion.value.descripcion_resolucion.trim()) {
    errorMsg.value = 'Por favor ingrese la descripción de la resolución.'
    return
  }
  if (formAtencion.value.estado === 'No procede' && !formAtencion.value.justificacion_no_procede.trim()) {
    errorMsg.value = 'Por favor ingrese la justificación de por qué no procede.'
    return
  }

  saving.value = true
  try {
    const res = await api.put(`/tickets/${ticket.value.id}/atender`, formAtencion.value)
    if (res.data.success) {
      ticket.value = res.data.data
      emit('updated')
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Error al actualizar el estado del ticket.'
  } finally {
    saving.value = false
  }
}

const guardarClasificacion = async () => {
  savingClasif.value = true
  try {
    const res = await api.put(`/tickets/${ticket.value.id}/clasificacion`, formClasificacion.value)
    if (res.data.success) {
      ticket.value = res.data.data
      editandoClasificacion.value = false
      emit('updated')
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Error al actualizar la clasificación.')
  } finally {
    savingClasif.value = false
  }
}

const cerrar = () => {
  editandoClasificacion.value = false
  emit('close')
}

const formatFecha = (str) => {
  if (!str) return '-'
  const d = new Date(str)
  return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatTiempo = (str) => {
  if (!str) return '-'
  const parts = str.split(':')
  if (parts.length >= 2) {
    return `${parts[0]}:${parts[1]}`
  }
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

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    loadTicket()
    loadCategorias()
  }
})
</script>

<style scoped>
.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  padding: 12px;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
}

.detail-label {
  display: block;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
  margin-bottom: 2px;
}

.detail-val {
  font-size: 0.875rem;
  color: #1e293b;
}

.detail-box {
  padding: 10px 12px;
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.875rem;
  white-space: pre-wrap;
}

.file-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 12px;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  font-size: 0.8125rem;
}

.monitor-actions-section {
  margin-top: 20px;
  padding: 16px;
  background-color: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
}

.section-subtitle {
  font-size: 0.95rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 12px;
}
</style>
