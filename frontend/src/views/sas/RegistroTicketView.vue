<template>
  <div class="page-body">
    <div class="card">
      <div v-if="successMsg" class="alert alert-success">
        {{ successMsg }}
      </div>

      <div v-if="errorMsg" class="alert alert-danger">
        {{ errorMsg }}
      </div>

      <form @submit.prevent="handleSubmit">
        <!-- 1. Datos del Registro -->
        <div style="margin-bottom: 22px;">
          <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 10px;">
            1. Datos del Registro
          </h3>

          <div class="grid-2 auto-data-box">
            <div>
              <span class="data-label">Fecha y Hora de Registro:</span>
              <span class="data-value">{{ currentDateTime }}</span>
            </div>
            <div>
              <span class="data-label">Usuario Solicitante (SAS):</span>
              <span class="data-value"><strong>{{ auth.nombreCompleto }}</strong> ({{ auth.user?.cod_usuario }})</span>
            </div>
            <div>
              <span class="data-label">Sede Jurisdiccional Asociada:</span>
              <span class="data-value">{{ auth.user?.sede_jurisdiccional?.nombre || 'Sede no asignada' }}</span>
            </div>
            <div>
              <span class="data-label">Sede Regional:</span>
              <span class="data-value">{{ auth.user?.sede_jurisdiccional?.sede_regional?.nombre || auth.user?.sede_regional?.nombre || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- 2. Datos de la Incidencia -->
        <div style="margin-bottom: 22px;">
          <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 10px;">
            2. Datos de la Incidencia
          </h3>

          <div class="grid-3">
            <div class="form-group">
              <label class="form-label" for="categoria">Categoría de Atención: *</label>
              <select 
                id="categoria" 
                v-model="form.id_categoria" 
                @change="onCategoriaChange" 
                class="form-select" 
                required
              >
                <option :value="null" disabled>Seleccione categoría</option>
                <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                  {{ cat.nombre }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="tipo">Tipo de Atención: *</label>
              <select 
                id="tipo" 
                v-model="form.id_tipo_atencion" 
                class="form-select" 
                :disabled="!form.id_categoria || tiposDisponibles.length === 0"
                required
              >
                <option :value="null" disabled>Seleccione tipo</option>
                <option v-for="tipo in tiposDisponibles" :key="tipo.id" :value="tipo.id">
                  {{ tipo.nombre }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="prioridad">Prioridad de Atención: *</label>
              <select id="prioridad" v-model="form.prioridad" class="form-select" required>
                <option value="Alta">Alta</option>
                <option value="Media">Media</option>
                <option value="Baja">Baja</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="descripcion">Descripción del Problema o Solicitud: *</label>
            <textarea
              id="descripcion"
              v-model="form.descripcion_problema"
              class="form-control"
              placeholder="Describa claramente la incidencia presentada..."
              rows="4"
              required
            ></textarea>
          </div>
        </div>

        <!-- 3. Archivos Adjuntos -->
        <div style="margin-bottom: 24px;">
          <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
            3. Archivos Adjuntos (Máx. 3 archivos)
          </h3>
          <p style="font-size: 0.8125rem; color: #64748b; margin-bottom: 10px;">
            Formatos: Word (.doc, .docx), Excel (.xls, .xlsx), PDF o Imágenes (.jpg, .png).
          </p>

          <input 
            type="file" 
            ref="fileInput" 
            @change="handleFileChange" 
            multiple 
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
            class="form-control"
          />

          <div v-if="archivosSeleccionados.length > 0" style="margin-top: 10px;">
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 6px;">
              <li v-for="(file, idx) in archivosSeleccionados" :key="idx" class="selected-file-item">
                <div style="display: flex; align-items: center; gap: 8px;">
                  <Paperclip :size="15" style="color: #64748b;" />
                  <span>{{ file.name }} ({{ formatBytes(file.size) }})</span>
                </div>
                <button type="button" @click="removeFile(idx)" class="btn btn-secondary btn-sm" style="padding: 2px 8px;">
                  <Trash2 :size="13" />
                  <span>Quitar</span>
                </button>
              </li>
            </ul>
          </div>
        </div>

        <!-- Botones con textos cortos e iconos -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
          <button type="button" @click="resetForm" class="btn btn-secondary" :disabled="submitting">
            <RotateCcw :size="15" />
            <span>Limpiar</span>
          </button>
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <Send :size="15" />
            <span>{{ submitting ? 'Registrando...' : 'Registrar Ticket' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { Send, RotateCcw, Paperclip, Trash2 } from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const categorias = ref([])
const archivosSeleccionados = ref([])
const fileInput = ref(null)
const submitting = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const form = ref({
  id_categoria: null,
  id_tipo_atencion: null,
  prioridad: 'Media',
  descripcion_problema: ''
})

const currentDateTime = computed(() => {
  const d = new Date()
  return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
})

const tiposDisponibles = computed(() => {
  if (!form.value.id_categoria) return []
  const cat = categorias.value.find(c => c.id === form.value.id_categoria)
  return cat ? cat.tipos : []
})

const loadCatalogo = async () => {
  try {
    const res = await api.get('/catalogo?solo_activos=true')
    if (res.data.success) {
      categorias.value = res.data.data
      if (categorias.value.length > 0) {
        form.value.id_categoria = categorias.value[0].id
        onCategoriaChange()
      }
    }
  } catch (err) {
    errorMsg.value = 'Error al cargar catálogo de categorías.'
  }
}

const onCategoriaChange = () => {
  if (tiposDisponibles.value.length > 0) {
    form.value.id_tipo_atencion = tiposDisponibles.value[0].id
  } else {
    form.value.id_tipo_atencion = null
  }
}

const handleFileChange = (e) => {
  const files = Array.from(e.target.files)
  if (files.length + archivosSeleccionados.value.length > 3) {
    alert('Solo se permite adjuntar hasta 3 archivos por ticket.')
    return
  }
  archivosSeleccionados.value = [...archivosSeleccionados.value, ...files].slice(0, 3)
}

const removeFile = (idx) => {
  archivosSeleccionados.value.splice(idx, 1)
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatBytes = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`
}

const resetForm = () => {
  form.value.descripcion_problema = ''
  form.value.prioridad = 'Media'
  archivosSeleccionados.value = []
  if (fileInput.value) fileInput.value.value = ''
  if (categorias.value.length > 0) {
    form.value.id_categoria = categorias.value[0].id
    onCategoriaChange()
  }
}

const handleSubmit = async () => {
  errorMsg.value = ''
  successMsg.value = ''

  if (!form.value.id_categoria || !form.value.id_tipo_atencion) {
    errorMsg.value = 'Por favor seleccione la categoría y el tipo de atención.'
    return
  }

  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('id_categoria', form.value.id_categoria)
    formData.append('id_tipo_atencion', form.value.id_tipo_atencion)
    formData.append('prioridad', form.value.prioridad)
    formData.append('descripcion_problema', form.value.descripcion_problema)

    archivosSeleccionados.value.forEach((file) => {
      formData.append('archivos[]', file)
    })

    const res = await api.post('/tickets', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (res.data.success) {
      const ticketCreado = res.data.data
      successMsg.value = `Ticket N° ${ticketCreado.cod_ticket} registrado exitosamente con Identificador ${ticketCreado.identificador_interno_mi}. Redirigiendo...`
      setTimeout(() => {
        router.push('/sas/mis-tickets')
      }, 1400)
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Error al registrar el ticket.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadCatalogo()
})
</script>

<style scoped>
.auto-data-box {
  background-color: #f8fafc;
  border: 1px solid #94a3b8;
  border-radius: 6px;
  padding: 16px;
}

.data-label {
  display: block;
  font-size: 0.75rem;
  color: #475569;
  font-weight: 600;
  margin-bottom: 2px;
}

.data-value {
  font-size: 0.875rem;
  color: #0f172a;
}

.selected-file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background-color: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.8125rem;
}
</style>
