<template>
  <div class="page-body">
    <div class="card">
      <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button @click="abrirModalCategoria(null)" class="btn btn-primary btn-sm">
          <Plus :size="15" />
          <span>+ Nueva Categoría</span>
        </button>
      </div>

      <div v-if="loading" style="padding: 24px; text-align: center; color: #64748b;">
        Cargando catálogo...
      </div>

      <div v-else style="display: flex; flex-direction: column; gap: 20px;">
        <div v-for="cat in categorias" :key="cat.id" class="category-block">
          <!-- Cabecera de la Categoria -->
          <div class="category-header">
            <div style="display: flex; align-items: center; gap: 10px;">
              <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">{{ cat.nombre }}</h3>
              <span class="badge" :class="cat.activo ? 'badge-cerrado' : 'badge-desactivada'">
                {{ cat.activo ? 'Activa' : 'Desactivada' }}
              </span>
            </div>

            <div style="display: flex; gap: 6px;">
              <button @click="abrirModalTipo(cat.id, null)" class="btn btn-secondary btn-sm" :disabled="!cat.activo">
                <Plus :size="13" />
                <span>+ Tipo</span>
              </button>
              <button @click="abrirModalCategoria(cat)" class="btn btn-secondary btn-sm">
                <Edit2 :size="13" />
                <span>Editar</span>
              </button>
              <button @click="toggleCategoria(cat)" class="btn btn-sm" :class="cat.activo ? 'btn-danger' : 'btn-success'">
                <Power :size="13" />
                <span>{{ cat.activo ? 'Desactivar' : 'Activar' }}</span>
              </button>
            </div>
          </div>

          <!-- Tabla de Tipos -->
          <div class="table-container" style="border: none;">
            <table class="table">
              <thead>
                <tr>
                  <th>Tipo de Atención</th>
                  <th style="width: 140px;">Estado</th>
                  <th style="width: 180px; text-align: center;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!cat.tipos || cat.tipos.length === 0">
                  <td colspan="3" style="text-align: center; color: #94a3b8; padding: 16px;">
                    No hay tipos de atención registrados para esta categoría.
                  </td>
                </tr>
                <tr v-for="tipo in cat.tipos" :key="tipo.id">
                  <td>{{ tipo.nombre }}</td>
                  <td>
                    <span class="badge" :class="tipo.activo ? 'badge-cerrado' : 'badge-desactivada'">
                      {{ tipo.activo ? 'Activo' : 'Desactivado' }}
                    </span>
                  </td>
                  <td style="text-align: center;">
                    <div style="display: flex; gap: 6px; justify-content: center;">
                      <button @click="abrirModalTipo(cat.id, tipo)" class="btn btn-secondary btn-sm" style="padding: 2px 8px;">
                        <Edit2 :size="12" />
                        <span>Editar</span>
                      </button>
                      <button @click="toggleTipo(tipo)" class="btn btn-sm" :class="tipo.activo ? 'btn-danger' : 'btn-success'" style="padding: 2px 8px;">
                        <Power :size="12" />
                        <span>{{ tipo.activo ? 'Desactivar' : 'Activar' }}</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Categoria -->
    <div v-if="modalCategoriaOpen" class="modal-backdrop" @click.self="modalCategoriaOpen = false">
      <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
          <h3 class="card-title">{{ categoriaEdit ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
          <button @click="modalCategoriaOpen = false" class="btn btn-secondary btn-sm">
            <X :size="14" />
          </button>
        </div>
        <form @submit.prevent="guardarCategoria">
          <div class="form-group">
            <label class="form-label">Nombre de la Categoría: *</label>
            <input v-model="nombreCategoriaModal" type="text" class="form-control" placeholder="Ej: Sistema Integrado" required />
          </div>
          <div class="modal-footer">
            <button type="button" @click="modalCategoriaOpen = false" class="btn btn-secondary">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <Save :size="14" />
              <span>{{ saving ? 'Guardando...' : 'Guardar' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tipo -->
    <div v-if="modalTipoOpen" class="modal-backdrop" @click.self="modalTipoOpen = false">
      <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
          <h3 class="card-title">{{ tipoEdit ? 'Editar Tipo' : 'Nuevo Tipo' }}</h3>
          <button @click="modalTipoOpen = false" class="btn btn-secondary btn-sm">
            <X :size="14" />
          </button>
        </div>
        <form @submit.prevent="guardarTipo">
          <div class="form-group">
            <label class="form-label">Nombre del Tipo de Atención: *</label>
            <input v-model="nombreTipoModal" type="text" class="form-control" placeholder="Ej: Aplicadores - Preselección" required />
          </div>
          <div class="modal-footer">
            <button type="button" @click="modalTipoOpen = false" class="btn btn-secondary">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <Save :size="14" />
              <span>{{ saving ? 'Guardando...' : 'Guardar' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { Plus, Edit2, Power, X, Save } from 'lucide-vue-next'

const categorias = ref([])
const loading = ref(false)
const saving = ref(false)

const modalCategoriaOpen = ref(false)
const categoriaEdit = ref(null)
const nombreCategoriaModal = ref('')

const modalTipoOpen = ref(false)
const tipoEdit = ref(null)
const idCategoriaParaTipo = ref(null)
const nombreTipoModal = ref('')

const loadCatalogos = async () => {
  loading.value = true
  try {
    const res = await api.get('/catalogo?solo_activos=false')
    if (res.data.success) {
      categorias.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const abrirModalCategoria = (cat) => {
  categoriaEdit.value = cat
  nombreCategoriaModal.value = cat ? cat.nombre : ''
  modalCategoriaOpen.value = true
}

const guardarCategoria = async () => {
  saving.value = true
  try {
    if (categoriaEdit.value) {
      await api.put(`/catalogo/categorias/${categoriaEdit.value.id}`, { nombre: nombreCategoriaModal.value })
    } else {
      await api.post('/catalogo/categorias', { nombre: nombreCategoriaModal.value, activo: true })
    }
    modalCategoriaOpen.value = false
    loadCatalogos()
  } catch (err) {
    alert(err.response?.data?.message || 'Error al guardar categoría')
  } finally {
    saving.value = false
  }
}

const toggleCategoria = async (cat) => {
  try {
    await api.patch(`/catalogo/categorias/${cat.id}/toggle`)
    loadCatalogos()
  } catch (err) {
    alert(err.response?.data?.message || 'Error al cambiar estado de categoría')
  }
}

const abrirModalTipo = (catId, tipo) => {
  idCategoriaParaTipo.value = catId
  tipoEdit.value = tipo
  nombreTipoModal.value = tipo ? tipo.nombre : ''
  modalTipoOpen.value = true
}

const guardarTipo = async () => {
  saving.value = true
  try {
    if (tipoEdit.value) {
      await api.put(`/catalogo/tipos/${tipoEdit.value.id}`, {
        id_categoria: idCategoriaParaTipo.value,
        nombre: nombreTipoModal.value
      })
    } else {
      await api.post('/catalogo/tipos', {
        id_categoria: idCategoriaParaTipo.value,
        nombre: nombreTipoModal.value,
        activo: true
      })
    }
    modalTipoOpen.value = false
    loadCatalogos()
  } catch (err) {
    alert(err.response?.data?.message || 'Error al guardar tipo')
  } finally {
    saving.value = false
  }
}

const toggleTipo = async (tipo) => {
  try {
    await api.patch(`/catalogo/tipos/${tipo.id}/toggle`)
    loadCatalogos()
  } catch (err) {
    alert(err.response?.data?.message || 'Error al cambiar estado del tipo')
  }
}

onMounted(() => {
  loadCatalogos()
})
</script>

<style scoped>
.category-block {
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  overflow: hidden;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 18px;
  background-color: #f8fafc;
  border-bottom: 1px solid #cbd5e1;
  flex-wrap: wrap;
  gap: 10px;
}
</style>
