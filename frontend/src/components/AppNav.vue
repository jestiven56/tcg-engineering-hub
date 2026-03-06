<template>
  <nav class="navbar">
    <div class="navbar-brand">
      <router-link to="/">TCG Engineering Hub</router-link>
    </div>
    <div class="navbar-user">
      <span class="user-role">{{ auth.user?.role }}</span>
      <span class="user-name">{{ auth.user?.name }}</span>
      <button @click="handleLogout" class="btn-logout">Cerrar sesión</button>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: #1e293b;
  color: white;
}
.navbar-brand a {
  color: white;
  text-decoration: none;
  font-weight: bold;
  font-size: 1.2rem;
}
.navbar-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.user-role {
  background: #3b82f6;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.75rem;
  text-transform: uppercase;
}
.btn-logout {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  cursor: pointer;
}
</style>