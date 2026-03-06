<template>
  <div>
    <AppNav />
    <div class="container" v-if="artifact">

      <!-- Header -->
      <div class="page-header">
        <div>
          <router-link :to="`/projects/${projectId}`" class="back-link">← Proyecto</router-link>
          <h2>{{ typeLabel(artifact.type) }}</h2>
        </div>
        <div class="header-right">
          <StatusBadge :status="artifact.status" />
        </div>
      </div>

      <!-- Blocked reason -->
      <BlockedReason
        v-if="artifact.blocked_reason"
        :reasons="[artifact.blocked_reason]"
      />

      <!-- Cambiar status -->
      <div v-if="auth.canEdit" class="card status-card">
        <label>Cambiar estado</label>
        <div class="status-row">
          <select v-model="selectedStatus" class="status-select">
            <option value="not_started">Sin iniciar</option>
            <option value="in_progress">En progreso</option>
            <option value="blocked">Bloqueado</option>
            <option value="done">Completado</option>
          </select>
          <button @click="handleStatusChange" :disabled="savingStatus" class="btn-primary">
            {{ savingStatus ? 'Guardando...' : 'Actualizar estado' }}
          </button>
        </div>
        <div v-if="statusError" class="error-msg">{{ statusError }}</div>
        <BlockedReason v-if="statusErrors.length" :reasons="statusErrors" />
      </div>

      <!-- Formulario de contenido según tipo -->
      <div class="card content-card">
        <div class="card-header">
          <h3>Contenido</h3>
          <div class="header-actions">
            <span v-if="auth.canEdit" class="owner-label">
              Responsable:
              <select v-model="selectedOwner" class="owner-select">
                <option :value="null">Sin asignar</option>
                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </span>
          </div>
        </div>

        <!-- Strategic Alignment -->
        <div v-if="artifact.type === 'strategic_alignment'" class="fields">
          <div class="form-group">
            <label>Transformación</label>
            <textarea v-model="content.transformation" :disabled="!auth.canEdit" rows="3" />
          </div>
          <div class="form-group">
            <label>Decisiones soportadas</label>
            <ArrayInput v-model="content.supported_decisions" :readonly="!auth.canEdit" />
          </div>
          <div class="form-group">
            <label>Éxito medible (métrica → objetivo)</label>
            <MetricInput v-model="content.measurable_success" :readonly="!auth.canEdit" />
          </div>
          <div class="form-group">
            <label>Fuera de alcance</label>
            <ArrayInput v-model="content.out_of_scope" :readonly="!auth.canEdit" />
          </div>
        </div>

        <!-- Big Picture -->
        <div v-else-if="artifact.type === 'big_picture'" class="fields">
          <div class="form-group">
            <label>Visión del ecosistema</label>
            <textarea v-model="content.ecosystem_vision" :disabled="!auth.canEdit" rows="3" />
          </div>
          <div class="form-group">
            <label>Dominios impactados</label>
            <ArrayInput v-model="content.impacted_domains" :readonly="!auth.canEdit" />
          </div>
          <div class="form-group">
            <label>Definición de éxito</label>
            <textarea v-model="content.success_definition" :disabled="!auth.canEdit" rows="3" />
          </div>
        </div>

        <!-- Domain Breakdown -->
        <div v-else-if="artifact.type === 'domain_breakdown'" class="fields">
          <div class="form-group">
            <label>Dominios</label>
            <DomainInput v-model="content.domains" :readonly="!auth.canEdit" :users="users" />
          </div>
        </div>

        <!-- Module Matrix -->
        <div v-else-if="artifact.type === 'module_matrix'" class="fields">
          <div class="form-group">
            <label>Módulos</label>
            <ModuleMatrixInput v-model="content.modules_overview" :readonly="!auth.canEdit" />
          </div>
        </div>

        <!-- System Architecture -->
        <div v-else-if="artifact.type === 'system_architecture'" class="fields">
          <div class="form-group">
            <label>Modelo de autenticación</label>
            <textarea v-model="content.auth_model" :disabled="!auth.canEdit" rows="2" />
          </div>
          <div class="form-group">
            <label>Estilo de API</label>
            <input v-model="content.api_style" :disabled="!auth.canEdit" />
          </div>
          <div class="form-group">
            <label>Notas del modelo de datos</label>
            <textarea v-model="content.data_model_notes" :disabled="!auth.canEdit" rows="3" />
          </div>
          <div class="form-group">
            <label>Notas de escalabilidad</label>
            <textarea v-model="content.scalability_notes" :disabled="!auth.canEdit" rows="3" />
          </div>
        </div>

        <!-- Phase Scope -->
        <div v-else-if="artifact.type === 'phase_scope'" class="fields">
          <div class="form-group">
            <label>Módulos incluidos (IDs)</label>
            <ArrayInput v-model="content.included_modules" :readonly="!auth.canEdit" />
          </div>
          <div class="form-group">
            <label>Items excluidos</label>
            <ArrayInput v-model="content.excluded_items" :readonly="!auth.canEdit" />
          </div>
          <div class="form-group">
            <label>Criterios de aceptación</label>
            <ArrayInput v-model="content.acceptance_criteria" :readonly="!auth.canEdit" />
          </div>
        </div>

        <!-- Module Engineering (texto libre) -->
        <div v-else class="fields">
          <div class="form-group">
            <label>Contenido</label>
            <textarea v-model="content.notes" :disabled="!auth.canEdit" rows="6" />
          </div>
        </div>

        <!-- Guardar -->
        <div v-if="auth.canEdit" class="form-actions">
          <button @click="handleSave" :disabled="saving" class="btn-primary">
            {{ saving ? 'Guardando...' : '💾 Guardar cambios' }}
          </button>
          <span v-if="saveSuccess" class="success-msg">✅ Guardado</span>
        </div>
        <div v-if="saveError" class="error-msg">{{ saveError }}</div>
      </div>

    </div>
    <div v-else class="loading container">Cargando artefacto...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { getArtifact, updateArtifact, updateArtifactStatus } from '@/api/artifacts'
