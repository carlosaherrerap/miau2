<template>
  <div class="page-body">
    <div class="card">

      <!-- Informacion y formato requerido -->
      <div class="alert alert-info">
        <strong>Estructura requerida del archivo CSV:</strong>
        <p style="margin-top: 4px; font-size: 0.8125rem;">
          Columnas requeridas separadas por comas (,):
        </p>
        <div style="margin-top: 6px; padding: 8px; background-color: #ffffff; border: 1px solid #bfdbfe; border-radius: 4px; font-family: monospace; font-size: 0.75rem; overflow-x: auto;">
          cod_usuario,username,clave,nombres,ape_pat,ape_mat,rol_cod,sede_reg_cod,sede_juris_cod,doc,email,codigo_monitor<br/>
          SAS101,sas101,Pass123,Juan,Perez,Gomez,SAS,REG-LIM,JUR-LIM-1,72819201,juan.perez@enla2026.gob.pe,<br/>
          MI-005,monitor5,Pass123,Maria,Rios,Vargas,MI,REG-AQP,,43981209,maria.rios@enla2026.gob.pe,MI5
        </div>
        <p style="margin-top: 6px; font-size: 0.75rem; color: #1e40af;">
          Roles válidos: <code>SAS</code>, <code>MI</code>, <code>EMI</code>, <code>CSMI</code>, <code>ECC</code>, <code>GUEST</code>.
        </p>
      </div>

      <!-- Formulario de subida -->
      <form @submit.prevent="subirCsvUsuarios" style="margin-top: 20px;">
        <div class="form-group">
          <label class="form-label">Archivo CSV de Usuarios: *</label>
          <input type="file" ref="fileInput" accept=".csv,.txt" class="form-control" required />
        </div>

        <div v-if="successMsg" class="alert alert-success">
          {{ successMsg }}
        </div>

        <div v-if="errores.length > 0" class="alert alert-warning">
          <strong>Advertencias encontradas:</strong>
          <ul style="margin-top: 4px; padding-left: 20px;">
            <li v-for="(err, idx) in errores" :key="idx">{{ err }}</li>
          </ul>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 8px;">
          <button type="submit" class="btn btn-primary" :disabled="uploading">
            <UploadCloud :size="15" />
            <span>{{ uploading ? 'Importando...' : 'Importar CSV' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import { UploadCloud } from 'lucide-vue-next'

const fileInput = ref(null)
const uploading = ref(false)
const successMsg = ref('')
const errores = ref([])

const subirCsvUsuarios = async () => {
  if (!fileInput.value?.files[0]) {
    alert('Por favor seleccione un archivo CSV.')
    return
  }

  uploading.value = true
  successMsg.value = ''
  errores.value = []

  try {
    const formData = new FormData()
    formData.append('archivo_csv', fileInput.value.files[0])

    const res = await api.post('/usuarios/carga-masiva', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (res.data.success) {
      successMsg.value = res.data.message
      errores.value = res.data.data?.errores || []
      if (fileInput.value) fileInput.value.value = ''
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Error al procesar carga masiva.')
  } finally {
    uploading.value = false
  }
}
</script>
