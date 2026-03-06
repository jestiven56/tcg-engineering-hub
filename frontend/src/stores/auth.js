import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login as loginApi, logout as logoutApi, me } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)

  // Helpers de permisos
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const isPm       = computed(() => user.value?.role === 'pm')
  const isEngineer = computed(() => user.value?.role === 'engineer')
  const isViewer   = computed(() => user.value?.role === 'viewer')

  const canEdit    = computed(() => ['admin', 'pm'].includes(user.value?.role))
  const canEditModules = computed(() => ['admin', 'pm', 'engineer'].includes(user.value?.role))

  async function login(credentials) {
    const { data } = await loginApi(credentials)
    token.value = data.data.token
    user.value  = data.data.user
    localStorage.setItem('token', token.value)
  }

  async function fetchUser() {
    const { data } = await me()
    user.value = data.data
  }

  async function logout() {
    await logoutApi()
    token.value = null
    user.value  = null
    localStorage.removeItem('token')
  }

  return {
    user, token, isAuthenticated,
    isAdmin, isPm, isEngineer, isViewer,
    canEdit, canEditModules,
    login, logout, fetchUser
  }
})