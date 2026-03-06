<template>
  <div>
    <AppNav />
    <div class="container" v-if="project">

      <!-- Header -->
      <div class="page-header">
        <div>
          <router-link to="/" class="back-link">← Proyectos</router-link>
          <h2>{{ project.name }}</h2>
          <p class="client">{{ project.client_name }}</p>
        </div>
        <div class="header-right">
          <StatusBadge :status="project.status" />
          <select
            v-if="auth.canEdit"
            v-model="selectedStatus"
            @change="handleStatusChange"
            class="status-select"
          >
            <option value="draft">Draft</option>
            <option value="discovery">Discovery</option>
            <option value="execution">Execution</option>
            <option value="delivered">Delivered</option>
          </select>
        </div>
      </div>

      <!-- Gate error al cambiar status -->
      <BlockedReason :reasons="statusErrors" />

      <!-- Tabs -->
      <div class="tabs">
        <button
          v-for="tab in tabs" :key="tab.key"
          :class="['tab', activeTab === tab.key && 'tab-active']"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab: Artefactos -->
      <div v-if="activeTab === 'artifacts'">
        <div v-if="loadingArtifacts" class="loading">Cargando artefactos...</div>
        <div v-else class="artifacts-list">
          <div
            v-for="artifact in artifacts"
            :key="artifact.id"
            class="card artifact-row"
          >
            <div class="artifact-info">
              <span class="artifact-type">{{ typeLabel(artifact.type) }}</span>
              <StatusBadge :status="artifact.status" />
            </div>
            <div class="artifact-meta">
              <span v-if="artifact.owner">👤 {{ artifact.owner.name }}</span>
              <span v-if="artifact.blocked_reason" class="blocked-hint">
                ⚠️ {{ artifact.blocked_reason }}
              </span>
            </div>
            <router-link
              :to="`/projects/${project.id}/artifacts/${artifact.id}`"
              class="btn-secondary btn-sm"
            >
              Ver / Editar →
            </router-link>
          </div>
        </div>
      </div>

      <!-- Tab: Módulos -->
      <div v-if="activeTab === 'modules'">
        <div class="tab-header">
          <button v-if="auth.canEditModules" @click="showModuleForm = true" class="btn-primary">
            + Nuevo módulo
          </button>
        </div>

        <!-- Formulario módulo -->
        <div v-if="showModuleForm" class="card form-card">
          <h3>Nuevo Módulo</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Nombre</label>
              <input v-model="newModule.name" placeholder="Nombre del módulo" />
            </div>
            <div class="form-group">
              <label>Dominio</label>
              <input v-model="newModule.domain" placeholder="Ej: Pagos, Auth..." />
            </div>
          </div>
          <div v-if="moduleError" class="error-msg">{{ moduleError }}</div>
          <div class="form-actions">
            <button @click="handleCreateModule" :disabled="creatingModule" class="btn-primary">
              {{ creatingModule ? 'Creando...' : 'Crear' }}
            </button>
            <button @click="showModuleForm = false" class="btn-secondary">Cancelar</button>
          </div>
        </div>

        <div v-if="loadingModules" class="loading">Cargando módulos...</div>
        <div v-else-if="modules.length === 0" class="empty">No hay módulos aún.</div>
        <div v-else class="modules-list">
          <div v-for="mod in modules" :key="mod.id" class="card module-row">
            <div class="module-info">
              <span class="module-name">{{ mod.name }}</span>
              <span class="module-domain">{{ mod.domain }}</span>
              <StatusBadge :status="mod.status" />
            </div>
            <router-link
              :to="`/projects/${project.id}/modules/${mod.id}`"
              class="btn-secondary btn-sm"
            >
              Ver / Editar →
            </router-link>
          </div>
        </div>
      </div>

      <!-- Tab: Auditoría -->
      <div v-if="activeTab === 'audit'">
        <AuditTimeline
          :events="auditEvents"
          :loading="loadingAudit"
          :page="auditPage"
          :total-pages="auditTotalPages"
          @page-change="loadAudit"
        />
      </div>

    </div>
    <div v-else class="loading container">Cargando proyecto...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { getProject, updateProjectStatus } from '@/api/projects'
import { getArtifacts } from '@/api/artifacts'
import { getModules, createModule } from '@/api/modules'
import { getAuditEvents } from '@/api/audit'
import AppNav from '@/components/AppNav.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import BlockedReason from '@/components/BlockedReason.vue'
import AuditTimeline from '@/components/AuditTimeline.vue'

