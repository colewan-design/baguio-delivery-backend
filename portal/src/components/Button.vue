<script setup lang="ts">
withDefaults(
  defineProps<{ variant?: 'primary' | 'outline'; loading?: boolean; disabled?: boolean; type?: 'button' | 'submit' }>(),
  { variant: 'primary', loading: false, disabled: false, type: 'button' }
);
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="rounded-pill px-6 py-3 font-bold text-sm transition disabled:opacity-50"
    :class="variant === 'primary' ? 'bg-primary text-white hover:bg-primary-dark' : 'bg-white text-primary border-2 border-primary'"
  >
    <span v-if="loading">Loading…</span>
    <slot v-else />
  </button>
</template>
