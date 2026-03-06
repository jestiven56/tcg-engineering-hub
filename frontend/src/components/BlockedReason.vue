<template>
  <div v-if="reasons?.length" class="blocked-box">
    <span class="blocked-icon">⚠️</span>
    <div>
      <p class="blocked-title">Bloqueado</p>
      <ul>
        <li v-for="(reason, i) in reasons" :key="i">{{ reason }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps({
  reasons: { type: Array, default: () => [] }
})
</script>

<style scoped>
.blocked-box {
  display: flex;
  gap: 0.75rem;
  background: #fff7ed;
  border: 1px solid #fed7aa;
  border-radius: 8px;
  padding: 1rem;
  margin: 1rem 0;
}
.blocked-title {
  font-weight: 600;
  color: #c2410c;
  margin: 0 0 0.4rem;
}
ul {
  margin: 0;
  padding-left: 1.2rem;
  color: #9a3412;
  font-size: 0.9rem;
}
</style>