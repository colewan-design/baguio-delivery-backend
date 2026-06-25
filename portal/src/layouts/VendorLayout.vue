<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '~/stores/auth';
import { updateVendorOpenStatus } from '~/api/vendor';

const auth = useAuthStore();
const router = useRouter();
const togglingOpen = ref(false);

async function toggleOpen() {
  if (!auth.user?.vendor) return;
  togglingOpen.value = true;
  try {
    const vendor = await updateVendorOpenStatus(!auth.user.vendor.is_open);
    auth.user.vendor.is_open = vendor.is_open;
  } finally {
    togglingOpen.value = false;
  }
}

function handleLogout() {
  auth.logout();
  router.push('/vendor/login');
}
</script>

<template>
  <div class="min-h-screen flex">
    <aside class="w-56 border-r border-border p-6">
      <div class="font-extrabold mb-8">Baguio Delivery</div>
      <nav class="space-y-1">
        <RouterLink to="/vendor/orders" class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary">Orders</RouterLink>
        <RouterLink to="/vendor/menu"   class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary">Menu</RouterLink>
      </nav>

      <button
        v-if="auth.user?.vendor"
        :disabled="togglingOpen"
        class="mt-8 w-full rounded-pill px-3 py-2 text-sm font-semibold disabled:opacity-50"
        :class="auth.user.vendor.is_open ? 'bg-primary text-white' : 'bg-chip-bg text-text-muted'"
        @click="toggleOpen"
      >
        {{ auth.user.vendor.is_open ? 'Open' : 'Closed' }} — tap to toggle
      </button>

      <button @click="handleLogout" class="mt-4 text-sm font-semibold text-text-muted hover:text-danger">Log Out</button>
    </aside>
    <main class="flex-1 p-8"><RouterView /></main>
  </div>
</template>
