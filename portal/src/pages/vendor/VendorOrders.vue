<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { acceptOrder, listVendorOrders, markOrderReady, rejectOrder } from '~/api/vendor';
import type { Order, OrderStatus } from '~/types/models';

const orders = ref<Order[]>([]);
const loading = ref(true);
const acting = ref<number | null>(null);
const statusFilter = ref<OrderStatus | ''>('');

const statuses: OrderStatus[] = ['pending','accepted','rejected','rider_assigned','ready_for_pickup','out_for_delivery','completed','cancelled'];

async function refresh() {
  loading.value = true;
  const res = await listVendorOrders(statusFilter.value || undefined);
  orders.value = res.data;
  loading.value = false;
}

async function handleAccept(order: Order) { acting.value = order.id; await acceptOrder(order.id); await refresh(); acting.value = null; }
async function handleReject(order: Order) { acting.value = order.id; await rejectOrder(order.id); await refresh(); acting.value = null; }
async function handleReady(order: Order)  { acting.value = order.id; await markOrderReady(order.id); await refresh(); acting.value = null; }

watch(statusFilter, refresh);
onMounted(refresh);
</script>

<template>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold">Orders</h1>
    <select v-model="statusFilter" class="rounded-xl bg-chip-bg px-3 py-2 text-sm">
      <option value="">All statuses</option>
      <option v-for="s in statuses" :key="s" :value="s">{{ s.replace(/_/g, ' ') }}</option>
    </select>
  </div>

  <table v-if="!loading" class="w-full text-sm">
    <thead>
      <tr class="text-left text-text-muted border-b border-border">
        <th class="py-2">#</th><th class="py-2">Customer</th><th class="py-2">Items</th><th class="py-2">Status</th><th class="py-2">Total</th><th class="py-2"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="order in orders" :key="order.id" class="border-b border-border">
        <td class="py-3">{{ order.id }}</td>
        <td class="py-3">{{ order.customer?.name }}</td>
        <td class="py-3">{{ order.items.map((i) => `${i.quantity}× ${i.menu_item?.name ?? 'Item'}`).join(', ') }}</td>
        <td class="py-3 capitalize">{{ order.status.replace(/_/g, ' ') }}</td>
        <td class="py-3">₱{{ Number(order.total).toFixed(2) }}</td>
        <td class="py-3 flex gap-2">
          <button v-if="order.status === 'pending'" :disabled="acting === order.id" class="rounded-pill bg-primary text-white px-3 py-1.5 text-xs font-semibold disabled:opacity-50" @click="handleAccept(order)">Accept</button>
          <button v-if="order.status === 'pending'" :disabled="acting === order.id" class="rounded-pill bg-chip-bg text-danger px-3 py-1.5 text-xs font-semibold disabled:opacity-50" @click="handleReject(order)">Reject</button>
          <button v-if="order.status === 'rider_assigned'" :disabled="acting === order.id" class="rounded-pill bg-chip-bg text-text-muted px-3 py-1.5 text-xs font-semibold disabled:opacity-50" @click="handleReady(order)">Mark Ready</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>
