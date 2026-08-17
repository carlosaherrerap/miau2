<template>
  <div class="page-body">
    <div class="card">
      <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button @click="loadData" class="btn btn-secondary btn-sm" :disabled="loading">
          <RefreshCw :size="14" :class="{ 'spin-icon': loading }" />
          <span>Actualizar</span>
        </button>
      </div>

      <div v-if="loading" style="padding: 32px; text-align: center; color: #64748b;">
        Cargando indicadores gerenciales...
      </div>

      <div v-else>
        <!-- 1. TARJETAS DE INDICADORES PRINCIPALES -->
        <div class="grid-4" style="margin-bottom: 24px;">
          <div class="stat-card">
            <span class="stat-title">Total Registrados</span>
            <span class="stat-value">{{ data.resumen?.total || 0 }}</span>
            <span class="stat-sub">100% de la carga total</span>
          </div>

          <div class="stat-card">
            <span class="stat-title">Tickets Abiertos</span>
            <span class="stat-value" style="color: #b45309;">{{ data.resumen?.abiertos || 0 }}</span>
            <span class="stat-sub">{{ calcPorcentaje(data.resumen?.abiertos, data.resumen?.total) }}% pendientes</span>
          </div>

          <div class="stat-card">
            <span class="stat-title">Tickets Cerrados</span>
            <span class="stat-value" style="color: #15803d;">{{ data.resumen?.cerrados || 0 }}</span>
            <span class="stat-sub">{{ calcPorcentaje(data.resumen?.cerrados, data.resumen?.total) }}% resueltos</span>
          </div>

          <div class="stat-card">
            <span class="stat-title">Tiempo Promedio</span>
            <span class="stat-value" style="color: #1e40af;">{{ data.resumen?.tiempo_promedio_resolucion || '00:00' }}</span>
            <span class="stat-sub">Formato hh:mm</span>
          </div>
        </div>

        <!-- 2. TICKETS POR MONITOR INFORMATICO Y POR SEDE REGIONAL -->
        <div class="grid-2" style="margin-bottom: 24px;">
          <!-- Por Monitor -->
          <div class="sub-panel">
            <h3 class="panel-heading">Carga por Monitor Informático</h3>
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Monitor</th>
                    <th>Total</th>
                    <th>Abiertos</th>
                    <th>Cerrados</th>
                    <th>No Procede</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(m, idx) in data.por_monitor" :key="idx">
                    <td>
                      <strong>{{ m.monitor_cod }}</strong>
                      <div style="font-size: 0.75rem; color: #64748b;">{{ m.monitor_nombre }}</div>
                    </td>
                    <td><strong>{{ m.total }}</strong></td>
                    <td style="color: #b45309;">{{ m.abiertos }}</td>
                    <td style="color: #15803d;">{{ m.cerrados }}</td>
                    <td style="color: #b91c1c;">{{ m.no_procede }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Por Sede Regional -->
          <div class="sub-panel">
            <h3 class="panel-heading">Distribución por Sede Regional</h3>
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Sede Regional</th>
                    <th>Total</th>
                    <th>% Participación</th>
                    <th>Distribución</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(sr, idx) in data.por_sede_regional" :key="idx">
                    <td><strong>{{ sr.sede_regional }}</strong></td>
                    <td>{{ sr.total }}</td>
                    <td>{{ calcPorcentaje(sr.total, data.resumen?.total) }}%</td>
                    <td style="width: 120px;">
                      <div class="progress-bar-bg">
                        <div class="progress-bar-fill" :style="{ width: calcPorcentaje(sr.total, data.resumen?.total) + '%' }"></div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 3. TICKETS POR CATEGORIA Y TIPO DE ATENCION -->
        <div class="grid-2" style="margin-bottom: 24px;">
          <!-- Por Categoria -->
          <div class="sub-panel">
            <h3 class="panel-heading">Tickets por Categoría</h3>
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>% Total</th>
                    <th>Barra</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(cat, idx) in data.por_categoria" :key="idx">
                    <td><strong>{{ cat.categoria }}</strong></td>
                    <td>{{ cat.total }}</td>
                    <td>{{ calcPorcentaje(cat.total, data.resumen?.total) }}%</td>
                    <td style="width: 100px;">
                      <div class="progress-bar-bg">
                        <div class="progress-bar-fill" :style="{ width: calcPorcentaje(cat.total, data.resumen?.total) + '%' }"></div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Por Tipo -->
          <div class="sub-panel">
            <h3 class="panel-heading">Tickets por Tipo Específico</h3>
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Categoría</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(tipo, idx) in data.por_tipo" :key="idx">
                    <td><small style="color: #64748b;">{{ tipo.categoria }}</small></td>
                    <td><strong>{{ tipo.tipo }}</strong></td>
                    <td>{{ tipo.total }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 4. EVOLUCION TEMPORAL POR PERIODO -->
        <div class="sub-panel">
          <h3 class="panel-heading">Evolución de Tickets por Fecha</h3>
          <div class="table-container">
            <table class="table">
              <thead>
                <tr>
                  <th>Fecha de Registro</th>
                  <th>Total Nuevos</th>
                  <th>Abiertos</th>
                  <th>Cerrados</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(ev, idx) in data.evolucion_temporal" :key="idx">
                  <td><strong>{{ ev.fecha }}</strong></td>
                  <td>{{ ev.total }}</td>
                  <td style="color: #b45309;">{{ ev.abiertos }}</td>
                  <td style="color: #15803d;">{{ ev.cerrados }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { RefreshCw } from 'lucide-vue-next'

const loading = ref(false)
const data = ref({
  resumen: {},
  por_monitor: [],
  por_sede_regional: [],
  por_categoria: [],
  por_tipo: [],
  evolucion_temporal: []
})

const loadData = async () => {
  loading.value = true
  try {
    const res = await api.get('/dashboard/gerencial')
    if (res.data.success) {
      data.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const calcPorcentaje = (val, total) => {
  if (!total || total === 0 || !val) return 0
  return Math.round((val / total) * 100)
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.stat-card {
  padding: 16px;
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
}

.stat-title {
  font-size: 0.8125rem;
  font-weight: 700;
  color: #475569;
}

.stat-value {
  font-size: 1.85rem;
  font-weight: 700;
  color: #0f172a;
  margin: 4px 0;
  line-height: 1.1;
}

.stat-sub {
  font-size: 0.75rem;
  color: #64748b;
}

.sub-panel {
  padding: 16px;
  background-color: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
}

.panel-heading {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 12px;
}

.progress-bar-bg {
  width: 100%;
  height: 8px;
  background-color: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background-color: #1e40af;
  border-radius: 4px;
}

.spin-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