const route = useRoute()
const auth  = useAuthStore()
const id    = route.params.id

const project        = ref(null)
const artifacts      = ref([])
const modules        = ref([])
const auditEvents    = ref([])
const activeTab      = ref('artifacts')
const selectedStatus = ref('')
const statusErrors   = ref([])
const loadingArtifacts = ref(false)
const loadingModules   = ref(false)
const loadingAudit     = ref(false)
const showModuleForm   = ref(false)
const creatingModule   = ref(false)
const moduleError      = ref(null)
const auditPage        = ref(1)
const auditTotalPages  = ref(1)
const newModule        = ref({ name: '', domain: '' })

const tabs = [
  { key: 'artifacts', label: '📄 Artefactos' },
  { key: 'modules',   label: '🧩 Módulos' },
  { key: 'audit',     label: '📋 Auditoría' },
]

const typeLabel = (type) => ({
  strategic_alignment: 'Strategic Alignment',
  big_picture:         'Big Picture',
  domain_breakdown:    'Domain Breakdown',
  module_matrix:       'Module Matrix',
  module_engineering:  'Module Engineering',
  system_architecture: 'System Architecture',
  phase_scope:         'Phase Scope',
}[type] ?? type)

const loadProject = async () => {
  const { data } = await getProject(id)
  project.value        = data.data
  selectedStatus.value = data.data.status
}

const loadArtifacts = async () => {
  loadingArtifacts.value = true
  try {
    const { data } = await getArtifacts(id)
    artifacts.value = data.data
  } finally {
    loadingArtifacts.value = false
  }
}

const loadModules = async () => {
  loadingModules.value = true
  try {
    const { data } = await getModules(id)
    modules.value = data.data.data
  } finally {
    loadingModules.value = false
  }
}

const loadAudit = async (p = 1) => {
  loadingAudit.value = true
  try {
    const { data } = await getAuditEvents(id, p)
    auditEvents.value   = data.data.data
    auditPage.value     = data.data.current_page
    auditTotalPages.value = data.data.last_page
  } finally {
    loadingAudit.value = false
  }
}

const handleStatusChange = async () => {
  statusErrors.value = []
  try {
    await updateProjectStatus(id, selectedStatus.value)
    await loadProject()
  } catch (e) {
    statusErrors.value = e.response?.data?.errors ?? [e.response?.data?.message]
    selectedStatus.value = project.value.status
  }
}

const handleCreateModule = async () => {
  moduleError.value  = null
  creatingModule.value = true
  try {
    await createModule(id, newModule.value)
    showModuleForm.value = false
    newModule.value = { name: '', domain: '' }
    await loadModules()
  } catch (e) {
    moduleError.value = e.response?.data?.message ?? 'Error al crear módulo.'
  } finally {
    creatingModule.value = false
  }
}

onMounted(async () => {
  await loadProject()
  await loadArtifacts()
  await loadModules()
  await loadAudit()
})
</script>

<style scoped>
.container { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
.back-link { color: #3b82f6; text-decoration: none; font-size: 0.9rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.client { color: #64748b; margin: 0.25rem 0; }
.header-right { display: flex; gap: 0.75rem; align-items: center; }
.status-select { padding: 0.4rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; }
.tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0; }
.tab { background: none; border: none; padding: 0.6rem 1.2rem; cursor: pointer; color: #64748b; border-bottom: 2px solid transparent; margin-bottom: -2px; }
.tab-active { color: #3b82f6; border-bottom-color: #3b82f6; font-weight: 600; }
.card { background: white; border-radius: 10px; padding: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.artifact-row, .module-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.artifact-info, .module-info { display: flex; gap: 0.75rem; align-items: center; flex: 1; }
.artifact-type { font-weight: 600; color: #1e293b; font-size: 0.9rem; }
.artifact-meta { color: #94a3b8; font-size: 0.8rem; flex: 1; }
.blocked-hint  { color: #c2410c; }
.module-name   { font-weight: 600; color: #1e293b; }
.module-domain { color: #64748b; font-size: 0.85rem; }
.tab-header { margin-bottom: 1rem; }
.form-card { margin-bottom: 1rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.875rem; color: #475569; margin-bottom: 0.4rem; }
.form-group input { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; }
.form-actions { display: flex; gap: 0.75rem; }
.btn-primary  { background: #3b82f6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; }
.btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; text-decoration: none; }
.btn-sm { padding: 0.3rem 0.8rem; font-size: 0.85rem; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.loading, .empty { color: #94a3b8; padding: 2rem 0; text-align: center; }
.error-msg { background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
</style>