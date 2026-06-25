<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '~/stores/auth';

const auth = useAuthStore();
const router = useRouter();

function handleLogout() {
  auth.logout();
  router.push('/admin/login');
}
</script>

<template>
  <div class="min-h-screen flex">
    <aside class="w-56 border-r border-border p-6">
      <div class="font-extrabold mb-8">Baguio Delivery</div>
      <nav class="space-y-1">
        <RouterLink to="/admin"         class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary" exact-active-class="bg-chip-bg text-primary">Overview</RouterLink>
        <RouterLink to="/admin/vendors" class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary">Vendors</RouterLink>
        <RouterLink to="/admin/orders"  class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary">Orders</RouterLink>
        <RouterLink to="/admin/riders"  class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary">Riders</RouterLink>
        <RouterLink to="/admin/leads"   class="block rounded-xl px-3 py-2 text-sm font-semibold text-text-muted hover:bg-chip-bg" active-class="bg-chip-bg text-primary">Leads</RouterLink>
      </nav>
      <button @click="handleLogout" class="mt-8 text-sm font-semibold text-text-muted hover:text-danger">Log Out</button>
    </aside>
    <main class="flex-1 p-8"><RouterView /></main>
  </div>
</template>
