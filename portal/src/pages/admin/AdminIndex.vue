<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { listOrders, listRiders, listVendors } from '~/api/admin';

const activeOrders = ref(0); const availableRiders = ref(0); const pendingVendors = ref(0); const loading = ref(true);

onMounted(async () => {
  const [orders, riders, vendors] = await Promise.all([listOrders(), listRiders('available'), listVendors('pending')]);
  activeOrders.value = orders.data.filter((o) => !['completed','cancelled','rejected'].includes(o.status)).length;
  availableRiders.value = riders.total;
  pendingVendors.value = vendors.total;
  loading.value = false;
});
</script>

<template>
  <h1 class="text-2xl font-extrabold mb-6">Overview</h1>
  <div v-if="!loading" class="grid grid-cols-3 gap-4">
    <div class="rounded-card bg-white shadow p-6"><p class="text-sm text-text-muted">Active Orders (last 20)</p><p class="text-3xl font-extrabold mt-2">{{ activeOrders }}</p></div>
    <div class="rounded-card bg-white shadow p-6"><p class="text-sm text-text-muted">Available Riders</p><p class="text-3xl font-extrabold mt-2">{{ availableRiders }}</p></div>
    <div class="rounded-card bg-white shadow p-6"><p class="text-sm text-text-muted">Pending Vendor Approvals</p><p class="text-3xl font-extrabold mt-2">{{ pendingVendors }}</p></div>
  </div>
</template>
