<template>
  <span class="badge" :class="statusClass">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true }
})

const statusMap = {
  // Project
  draft:         { label: 'Borrador',    color: 'gray'   },
  discovery:     { label: 'Discovery',   color: 'blue'   },
  execution:     { label: 'Execution',   color: 'yellow' },
  delivered:     { label: 'Entregado',   color: 'green'  },
  // Artifact
  not_started:   { label: 'Sin iniciar', color: 'gray'   },
  in_progress:   { label: 'En progreso', color: 'blue'   },
  blocked:       { label: 'Bloqueado',   color: 'red'    },
  done:          { label: 'Completado',  color: 'green'  },
  // Module
  validated:     { label: 'Validado',    color: 'green'  },
  ready_for_build: { label: 'Listo',     color: 'purple' },
}

const label      = computed(() => statusMap[props.status]?.label ?? props.status)
const statusClass = computed(() => `badge-${statusMap[props.status]?.color ?? 'gray'}`)
</script>

<style scoped>
.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}
.badge-gray   { background: #e2e8f0; color: #475569; }
.badge-blue   { background: #dbeafe; color: #1d4ed8; }
.badge-yellow { background: #fef9c3; color: #a16207; }
.badge-green  { background: #dcfce7; color: #15803d; }
.badge-red    { background: #fee2e2; color: #b91c1c; }
.badge-purple { background: #f3e8ff; color: #7e22ce; }
</style>