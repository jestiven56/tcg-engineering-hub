<template>
  <div>
    <AppNav />
    <div class="container" v-if="module">

      <!-- Header -->
      <div class="page-header">
        <div>
          <router-link :to="`/projects/${projectId}`" class="back-link">← Proyecto</router-link>
          <h2>{{ module.name }}</h2>
          <span class="domain-tag">{{ module.domain }}</span>
        </div>
        <div class="header-right">
          <StatusBadge :status="module.status" />
          <button
            v-if="auth.canEditModules && module.status === 'draft'"
            @click="handleValidate"
            :disabled="!module.can_validate || validating"
            class="btn-validate"
          >
            {{ validating ? 'Validando...' : '✔ Validar módulo' }}
          </button>
        </div>
      </div>

      <!-- Razones de bloqueo para validación -->
      <BlockedReason
        v-if="module.block_reasons?.length"
        :reasons="module.block_reasons"
      />

      <!-- Error de validación -->
      <div v-if="validateError" class="error-msg">{{ validateError }}</div>

      <!-- Formulario -->
      <div class="card">
        <h3>Campos TCG</h3>

        <div class="form-group">
          <label>Objetivo <span class="required">*</span></label>
          <textarea v-model="form.objective" :disabled="!auth.canEditModules" rows="3" />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Inputs <span class="required">*</span></label>
            <ArrayInput v-model="form.inputs" :readonly="!auth.canEditModules" />
          </div>
          <div class="form-group">
            <label>Outputs <span class="required">*</span></label>
            <ArrayInput v-model="form.outputs" :readonly="!auth.canEditModules" />
          </div>
        </div>

        <div class="form-group">
          <label>Estructura de datos</label>
          <textarea v-model="form.data_structure" :disabled="!auth.canEditModules" rows="3" />
        </div>

        <div class="form-group">
          <label>Reglas de lógica</label>
          <textarea v-model="form.logic_rules" :disabled="!auth.canEditModules" rows="3" />
        </div>

        <div class="form-group">
          <label>Responsabilidad <span class="required">*</span></label>
          <input v-model="form.responsibility" :disabled="!auth.canEditModules" />
        </div>

        <div class="form-group">
          <label>Escenarios de fallo</label>
          <textarea v-model="form.failure_scenarios" :disabled="!auth.canEditModules" rows="3" />
        </div>

        <div class="form-group">
          <label>Requisitos de auditoría</label>
          <textarea v-model="form.audit_trail_requirements" :disabled="!auth.canEditModules" rows="2" />
        </div>

        <div class="form-group">
          <label>Dependencias (IDs de módulos)</label>
          <ArrayInput v-model="form.dependencies" :readonly="!auth.canEditModules" />
        </div>

        <div class="form-group">
          <label>Nota de versión</label>
          <input v-model="form.version_note" :disabled="!auth.canEditModules" placeholder="Ej: v1.0 - inicial" />
        </div>

        <!-- Guardar -->
        <div v-if="auth.canEditModules" class="form-actions">
          <button @click="handleSave" :disabled="saving" class="btn-primary">
            {{ saving ? 'Guardando...' : '💾 Guardar cambios' }}
          </button>
          <span v-if="saveSuccess" class="success-msg">✅ Guardado</span>
        </div>
        <div v-if="saveError" class="error-msg">{{ saveError }}</div>
      </div>

    </div>
    <div v-else class="loading container">Cargando módulo...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { getModule, updateModule, validateModule } from '@/api/modules'
import AppNav from '@/components/AppNav.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import BlockedReason from '@/components/BlockedReason.vue'
import ArrayInput from '@/components/ArrayInput.vue'

const route     = useRoute()
const auth      = useAuthStore()
const projectId = route.params.projectId
const moduleId  = route.params.moduleId

const module        = ref(null)
const saving        = ref(false)
const validating    = ref(false)
const saveSuccess   = ref(false)
const saveError     = ref(null)
const validateError = ref(null)

const form = ref({
  objective:                '',
  inputs:                   [],
  outputs:                  [],
  data_structure:           '',
  logic_rules:              '',
  responsibility:           '',
  failure_scenarios:        '',
  audit_trail_requirements: '',
  dependencies:             [],
  version_note:             '',
})

const loadModule = async () => {
  const { data } = await getModule(projectId, moduleId)
  module.value = data.data
  // Poblar el formulario con los datos existentes
  Object.keys(form.value).forEach(key => {
    form.value[key] = data.data[key] ?? form.value[key]
  })
}

const handleSave = async () => {
  saving.value    = true
  saveError.value = null
  saveSuccess.value = false
  try {
    await updateModule(projectId, moduleId, form.value)
    saveSuccess.value = true
    await loadModule() // recargar para actualizar can_validate
    setTimeout(() => saveSuccess.value = false, 3000)
  } catch (e) {
    saveError.value = e.response?.data?.message ?? 'Error al guardar.'
  } finally {
    saving.value = false
  }
}

const handleValidate = async () => {
  validating.value    = true
  validateError.value = null
  try {
    await validateModule(projectId, moduleId)
    await loadModule()
  } catch (e) {
    validateError.value = e.response?.data?.message ?? 'Error al validar.'
  } finally {
    validating.value = false
  }
}

onMounted(() => loadModule())
</script>

<style scoped>
.container { max-width: 860px; margin: 2rem auto; padding: 0 1.5rem; }
.back-link { color: #3b82f6; text-decoration: none; font-size: 0.9rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.domain-tag { background: #f1f5f9; color: #475569; padding: 0.2rem 0.75rem; border-radius: 999px; font-size: 0.8rem; }
.header-right { display: flex; gap: 0.75rem; align-items: center; }
.btn-validate { background: #8b5cf6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; }
.btn-validate:disabled { opacity: 0.5; cursor: not-allowed; }
.card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.card h3 { margin: 0 0 1.25rem; color: #1e293b; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.875rem; color: #475569; margin-bottom: 0.4rem; font-weight: 500; }
.form-group input,
.form-group textarea { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; font-family: inherit; resize: vertical; }
.form-group input:disabled,
.form-group textarea:disabled { background: #f8fafc; color: #64748b; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.required { color: #ef4444; }
.form-actions { display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; }
.btn-primary { background: #3b82f6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.success-msg { color: #15803d; font-size: 0.9rem; }
.error-msg { background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 6px; margin-top: 0.75rem; font-size: 0.9rem; }
.loading { color: #94a3b8; padding: 2rem 0; text-align: center; }
</style>