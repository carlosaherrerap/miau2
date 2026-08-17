<template>
  <div class="page-body">
    <div class="card">
      <div v-if="auth.isEspecialista && !mostrandoNuevaPub" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button @click="mostrandoNuevaPub = true" class="btn btn-primary btn-sm">
          <Plus :size="15" />
          <span>+ Publicar</span>
        </button>
      </div>

      <!-- FORMULARIO DE NUEVA PUBLICACION -->
      <div v-if="mostrandoNuevaPub" style="margin-bottom: 24px; padding: 18px; background-color: #f8fafc; border: 1px solid #94a3b8; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a;">Registrar Publicación</h3>
          <button @click="mostrandoNuevaPub = false" class="btn btn-secondary btn-sm">
            <X :size="14" />
            <span>Cancelar</span>
          </button>
        </div>

        <form @submit.prevent="guardarPublicacion">
          <div class="grid-3">
            <div class="form-group">
              <label class="form-label">Nombre del Aplicativo: *</label>
              <input v-model="formPub.nombre_aplicativo" type="text" class="form-control" placeholder="Ej: APK Geolocalización" required />
            </div>

            <div class="form-group">
              <label class="form-label">Versión o Referencia: *</label>
              <input v-model="formPub.version" type="text" class="form-control" placeholder="Ej: Versión 3.0" required />
            </div>

            <div class="form-group">
              <label class="form-label">Fecha de Publicación: *</label>
              <input v-model="formPub.fecha_publicacion" type="datetime-local" class="form-control" required />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Texto Informativo o Indicaciones: *</label>
            <textarea v-model="formPub.indicaciones" class="form-control" placeholder="Instrucciones dirigidas a los supervisores SAS..." rows="3" required></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Enlaces Generales (Opcional):</label>
            <input v-model="formPub.enlaces_generales" type="text" class="form-control" placeholder="Ej: https://drive.google.com/drive/folders/xxxx" />
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
            <button type="button" @click="mostrandoNuevaPub = false" class="btn btn-secondary">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <Save :size="14" />
              <span>{{ saving ? 'Guardando...' : 'Crear y Publicar' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- LISTA DE PUBLICACIONES -->
      <div v-if="loading" style="padding: 24px; text-align: center; color: #64748b;">
        Cargando publicaciones...
      </div>

      <div v-else-if="publicaciones.length === 0" style="padding: 32px; text-align: center; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px;">
        No se registran publicaciones actualmente.
      </div>

      <div v-else style="display: flex; flex-direction: column; gap: 16px;">
        <div v-for="pub in publicaciones" :key="pub.id" class="pub-admin-card">
          <div class="pub-admin-header">
            <div>
              <div style="display: flex; align-items: center; gap: 10px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">
                  {{ pub.nombre_aplicativo }} - {{ pub.version }}
                </h3>
                <span class="badge" :class="pub.estado === 'Activa' ? 'badge-activa' : 'badge-desactivada'">
                  {{ pub.estado }}
                </span>
              </div>
              <small style="color: #64748b;">
                Publicado el: {{ formatFecha(pub.fecha_publicacion) }} | Autor: {{ pub.autor?.nombres }} {{ pub.autor?.ape_pat }}
              </small>
            </div>

            <!-- Acciones de Publicacion con botones cortos e iconos -->
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
              <button @click="abrirSeguimiento(pub)" class="btn btn-secondary btn-sm">
                <Eye :size="14" />
                <span>Seguimiento ({{ pub.vistas_count || 0 }})</span>
              </button>

              <button v-if="auth.isEspecialista" @click="abrirModalCsv(pub)" class="btn btn-secondary btn-sm">
                <UploadCloud :size="14" />
                <span>Cargar CSV ({{ pub.credenciales_count || 0 }})</span>
              </button>

              <button v-if="auth.isEspecialista" @click="toggleVigencia(pub)" class="btn btn-sm" :class="pub.estado === 'Activa' ? 'btn-danger' : 'btn-success'">
                <Power :size="14" />
                <span>{{ pub.estado === 'Activa' ? 'Desactivar' : 'Activar' }}</span>
              </button>
            </div>
          </div>

          <div style="font-size: 0.875rem; color: #334155; margin-top: 8px; white-space: pre-wrap;">
            {{ pub.indicaciones }}
          </div>

          <div v-if="pub.enlaces_generales" style="margin-top: 10px; font-size: 0.8125rem; color: #64748b;">
            <strong>Enlace:</strong> <a :href="pub.enlaces_generales" target="_blank">{{ pub.enlaces_generales }}</a>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL CSV CREDENCIALES -->
    <div v-if="modalCsvOpen" class="modal-backdrop" @click.self="modalCsvOpen = false">
      <div class="modal-content" style="max-width: 580px;">
        <div class="modal-header">
          <h3 class="card-title">Carga de Credenciales CSV</h3>
          <button @click="modalCsvOpen = false" class="btn btn-secondary btn-sm">
            <X :size="14" />
          </button>
        </div>

        <div style="font-size: 0.8125rem; color: #64748b; margin-bottom: 14px;">
          Publicación: <strong>{{ selectedPub?.nombre_aplicativo }} - {{ selectedPub?.version }}</strong>
          <p style="margin-top: 6px;">
            El archivo CSV debe tener en la primera columna el <strong>SAS ID</strong> seguido de pares de descripción y valor:
          </p>
          <div style="margin-top: 6px; padding: 8px; background-color: #f1f5f9; border-radius: 4px; font-family: monospace; font-size: 0.75rem;">
            SAS ID,Dato1,Valor1,Dato2,Valor2,Dato3,Valor3<br/>
            SAS043,Administrador Clave,3345,Administrador Token,4456,Enlace marco,https://drive.google.com/xxxxx
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Seleccione el archivo CSV: *</label>
          <input type="file" ref="csvFileInput" accept=".csv,.txt" class="form-control" required />
        </div>

        <div v-if="csvUploadMsg" class="alert alert-info">{{ csvUploadMsg }}</div>

        <div class="modal-footer">
          <button @click="modalCsvOpen = false" class="btn btn-secondary">Cancelar</button>
          <button @click="subirCsvCredenciales" class="btn btn-primary" :disabled="uploadingCsv">
            <UploadCloud :size="15" />
            <span>{{ uploadingCsv ? 'Procesando...' : 'Cargar Credenciales' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL SEGUIMIENTO SAS -->
    <div v-if="modalSeguimientoOpen" class="modal-backdrop" @click.self="modalSeguimientoOpen = false">
      <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
          <div>
            <h3 class="card-title">Seguimiento de Visualización SAS</h3>
            <span style="font-size: 0.8125rem; color: #64748b;">
              {{ seguimientoData.publicacion?.nombre_aplicativo }} - {{ seguimientoData.publicacion?.version }}
            </span>
          </div>
          <div style="display: flex; gap: 8px;">
            <button @click="exportarSeguimientoExcel" class="btn btn-success btn-sm">
              <Download :size="14" />
              <span>Exportar Excel</span>
            </button>
            <button @click="modalSeguimientoOpen = false" class="btn btn-secondary btn-sm">
              <X :size="14" />
            </button>
          </div>
        </div>

        <!-- Indicadores de lectura -->
        <div class="grid-3" style="margin-bottom: 16px;">
          <div style="padding: 10px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px;">
            <span style="font-size: 0.75rem; color: #64748b;">Total SAS:</span>
            <div style="font-size: 1.25rem; font-weight: 700;">{{ seguimientoData.total_sas || 0 }}</div>
          </div>
          <div style="padding: 10px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">
            <span style="font-size: 0.75rem; color: #166534;">Visualizados:</span>
            <div style="font-size: 1.25rem; font-weight: 700; color: #166534;">{{ seguimientoData.visualizados || 0 }}</div>
          </div>
          <div style="padding: 10px; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 6px;">
            <span style="font-size: 0.75rem; color: #92400e;">Pendientes:</span>
            <div style="font-size: 1.25rem; font-weight: 700; color: #92400e;">{{ seguimientoData.pendientes || 0 }}</div>
          </div>
        </div>

        <!-- Filtro -->
        <div style="display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;">
          <select v-model="filtroSeguimientoEstado" @change="filtrarSeguimiento" class="form-select" style="width: 180px;">
            <option value="">Todos los Estados</option>
            <option value="Pendiente">Pendiente (Sin leer)</option>
            <option value="Visualizado">Visualizado</option>
          </select>
        </div>

        <!-- Tabla -->
        <div class="table-container" style="max-height: 380px; overflow-y: auto;">
          <table class="table">
            <thead>
              <tr>
                <th>Usuario SAS</th>
                <th>Sede Regional</th>
                <th>Sede Jurisdiccional</th>
                <th>Estado</th>
                <th>Fecha 1ra Visualización</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in seguimientoFiltrado" :key="item.id_usuario">
                <td><strong>{{ item.cod_usuario }}</strong> - {{ item.nombres }}</td>
                <td>{{ item.sede_regional }}</td>
                <td>{{ item.sede_jurisdiccional }}</td>
                <td>
                  <span class="badge" :class="item.estado_visualizacion === 'Visualizado' ? 'badge-cerrado' : 'badge-abierto'">
                    {{ item.estado_visualizacion }}
                  </span>
                </td>
                <td>{{ item.fecha_visualizacion }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import * as XLSX from 'xlsx'
import { Plus, Eye, UploadCloud, Power, Download, Save, X } from 'lucide-vue-next'

const auth = useAuthStore()
const publicaciones = ref([])
const loading = ref(false)
const saving = ref(false)
const mostrandoNuevaPub = ref(false)

const formPub = ref({
  nombre_aplicativo: '',
  version: '',
  fecha_publicacion: new Date().toISOString().slice(0, 16),
  indicaciones: '',
  enlaces_generales: ''
})

// Modal CSV
const modalCsvOpen = ref(false)
const selectedPub = ref(null)
const csvFileInput = ref(null)
const uploadingCsv = ref(false)
const csvUploadMsg = ref('')

// Modal Seguimiento
const modalSeguimientoOpen = ref(false)
const seguimientoData = ref({ publicacion: null, total_sas: 0, visualizados: 0, pendientes: 0, detalle: [] })
const filtroSeguimientoEstado = ref('')

const seguimientoFiltrado = computed(() => {
  if (!filtroSeguimientoEstado.value) return seguimientoData.value.detalle || []
  return (seguimientoData.value.detalle || []).filter(d => d.estado_visualizacion === filtroSeguimientoEstado.value)
})

const fetchPublicaciones = async () => {
  loading.value = true
  try {
    const res = await api.get('/publicaciones')
    if (res.data.success) {
      publicaciones.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const guardarPublicacion = async () => {
  saving.value = true
  try {
    const res = await api.post('/publicaciones', formPub.value)
    if (res.data.success) {
      mostrandoNuevaPub.value = false
      formPub.value = {
        nombre_aplicativo: '',
        version: '',
        fecha_publicacion: new Date().toISOString().slice(0, 16),
        indicaciones: '',
        enlaces_generales: ''
      }
      fetchPublicaciones()
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Error al guardar publicación')
  } finally {
    saving.value = false
  }
}

const toggleVigencia = async (pub) => {
  const nuevoEstado = pub.estado === 'Activa' ? 'Desactivada' : 'Activa'
  if (!confirm(`¿Está seguro de cambiar el estado a ${nuevoEstado}?`)) return
  try {
    await api.put(`/publicaciones/${pub.id}`, { estado: nuevoEstado })
    fetchPublicaciones()
  } catch (err) {
    alert(err.response?.data?.message || 'Error al cambiar vigencia')
  }
}

const abrirModalCsv = (pub) => {
  selectedPub.value = pub
  csvUploadMsg.value = ''
  modalCsvOpen.value = true
}

const subirCsvCredenciales = async () => {
  if (!csvFileInput.value?.files[0]) {
    alert('Por favor seleccione un archivo CSV.')
    return
  }

  uploadingCsv.value = true
  csvUploadMsg.value = ''
  try {
    const formData = new FormData()
    formData.append('archivo_csv', csvFileInput.value.files[0])

    const res = await api.post(`/publicaciones/${selectedPub.value.id}/credenciales-csv`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (res.data.success) {
      csvUploadMsg.value = res.data.message
      fetchPublicaciones()
      setTimeout(() => {
        modalCsvOpen.value = false
      }, 1500)
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Error al procesar archivo CSV')
  } finally {
    uploadingCsv.value = false
  }
}

const abrirSeguimiento = async (pub) => {
  selectedPub.value = pub
  modalSeguimientoOpen.value = true
  try {
    const res = await api.get(`/publicaciones/${pub.id}/seguimiento`)
    if (res.data.success) {
      seguimientoData.value = res.data.data
    }
  } catch (err) {
    alert('Error al cargar seguimiento de visualizaciones.')
  }
}

const exportarSeguimientoExcel = () => {
  const rows = seguimientoFiltrado.value.map(d => ({
    'Usuario SAS': d.cod_usuario,
    'Nombre SAS': d.nombres,
    'Sede Regional': d.sede_regional,
    'Sede Jurisdiccional': d.sede_jurisdiccional,
    'Estado': d.estado_visualizacion,
    'Fecha de Visualización': d.fecha_visualizacion
  }))

  const ws = XLSX.utils.json_to_sheet(rows)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Seguimiento SAS')
  XLSX.writeFile(wb, `Seguimiento_${selectedPub.value?.nombre_aplicativo || 'Pub'}_${new Date().toISOString().slice(0, 10)}.xlsx`)
}

const formatFecha = (str) => {
  if (!str) return '-'
  const d = new Date(str)
  return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(() => {
  fetchPublicaciones()
})
</script>

<style scoped>
.pub-admin-card {
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 18px;
}

.pub-admin-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding-bottom: 10px;
  border-bottom: 1px solid #e2e8f0;
  flex-wrap: wrap;
  gap: 10px;
}
</style>
