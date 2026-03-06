<template>
  <div class="timeline">
    <div v-if="loading" class="loading">Cargando historial...</div>

    <div v-else-if="events.length === 0" class="empty">
      No hay eventos registrados aún.
    </div>

    <div v-else>
      <div v-for="event in events" :key="event.id" class="timeline-item">
        <div class="timeline-dot" :class="`dot-${event.action}`"></div>
        <div class="timeline-content">
          <div class="timeline-header">
            <span class="action-label">{{ actionLabel(event.action) }}</span>
            <span class="entity-type">{{ event.entity_type }}</span>
            <span class="actor">{{ event.actor?.name }}</span>
          </div>
          <div class="timeline-date">{{ formatDate(event.created_at) }}</div>

          <!-- Mostrar diff si hay before/after -->
          <div v-if="event.before_json || event.after_json" class="diff">
            <span v-if="event.before_json?.status" class="diff-before">
              {{ event.before_json.status }}
            </span>
            <span v-if="event.before_json?.status" class="diff-arrow">→</span>
            <span v-if="event.after_json?.status" class="diff-after">
              {{ event.after_json.status }}
            </span>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="totalPages > 1" class="pagination">
        <button :disabled="page === 1" @click="$emit('page-change', page - 1)">←</button>
        <span>{{ page }} / {{ totalPages }}</span>
        <button :disabled="page === totalPages" @click="$emit('page-change', page + 1)">→</button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  events:     { type: Array,   default: () => [] },
  loading:    { type: Boolean, default: false },
  page:       { type: Number,  default: 1 },
  totalPages: { type: Number,  default: 1 },
})

defineEmits(['page-change'])

const actionLabel = (action) => ({
  created:        '✅ Creado',
  updated:        '✏️ Editado',
  status_changed: '🔄 Estado cambiado',
  validated:      '✔️ Validado',
  completed:      '🏁 Completado',
}[action] ?? action)

const formatDate = (date) =>
  new Date(date).toLocaleString('es-CO', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
</script>

<style scoped>
.timeline { position: relative; padding-left: 1.5rem; }
.timeline-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  position: relative;
}
.timeline-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin-top: 4px;
  flex-shrink: 0;
}
.dot-created        { background: #22c55e; }
.dot-updated        { background: #3b82f6; }
.dot-status_changed { background: #f59e0b; }
.dot-validated      { background: #8b5cf6; }
.dot-completed      { background: #10b981; }

.timeline-header { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
.action-label  { font-weight: 600; font-size: 0.9rem; }
.entity-type   { background: #f1f5f9; padding: 0.1rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
.actor         { color: #64748b; font-size: 0.85rem; }
.timeline-date { color: #94a3b8; font-size: 0.8rem; margin-top: 0.2rem; }

.diff { display: flex; gap: 0.5rem; margin-top: 0.4rem; align-items: center; font-size: 0.85rem; }
.diff-before { background: #fee2e2; color: #b91c1c; padding: 0.1rem 0.5rem; border-radius: 4px; }
.diff-after  { background: #dcfce7; color: #15803d; padding: 0.1rem 0.5rem; border-radius: 4px; }
.diff-arrow  { color: #94a3b8; }

.pagination { display: flex; gap: 1rem; align-items: center; justify-content: center; margin-top: 1rem; }
.pagination button { padding: 0.3rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; }
.pagination button:disabled { opacity: 0.4; cursor: not-allowed; }

.loading, .empty { color: #94a3b8; padding: 1rem 0; }
</style>