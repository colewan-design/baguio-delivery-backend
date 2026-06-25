<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { createRider, listRiders } from '~/api/admin';
import type { Rider } from '~/types/models';
import Button from '~/components/Button.vue';

const route = useRoute();
const riders = ref<Rider[]>([]);
const loading = ref(true);
const showForm = ref(Boolean(route.query.lead_id));
const creating = ref(false);
const error = ref<string | null>(null);

const form = reactive({
  name: (route.query.name as string) ?? '', email: '', phone: '', vehicle_type: '',
  lead_id: route.query.lead_id ? Number(route.query.lead_id) : undefined,
});

async function refresh() {
  loading.value = true;
  riders.value = (await listRiders()).data;
  loading.value = false;
}

async function handleCreate() {
  error.value = null; creating.value = true;
  try {
    await createRider({ ...form, vehicle_type: form.vehicle_type || undefined });
    showForm.value = false;
    Object.assign(form, { name:'', email:'', phone:'', vehicle_type:'', lead_id: undefined });
    await refresh();
  } catch { error.value = 'Could not create rider.'; } finally { creating.value = false; }
}

onMounted(refresh);
</script>

<template>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold">Riders</h1>
    <Button @click="showForm = !showForm">{{ showForm ? 'Cancel' : 'Onboard Rider' }}</Button>
  </div>

  <form v-if="showForm" @submit.prevent="handleCreate" class="rounded-card bg-white shadow p-6 mb-8 grid grid-cols-2 gap-3 max-w-2xl">
    <input v-model="form.name" required placeholder="Full name" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model="form.email" required type="email" placeholder="Email" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model="form.phone" required placeholder="Phone" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model="form.vehicle_type" placeholder="Vehicle type (optional)" class="rounded-xl bg-chip-bg px-3 py-2 text-sm col-span-2" />
    <p class="col-span-2 text-text-muted text-xs">No password needed — rider gets an email to activate the account.</p>
    <p v-if="error" class="col-span-2 text-danger text-sm">{{ error }}</p>
    <Button type="submit" :loading="creating" class="col-span-2">Create Rider</Button>
  </form>

  <table v-if="!loading" class="w-full text-sm">
    <thead>
      <tr class="text-left text-text-muted border-b border-border">
        <th class="py-2">Name</th><th class="py-2">Phone</th><th class="py-2">Vehicle</th><th class="py-2">Status</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="rider in riders" :key="rider.id" class="border-b border-border">
        <td class="py-3 font-semibold">{{ rider.user?.name }}</td>
        <td class="py-3 text-text-muted">{{ rider.user?.phone }}</td>
        <td class="py-3">{{ rider.vehicle_type ?? '—' }}</td>
        <td class="py-3 capitalize">{{ rider.status }}</td>
      </tr>
    </tbody>
  </table>
</template>
