<template>
  <div class="page-body">
    <div class="card">

      <div v-if="loading" style="padding: 24px; text-align: center; color: #64748b;">
        Cargando publicaciones...
      </div>

      <div v-else-if="publicaciones.length === 0" style="padding: 32px; text-align: center; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px;">
        No hay publicaciones disponibles en este momento.
      </div>

      <div v-else style="display: flex; flex-direction: column; gap: 20px;">
        <div v-for="pub in publicaciones" :key="pub.id" class="pub-card" :class="{ 'pub-desactivada': pub.estado === 'Desactivada' }">
          <div class="pub-header">
            <div style="display: flex; align-items: center; gap: 10px;">
              <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">
                {{ pub.nombre_aplicativo }} - {{ pub.version }}
              </h3>
              <span class="badge" :class="pub.estado === 'Activa' ? 'badge-activa' : 'badge-desactivada'">
                {{ pub.estado }}
              </span>
            </div>
            <span style="font-size: 0.8125rem; color: #64748b;">
              Publicado el: {{ formatFecha(pub.fecha_publicacion) }}
            </span>
          </div>

          <!-- Indicaciones -->
          <div style="margin-bottom: 14px; font-size: 0.875rem; color: #334155; white-space: pre-wrap;">
            {{ pub.indicaciones }}
          </div>

          <!-- Enlaces Generales -->
          <div v-if="pub.enlaces_generales" style="margin-bottom: 14px; padding: 10px 14px; background-color: #f1f5f9; border-radius: 6px; font-size: 0.8125rem;">
            <strong style="color: #0f172a;">Enlace general de descarga / documentación:</strong>
            <a :href="pub.enlaces_generales" target="_blank" rel="noopener noreferrer" style="margin-left: 8px; word-break: break-all;">
              {{ pub.enlaces_generales }}
            </a>
          </div>

          <!-- TABLA DE CREDENCIALES / INFORMACION PERSONALIZADA DEL USUARIO SAS -->
          <div v-if="pub.estado === 'Activa' && pub.credenciales_personalizadas && pub.credenciales_personalizadas.length > 0" style="margin-top: 14px;">
            <h4 style="font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 8px;">
              Información y Credenciales Asignadas a su Usuario ({{ auth.user?.cod_usuario }}):
            </h4>

            <div class="table-container" style="max-width: 600px;">
              <table class="table">
                <thead>
                  <tr>
                    <th>Parámetro / Descripción</th>
                    <th>Valor / Clave / Enlace</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in pub.credenciales_personalizadas" :key="idx">
                    <td><strong>{{ item.label }}</strong></td>
                    <td>
                      <template v-if="isUrl(item.value)">
                        <a :href="item.value" target="_blank" rel="noopener noreferrer" style="word-break: break-all;">
                          {{ item.value }}
                        </a>
                      </template>
                      <template v-else>
                        <code>{{ item.value }}</code>
                      </template>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else-if="pub.estado === 'Desactivada'" style="padding: 10px 14px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem; color: #64748b;">
            Esta publicación ha sido desactivada. Las credenciales de acceso han dejado de estar disponibles y se conservan como registro histórico.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const auth = useAuthStore()
const publicaciones = ref([])
const loading = ref(false)

const fetchMisPublicaciones = async () => {
  loading.value = true
  try {
    const res = await api.get('/publicaciones/mis-publicaciones')
    if (res.data.success) {
      publicaciones.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const formatFecha = (str) => {
  if (!str) return '-'
  const d = new Date(str)
  return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const isUrl = (str) => {
  if (!str) return false
  return str.startsWith('http://') || str.startsWith('https://')
}

onMounted(() => {
  fetchMisPublicaciones()
})
</script>

<style scoped>
.pub-card {
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 20px;
}

.pub-desactivada {
  background-color: #f8fafc;
  opacity: 0.85;
}

.pub-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 8px;
}
</style>
