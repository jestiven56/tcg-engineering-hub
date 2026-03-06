<template>
  <div>
    <AppNav />
    <div class="container">
      <div class="page-header">
        <h2>Proyectos</h2>
        <button v-if="auth.canEdit" @click="showForm = true" class="btn-primary">
          + Nuevo proyecto
        </button>
      </div>

      <!-- Formulario crear proyecto -->
      <div v-if="showForm" class="card form-card">
        <h3>Nuevo Proyecto</h3>
        <div class="form-group">
          <label>Nombre</label>
          <input v-model="newProject.name" placeholder="Nombre del proyecto" />
        </div>
        <div class="form-group">
          <label>Cliente</label>
          <input v-model="newProject.client_name" placeholder="Nombre del cliente" />
        </div>
        <div v-if="formError" class="error-msg">{{ formError }}</div>
        <div class="form-actions">
          <button @click="handleCreate" :disabled="creating" class="btn-primary">
            {{ creating ? 'Creando...' : 'Crear' }}
          </button>
          <button @click="showForm = false" class="btn-secondary">Cancelar</button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading">Cargando proyectos...</div>

      <!-- Lista -->
      <div v-else-if="projects.length === 0" class="empty">No hay proyectos aún.</div>

      <div v-else class="projects-grid">
        <div
          v-for="project in projects"
          :key="project.id"
          class="card project-card"
          @click="goToProject(project.id)"
        >
          <div class="project-header">
            <h3>{{ project.name }}</h3>
            <StatusBadge :status="project.status" />
          </div>
          <p class="client">{{ project.client_name }}</p>
          <p class="meta">Creado por {{ project.creator?.name }}</p>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="totalPages > 1" class="pagination">
        <button :disabled="page === 1" @click="loadProjects(page - 1)">←</button>
        <span>{{ page }} / {{ totalPages }}</span>
        <button :disabled="page === totalPages" @click="loadProjects(page + 1)">→</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { getProjects, createProject } from '@/api/projects'
import AppNav from '@/components/AppNav.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const router   = useRouter()
const auth     = useAuthStore()

const projects   = ref([])
const loading    = ref(false)
const page       = ref(1)
const totalPages = ref(1)
const showForm   = ref(false)
const creating   = ref(false)
const formError  = ref(null)
const newProject = ref({ name: '', client_name: '' })

const loadProjects = async (p = 1) => {
  loading.value = true
  try {
    const { data } = await getProjects(p)
    projects.value = data.data.data
    page.value     = data.data.current_page
    totalPages.value = data.data.last_page
  } finally {
    loading.value = false
  }
}

const handleCreate = async () => {
  formError.value = null
  creating.value  = true
  try {
    await createProject(newProject.value)
    showForm.value = false
    newProject.value = { name: '', client_name: '' }
    await loadProjects()
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Error al crear proyecto.'
  } finally {
    creating.value = false
  }
}

const goToProject = (id) => router.push(`/projects/${id}`)

onMounted(() => loadProjects())
</script>

<style scoped>
.container { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.projects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.project-card { cursor: pointer; transition: box-shadow 0.2s; }
.project-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
.project-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
.project-header h3 { margin: 0; font-size: 1rem; color: #1e293b; }
.client { color: #64748b; font-size: 0.9rem; margin: 0.25rem 0; }
.meta   { color: #94a3b8; font-size: 0.8rem; margin: 0; }
.form-card { margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.875rem; color: #475569; margin-bottom: 0.4rem; }
.form-group input { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; }
.form-actions { display: flex; gap: 0.75rem; }
.btn-primary  { background: #3b82f6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; }
.btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.loading, .empty { color: #94a3b8; padding: 2rem 0; text-align: center; }
.error-msg { background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
.pagination { display: flex; gap: 1rem; align-items: center; justify-content: center; margin-top: 1.5rem; }
.pagination button { padding: 0.3rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; }
.pagination button:disabled { opacity: 0.4; cursor: not-allowed; }
</style>