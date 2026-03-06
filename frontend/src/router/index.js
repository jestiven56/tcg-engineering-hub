import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { public: true }
  },
  {
    path: '/',
    name: 'projects',
    component: () => import('@/views/ProjectsView.vue'),
  },
  {
    path: '/projects/:id',
    name: 'project-detail',
    component: () => import('@/views/ProjectDetailView.vue'),
  },
  {
    path: '/projects/:projectId/artifacts/:artifactId',
    name: 'artifact-detail',
    component: () => import('@/views/ArtifactDetailView.vue'),
  },
  {
    path: '/projects/:projectId/modules/:moduleId',
    name: 'module-detail',
    component: () => import('@/views/ModuleDetailView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Guard: si no está autenticado, redirige al login
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.public) return true

  if (!auth.isAuthenticated) return { name: 'login' }

  // Si tenemos token pero no user, lo cargamos
  if (!auth.user) {
    try {
      await auth.fetchUser()
    } catch {
      return { name: 'login' }
    }
  }

  return true
})

export default router