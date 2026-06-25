<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { getOrder, listRiders, reassignRider } from '~/api/admin';
import type { Order, Rider } from '~/types/models';
import Button from '~/components/Button.vue';

const route = useRoute();
const orderId = Number(route.params.id);
const order = ref<Order | null>(null);
const riders = ref<Rider[]>([]);
const selectedRiderId = ref<number | null>(null);
const reassigning = ref(false);

async function refresh() { order.value = await getOrder(orderId); }

onMounted(async () => {
  await refresh();
  riders.value = (await listRiders('available')).data;
});

async function handleReassign() {
  if (!selectedRiderId.value) return;
  reassigning.value = true;
  try { await reassignRider(orderId, selectedRiderId.value); await refresh(); }
  finally { reassigning.value = false; }
}
</script>

<template>
  <div v-if="order">
    <h1 class="text-2xl font-extrabold mb-1">Order #{{ order.id }}</h1>
    <p class="text-text-muted capitalize mb-6">{{ order.status.replace(/_/g, ' ') }}</p>

    <div class="grid grid-cols-2 gap-6 mb-8">
      <div class="rounded-card bg-white shadow p-6">
        <h2 class="font-bold mb-2">Customer</h2>
        <p class="text-sm">{{ order.customer?.name }}</p>
        <p class="text-sm text-text-muted">{{ order.customer?.phone }}</p>
        <p class="text-sm text-text-muted mt-2">{{ order.delivery_address }}</p>
      </div>
      <div class="rounded-card bg-white shadow p-6">
        <h2 class="font-bold mb-2">Vendor</h2>
        <p class="text-sm">{{ order.vendor?.business_name }}</p>
        <p class="text-sm text-text-muted">{{ order.vendor?.phone }}</p>
      </div>
    </div>

    <div class="rounded-card bg-white shadow p-6 mb-8">
      <h2 class="font-bold mb-2">Items</h2>
      <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm py-1">
        <span>{{ item.quantity }}x {{ item.menu_item?.name }}</span>
        <span>₱{{ (Number(item.price_at_order) * item.quantity).toFixed(2) }}</span>
      </div>
      <div class="flex justify-between font-bold mt-3 pt-3 border-t border-border">
        <span>Total</span><span>₱{{ Number(order.total).toFixed(2) }}</span>
      </div>
    </div>

    <div class="rounded-card bg-white shadow p-6">
      <h2 class="font-bold mb-3">Rider</h2>
      <p class="text-sm mb-3">Currently assigned: {{ order.rider?.user?.name ?? 'None' }}</p>
      <div class="flex gap-3">
        <select v-model="selectedRiderId" class="rounded-xl bg-chip-bg px-3 py-2 text-sm flex-1">
          <option :value="null">Select an available rider…</option>
          <option v-for="rider in riders" :key="rider.id" :value="rider.id">{{ rider.user?.name }}</option>
        </select>
        <Button :disabled="!selectedRiderId" :loading="reassigning" @click="handleReassign">Reassign</Button>
      </div>
    </div>
  </div>
</template>
