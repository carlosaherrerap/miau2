<template>
  <div class="page-body">
    <div class="card">
      <div v-if="!mostrandoFormulario" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button @click="mostrandoFormulario = true" class="btn btn-primary btn-sm">
          <Plus :size="15" />
          <span>+ Nuevo</span>
        </button>
      </div>

      <!-- FORMULARIO DE REGISTRO -->
      <div v-if="mostrandoFormulario" style="margin-bottom: 24px; padding: 18px; background-color: #f8fafc; border: 1px solid #94a3b8; border-radius: 6px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a;">Nuevo Registro Informativo</h3>
          <button @click="mostrandoFormulario = false" class="btn btn-secondary btn-sm">
            <X :size="14" />
            <span>Cancelar</span>
          </button>
        </div>

        <form @submit.prevent="guardarIncidencia">
          <div class="form-group">
            <label class="form-label">Tipo de Incidencia / Evento: *</label>
            <select v-model="form.tipo_incidencia" class="form-select" required>
              <option value="corte_energia">Corte de Energía Eléctrica</option>
              <option value="problema_conectividad">Problema de Conectividad</option>
              <option value="aplicativo_movil">Problema de Instalación / Funcionamiento de APK Móvil</option>
            </select>
          </div>

          <!-- Campos para Corte de Energia / Conectividad -->
          <div v-if="form.tipo_incidencia === 'corte_energia' || form.tipo_incidencia === 'problema_conectividad'">
            <div class="grid-3">
              <div class="form-group" v-if="form.tipo_incidencia === 'corte_energia'">
                <label class="form-label">Tipo de Corte: *</label>
                <select v-model="form.subtipo_corte" class="form-select" required>
                  <option value="programado">Programado</option>
                  <option value="accidental">Accidental</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Fecha y Hora Inicio: *</label>
                <input v-model="form.fecha_inicio" type="datetime-local" class="form-control" required />
              </div>

              <div class="form-group">
                <label class="form-label">Fecha y Hora Reanudación (Opcional):</label>
                <input v-model="form.fecha_reanudacion" type="datetime-local" class="form-control" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Actividades Afectadas:</label>
              <input v-model="form.actividades_afectadas" type="text" class="form-control" placeholder="Ej: Atención de postulantes, sincronización..." />
            </div>

            <div class="form-group">
              <label class="form-label">Observaciones Adicionales:</label>
              <textarea v-model="form.observaciones" class="form-control" placeholder="Detalle adicional sobre la causa o estado actual..."></textarea>
            </div>
          </div>

          <!-- Campos para Aplicativo Movil -->
          <div v-if="form.tipo_incidencia === 'aplicativo_movil'">
            <div class="grid-3">
              <div class="form-group">
                <label class="form-label">Marca y Modelo: *</label>
                <input v-model="form.marca_modelo" type="text" class="form-control" placeholder="Ej: Samsung Galaxy A03" required />
              </div>

              <div class="form-group">
                <label class="form-label">Versión Android: *</label>
                <input v-model="form.version_android" type="text" class="form-control" placeholder="Ej: Android 11 Go" required />
              </div>

              <div class="form-group">
                <label class="form-label">Aplicativo o APK: *</label>
                <input v-model="form.aplicativo_afectado" type="text" class="form-control" placeholder="Ej: APK Geolocalización v3.0" required />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Proceso Relacionado:</label>
              <input v-model="form.proceso_relacionado" type="text" class="form-control" placeholder="Ej: Capacitación, marcación de asistencia..." />
            </div>

            <div class="form-group">
              <label class="form-label">Descripción del Problema: *</label>
              <textarea v-model="form.descripcion_problema" class="form-control" placeholder="Describa la dificultad que se presenta en el equipo..." required></textarea>
            </div>

            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Descartes Realizados por SAS:</label>
                <textarea v-model="form.descartes_sas" class="form-control" placeholder="Ej: Se reinstaló APK, se borró caché..."></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Resultado de Pruebas:</label>
                <textarea v-model="form.resultado_pruebas" class="form-control" placeholder="Detalle de resultados obtenidos..."></textarea>
              </div>
            </div>

            <div class="form-group" style="margin-top: 8px;">
              <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: #1e293b; cursor: pointer;">
                <input type="checkbox" v-model="form.funciona_en_equipo" />
                <span>¿El aplicativo funciona en el equipo? (Dejar desmarcado si NO funciona)</span>
              </label>
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
            <button type="button" @click="mostrandoFormulario = false" class="btn btn-secondary">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <Save :size="15" />
              <span>{{ saving ? 'Guardando...' : 'Guardar Aviso' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- LISTADO DE INCIDENCIAS INFORMATIVAS -->
      <div v-if="loading" style="padding: 24px; text-align: center; color: #64748b;">
        Cargando avisos e incidencias...
      </div>

      <div v-else-if="incidencias.length === 0" style="padding: 32px; text-align: center; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px;">
        No se registran avisos operativos o incidencias informativas.
      </div>

      <div v-else class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Tipo de Evento</th>
              <th>Sede Jurisdiccional</th>
              <th>Fecha Registro</th>
              <th>Detalle / Equipo</th>
              <th>Tiempo</th>
              <th>Estado</th>
              <th style="text-align: center;">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inc in incidencias" :key="inc.id">
              <td>
                <strong v-if="inc.tipo_incidencia === 'corte_energia'">Corte Energía ({{ inc.subtipo_corte }})</strong>
                <strong v-else-if="inc.tipo_incidencia === 'problema_conectividad'">Conectividad</strong>
                <strong v-else>APK Móvil</strong>
              </td>
              <td>{{ inc.sede_jurisdiccional?.nombre }}</td>
              <td>{{ formatFecha(inc.fecha_inicio || inc.created_at) }}</td>
              <td>
                <div v-if="inc.tipo_incidencia === 'aplicativo_movil'">
                  <span>{{ inc.marca_modelo }} ({{ inc.version_android }})</span>
                  <div style="font-size: 0.75rem; color: #64748b;">{{ inc.aplicativo_afectado }}</div>
                </div>
                <div v-else>
                  <span>{{ inc.actividades_afectadas || inc.observaciones || '-' }}</span>
                </div>
              </td>
              <td>
                <span v-if="inc.tiempo_interrupcion_minutos"><strong>{{ inc.tiempo_interrupcion_minutos }} min</strong></span>
                <span v-else style="color: #94a3b8;">En curso</span>
              </td>
              <td>
                <span v-if="inc.fecha_reanudacion" class="badge badge-cerrado">Restablecido</span>
                <span v-else class="badge badge-abierto">Interrupción</span>
              </td>
              <td style="text-align: center;">
                <button v-if="!inc.fecha_reanudacion && (inc.tipo_incidencia === 'corte_energia' || inc.tipo_incidencia === 'problema_conectividad')" 
                  @click="abrirModalReanudar(inc)" class="btn btn-secondary btn-sm">
                  <CheckCircle2 :size="13" />
                  <span>Fin</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal para registrar fecha de reanudacion -->
    <div v-if="modalReanudarOpen" class="modal-backdrop" @click.self="modalReanudarOpen = false">
      <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
          <h3 class="card-title">Registrar Fin del Corte / Servicio</h3>
          <button @click="modalReanudarOpen = false" class="btn btn-secondary btn-sm">X</button>
        </div>
        <div class="form-group">
          <label class="form-label">Fecha y Hora de Reanudación:</label>
          <input v-model="fechaReanudacionModal" type="datetime-local" class="form-control" required />
        </div>
        <div class="modal-footer">
          <button @click="modalReanudarOpen = false" class="btn btn-secondary">Cancelar</button>
          <button @click="guardarReanudacion" class="btn btn-primary">
            <Save :size="15" />
            <span>Guardar</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { Plus, X, Save, CheckCircle2 } from 'lucide-vue-next'

const incidencias = ref([])
const loading = ref(false)
const mostrandoFormulario = ref(false)
const saving = ref(false)

const modalReanudarOpen = ref(false)
const selectedIncidencia = ref(null)
const fechaReanudacionModal = ref('')

const form = ref({
  tipo_incidencia: 'corte_energia',
  subtipo_corte: 'accidental',
  fecha_inicio: '',
  fecha_reanudacion: '',
  actividades_afectadas: '',
  observaciones: '',
  marca_modelo: '',
  version_android: '',
  aplicativo_afectado: '',
  proceso_relacionado: '',
  descripcion_problema: '',
  descartes_sas: '',
  resultado_pruebas: '',
  funciona_en_equipo: false
})

const fetchIncidencias = async () => {
  loading.value = true
  try {
    const res = await api.get('/incidencias-informativas')
    if (res.data.success) {
      incidencias.value = res.data.data.data || res.data.data || []
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const guardarIncidencia = async () => {
  saving.value = true
  try {
    const res = await api.post('/incidencias-informativas', form.value)
    if (res.data.success) {
      mostrandoFormulario.value = false
      fetchIncidencias()
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Error al registrar aviso')
  } finally {
    saving.value = false
  }
}

const abrirModalReanudar = (inc) => {
  selectedIncidencia.value = inc
  fechaReanudacionModal.value = new Date().toISOString().slice(0, 16)
  modalReanudarOpen.value = true
}

const guardarReanudacion = async () => {
  if (!fechaReanudacionModal.value) return
  try {
    await api.put(`/incidencias-informativas/${selectedIncidencia.value.id}`, {
      fecha_reanudacion: fechaReanudacionModal.value
    })
    modalReanudarOpen.value = false
    fetchIncidencias()
  } catch (err) {
    alert(err.response?.data?.message || 'Error al actualizar reanudación')
  }
}

const formatFecha = (str) => {
  if (!str) return '-'
  const d = new Date(str)
  return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchIncidencias()
})
</script>
