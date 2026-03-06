<template>
  <div class="login-page">
    <div class="login-card">
      <h1>TCG Engineering Hub</h1>
      <p class="subtitle">Ingresa tus credenciales</p>

      <div v-if="error" class="error-msg">{{ error }}</div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="form.email" type="email" placeholder="admin@tcg.com" />
      </div>

      <div class="form-group">
        <label>Contraseña</label>
        <input v-model="form.password" type="password" placeholder="••••••••" />
      </div>

      <button @click="handleLogin" :disabled="loading" class="btn-primary">
        {{ loading ? 'Ingresando...' : 'Ingresar' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const form    = ref({ email: '', password: '' })
const loading = ref(false)
const error   = ref(null)

const handleLogin = async () => {
  error.value   = null
  loading.value = true
  try {
    await auth.login(form.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Error al iniciar sesión.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
}
.login-card {
  background: white;
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 400px;
}
h1 { font-size: 1.5rem; margin-bottom: 0.25rem; color: #1e293b; }
.subtitle { color: #94a3b8; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.875rem; color: #475569; margin-bottom: 0.4rem; }
.form-group input {
  width: 100%;
  padding: 0.6rem 0.8rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
}
.btn-primary {
  width: 100%;
  padding: 0.75rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  margin-top: 0.5rem;
}
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.error-msg {
  background: #fee2e2;
  color: #b91c1c;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}
</style>