import AppNav from '@/components/AppNav.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import BlockedReason from '@/components/BlockedReason.vue'
import ArrayInput from '@/components/ArrayInput.vue'
import MetricInput from '@/components/MetricInput.vue'
import DomainInput from '@/components/DomainInput.vue'
import ModuleMatrixInput from '@/components/ModuleMatrixInput.vue'
import api from '@/api/axios'

const route     = useRoute()
const auth      = useAuthStore()
const projectId = route.params.projectId
const artifactId = route.params.artifactId

const artifact       = ref(null)
const content        = ref({})
const selectedStatus = ref('')
const selectedOwner  = ref(null)
const users          = ref([])
const saving         = ref(false)
const savingStatus   = ref(false)
const saveSuccess    = ref(false)
const saveError      = ref(null)
const statusError    = ref(null)
const statusErrors   = ref([])

const typeLabel = (type) => ({
  strategic_alignment: 'Strategic Alignment',
  big_picture:         'Big Picture',
  domain_breakdown:    'Domain Breakdown',
  module_matrix:       'Module Matrix',
  module_engineering:  'Module Engineering',
  system_architecture: 'System Architecture',
  phase_scope:         'Phase Scope',
}[type] ?? type)

const loadArtifact = async () => {
  const { data } = await getArtifact(projectId, artifactId)
  artifact.value       = data.data
  content.value        = data.data.content_json ?? {}
  selectedStatus.value = data.data.status
  selectedOwner.value  = data.data.owner_user_id
}

const loadUsers = async () => {
  const { data } = await api.get('/users')
  users.value = data.data ?? []
}

const handleSave = async () => {
  saving.value     = true
  saveError.value  = null
  saveSuccess.value = false
  try {
    await updateArtifact(projectId, artifactId, {
      content_json:  content.value,
      owner_user_id: selectedOwner.value,
    })
    saveSuccess.value = true
    setTimeout(() => saveSuccess.value = false, 3000)
  } catch (e) {
    saveError.value = e.response?.data?.message ?? 'Error al guardar.'
  } finally {
    saving.value = false
  }
}

const handleStatusChange = async () => {
  savingStatus.value = true
  statusError.value  = null
  statusErrors.value = []
  try {
    await updateArtifactStatus(projectId, artifactId, selectedStatus.value)
    await loadArtifact()
  } catch (e) {
    statusErrors.value = e.response?.data?.errors ?? []
    statusError.value  = e.response?.data?.message ?? 'Error al cambiar estado.'
    selectedStatus.value = artifact.value.status
  } finally {
    savingStatus.value = false
  }
}

onMounted(async () => {
  await loadArtifact()
  await loadUsers()
})
</script>

<style scoped>
.container { max-width: 860px; margin: 2rem auto; padding: 0 1.5rem; }
.back-link { color: #3b82f6; text-decoration: none; font-size: 0.9rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.header-right { display: flex; gap: 0.75rem; align-items: center; }
.card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 1rem; }
.status-card label { font-size: 0.875rem; color: #475569; display: block; margin-bottom: 0.5rem; }
.status-row { display: flex; gap: 0.75rem; align-items: center; }
.status-select { padding: 0.5rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.card-header h3 { margin: 0; }
.owner-label { font-size: 0.875rem; color: #475569; display: flex; align-items: center; gap: 0.5rem; }
.owner-select { padding: 0.3rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; }
.fields { display: flex; flex-direction: column; gap: 1rem; }
.form-group label { display: block; font-size: 0.875rem; color: #475569; margin-bottom: 0.4rem; font-weight: 500; }
.form-group input,
.form-group textarea { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; font-family: inherit; resize: vertical; }
.form-group input:disabled,
.form-group textarea:disabled { background: #f8fafc; color: #64748b; }
.form-actions { display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem; }
.btn-primary { background: #3b82f6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.success-msg { color: #15803d; font-size: 0.9rem; }
.error-msg { background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 6px; margin-top: 0.75rem; font-size: 0.9rem; }
.loading { color: #94a3b8; padding: 2rem 0; text-align: center; }
</style>