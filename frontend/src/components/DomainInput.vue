<template>
  <div class="domain-input">
    <div v-for="(item, i) in modelValue" :key="i" class="domain-row card-inner">
      <div class="domain-fields">
        <div class="form-group">
          <label>Nombre</label>
          <input :value="item.name" @input="updateField(i, 'name', $event.target.value)"
            :disabled="readonly" placeholder="Nombre del dominio" />
        </div>
        <div class="form-group">
          <label>Objetivo</label>
          <input :value="item.objective" @input="updateField(i, 'objective', $event.target.value)"
            :disabled="readonly" placeholder="Objetivo" />
        </div>
      </div>
      <button v-if="!readonly" @click="remove(i)" class="btn-remove">✕ Eliminar</button>
    </div>
    <button v-if="!readonly" @click="add" class="btn-add">+ Agregar dominio</button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  readonly:   { type: Boolean, default: false },
  users:      { type: Array,   default: () => [] },
})
const emit = defineEmits(['update:modelValue'])

const add    = () => emit('update:modelValue', [...(props.modelValue ?? []), { name: '', objective: '' }])
const remove = (i) => emit('update:modelValue', props.modelValue.filter((_, idx) => idx !== i))
const updateField = (i, field, val) => {
  const arr = [...props.modelValue]
  arr[i] = { ...arr[i], [field]: val }
  emit('update:modelValue', arr)
}
</script>

<style scoped>
.domain-input { display: flex; flex-direction: column; gap: 0.75rem; }
.card-inner { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; }
.domain-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-group label { display: block; font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem; }
.form-group input { width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; }
.btn-remove { background: #fee2e2; color: #b91c1c; border: none; padding: 0.3rem 0.8rem; border-radius: 6px; cursor: pointer; margin-top: 0.5rem; font-size: 0.85rem; }
.btn-add { background: #f1f5f9; border: 1px dashed #cbd5e1; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; color: #475569; }
</style>