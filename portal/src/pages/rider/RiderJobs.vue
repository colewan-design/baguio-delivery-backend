<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { deliverOrder, listRiderJobs, pickupOrder } from '~/api/rider';
import type { Order } from '~/types/models';

const jobs = ref<Order[]>([]);
const loading = ref(true);
const acting = ref<number | null>(null);

async function refresh() { loading.value = true; jobs.value = await listRiderJobs(); loading.value = false; }
async function handlePickup(order: Order) { acting.value = order.id; await pickupOrder(order.id); await refresh(); acting.value = null; }
async function handleDeliver(order: Order) { acting.value = order.id; await deliverOrder(order.id); await refresh(); acting.value = null; }

onMounted(refresh);
</script>

<template>
  <h1 class="text-2xl font-extrabold mb-6">Jobs</h1>
  <p v-if="!loading && jobs.length === 0" class="text-text-muted text-sm">No active jobs right now.</p>

  <div v-if="!loading" class="space-y-4">
    <div v-for="order in jobs" :key="order.id" class="rounded-card bg-white border border-border p-5">
      <div class="flex items-start justify-between">
        <div>
          <p class="font-semibold">Order #{{ order.id }} — {{ order.vendor?.business_name }}</p>
          <p class="text-sm text-text-muted mt-1">Deliver to: {{ order.customer?.name }}, {{ order.delivery_address }}</p>
          <p class="text-sm text-text-muted capitalize mt-1">Status: {{ order.status.replace(/_/g, ' ') }}</p>
        </div>
        <p class="font-bold text-primary">₱{{ Number(order.total).toFixed(2) }}</p>
      </div>
      <div class="mt-4 flex gap-2">
        <button v-if="order.status === 'ready_for_pickup'" :disabled="acting === order.id" class="rounded-pill bg-primary text-white px-4 py-1.5 text-xs font-semibold disabled:opacity-50" @click="handlePickup(order)">Picked Up</button>
        <button v-if="order.status === 'out_for_delivery'" :disabled="acting === order.id" class="rounded-pill bg-primary text-white px-4 py-1.5 text-xs font-semibold disabled:opacity-50" @click="handleDeliver(order)">Delivered</button>
      </div>
    </div>
  </div>
</template>
