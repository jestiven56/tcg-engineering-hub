<template>
  <div class="array-input">
    <div v-for="(item, i) in modelValue" :key="i" class="array-row">
      <input :value="item" @input="update(i, $event.target.value)" :disabled="readonly" />
      <button v-if="!readonly" @click="remove(i)" class="btn-remove">✕</button>
    </div>
    <button v-if="!readonly" @click="add" class="btn-add">+ Agregar</button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  readonly:   { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

const add    = () => emit('update:modelValue', [...(props.modelValue ?? []), ''])
const remove = (i) => emit('update:modelValue', props.modelValue.filter((_, idx) => idx !== i))
const update = (i, val) => {
  const arr = [...props.modelValue]
  arr[i] = val
  emit('update:modelValue', arr)
}
</script>

<style scoped>
.array-input { display: flex; flex-direction: column; gap: 0.5rem; }
.array-row { display: flex; gap: 0.5rem; }
.array-row input { flex: 1; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; }
.btn-remove { background: #fee2e2; color: #b91c1c; border: none; padding: 0.3rem 0.6rem; border-radius: 6px; cursor: pointer; }
.btn-add { background: #f1f5f9; border: 1px dashed #cbd5e1; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; color: #475569; width: fit-content; }
</style>