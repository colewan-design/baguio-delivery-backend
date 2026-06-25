<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { listOrders } from '~/api/admin';
import type { Order, OrderStatus } from '~/types/models';

const router = useRouter();
const orders = ref<Order[]>([]);
const loading = ref(true);
const statusFilter = ref<OrderStatus | ''>('');
const statuses: OrderStatus[] = ['pending','accepted','rejected','rider_assigned','ready_for_pickup','out_for_delivery','completed','cancelled'];

async function refresh() {
  loading.value = true;
  orders.value = (await listOrders(statusFilter.value || undefined)).data;
  loading.value = false;
}

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
        <th class="py-2">#</th><th class="py-2">Customer</th><th class="py-2">Vendor</th><th class="py-2">Rider</th><th class="py-2">Status</th><th class="py-2">Total</th><th class="py-2"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="order in orders" :key="order.id" class="border-b border-border">
        <td class="py-3">{{ order.id }}</td>
        <td class="py-3">{{ order.customer?.name }}</td>
        <td class="py-3">{{ order.vendor?.business_name }}</td>
        <td class="py-3">{{ order.rider?.user?.name ?? '—' }}</td>
        <td class="py-3 capitalize">{{ order.status.replace(/_/g, ' ') }}</td>
        <td class="py-3">₱{{ Number(order.total).toFixed(2) }}</td>
        <td class="py-3"><button class="text-primary font-semibold" @click="router.push(`/admin/orders/${order.id}`)">View</button></td>
      </tr>
    </tbody>
  </table>
</template>
