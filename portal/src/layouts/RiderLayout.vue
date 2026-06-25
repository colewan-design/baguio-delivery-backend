<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '~/stores/auth';
import { updateRiderStatus } from '~/api/rider';
import type { Rider } from '~/types/models';

const auth = useAuthStore();
const router = useRouter();
const togglingStatus = ref(false);

const statusOptions: { value: Rider['status']; label: string }[] = [
  { value: 'available', label: 'Available' },
  { value: 'busy',      label: 'Busy' },
  { value: 'offline',   label: 'Offline' },
];

async function setStatus(status: Rider['status']) {
  if (!auth.user?.rider || status === auth.user.rider.status) return;
  togglingStatus.value = true;
  try {
    const rider = await updateRiderStatus(status);
    auth.user.rider.status = rider.status;
  } finally {
    togglingStatus.value = false;
  }
}

function handleLogout() {
  auth.logout();
  router.push('/rider/login');
}
</script>

<template>
  <div class="min-h-screen flex">
    <aside class="w-56 border-r border-border p-6">
      <div class="font-extrabold mb-8">Baguio Delivery</div>
      <nav class="space-y-1">
        <RouterLink to="/rider/jobs" class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary">Jobs</RouterLink>
      </nav>

      <div v-if="auth.user?.rider" class="mt-8 space-y-1.5">
        <button
          v-for="option in statusOptions"
          :key="option.value"
          :disabled="togglingStatus"
          class="block w-full rounded-pill px-3 py-2 text-left text-sm font-semibold disabled:opacity-50"
          :class="auth.user.rider.status === option.value ? 'bg-primary text-white' : 'bg-chip-bg text-text-muted'"
          @click="setStatus(option.value)"
        >
          {{ option.label }}
        </button>
      </div>

      <button @click="handleLogout" class="mt-4 text-sm font-semibold text-text-muted hover:text-danger">Log Out</button>
    </aside>
    <main class="flex-1 p-8"><RouterView /></main>
  </div>
</template>
