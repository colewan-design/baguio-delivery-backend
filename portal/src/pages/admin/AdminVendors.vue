<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { createVendor, listVendors, updateVendorStatus } from '~/api/admin';
import type { Vendor } from '~/types/models';
import Button from '~/components/Button.vue';

const route = useRoute();
const vendors = ref<Vendor[]>([]);
const loading = ref(true);
const showForm = ref(Boolean(route.query.lead_id));
const creating = ref(false);
const error = ref<string | null>(null);

const form = reactive({
  name: (route.query.name as string) ?? '', email: '', phone: '',
  business_name: '', category: 'food' as Vendor['category'],
  address: '', vendor_phone: '',
  lead_id: route.query.lead_id ? Number(route.query.lead_id) : undefined,
});
const lat = ref<number | null>(null);
const lng = ref<number | null>(null);

async function refresh() {
  loading.value = true;
  vendors.value = (await listVendors()).data;
  loading.value = false;
}

async function handleCreate() {
  error.value = null;
  if (lat.value === null || lng.value === null) { error.value = 'Enter a valid lat/lng.'; return; }
  creating.value = true;
  try {
    await createVendor({ ...form, lat: lat.value, lng: lng.value });
    showForm.value = false;
    Object.assign(form, { name:'', email:'', phone:'', business_name:'', category:'food', address:'', vendor_phone:'', lead_id: undefined });
    lat.value = null; lng.value = null;
    await refresh();
  } catch { error.value = 'Could not create vendor.'; } finally { creating.value = false; }
}

async function handleStatusChange(vendor: Vendor, status: Vendor['status']) {
  await updateVendorStatus(vendor.id, status);
  await refresh();
}

onMounted(refresh);
</script>

<template>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold">Vendors</h1>
    <Button @click="showForm = !showForm">{{ showForm ? 'Cancel' : 'Onboard Vendor' }}</Button>
  </div>

  <form v-if="showForm" @submit.prevent="handleCreate" class="rounded-card bg-white shadow p-6 mb-8 grid grid-cols-2 gap-3 max-w-2xl">
    <input v-model="form.name" required placeholder="Owner name" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model="form.email" required type="email" placeholder="Email" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model="form.phone" required placeholder="Owner phone" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model="form.business_name" required placeholder="Business name" class="rounded-xl bg-chip-bg px-3 py-2 text-sm col-span-2" />
    <select v-model="form.category" class="rounded-xl bg-chip-bg px-3 py-2 text-sm col-span-2">
      <option value="food">Food</option>
      <option value="groceries">Groceries</option>
      <option value="pharmacy">Pharmacy</option>
      <option value="errands">Errands</option>
    </select>
    <input v-model="form.address" required placeholder="Address" class="rounded-xl bg-chip-bg px-3 py-2 text-sm col-span-2" />
    <input v-model.number="lat" required type="number" step="any" placeholder="Latitude" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model.number="lng" required type="number" step="any" placeholder="Longitude" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
    <input v-model="form.vendor_phone" required placeholder="Business phone" class="rounded-xl bg-chip-bg px-3 py-2 text-sm col-span-2" />
    <p class="col-span-2 text-text-muted text-xs">No password needed — vendor gets an email to activate the account.</p>
    <p v-if="error" class="col-span-2 text-danger text-sm">{{ error }}</p>
    <Button type="submit" :loading="creating" class="col-span-2">Create Vendor</Button>
  </form>

  <table v-if="!loading" class="w-full text-sm">
    <thead>
      <tr class="text-left text-text-muted border-b border-border">
        <th class="py-2">Business</th><th class="py-2">Category</th><th class="py-2">Owner</th><th class="py-2">Status</th><th class="py-2">Open?</th><th class="py-2"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="vendor in vendors" :key="vendor.id" class="border-b border-border">
        <td class="py-3 font-semibold">{{ vendor.business_name }}</td>
        <td class="py-3 text-text-muted capitalize">{{ vendor.category }}</td>
        <td class="py-3 text-text-muted">{{ vendor.user?.name }}</td>
        <td class="py-3 capitalize">{{ vendor.status }}</td>
        <td class="py-3">{{ vendor.is_open ? 'Yes' : 'No' }}</td>
        <td class="py-3 space-x-2">
          <button v-if="vendor.status !== 'approved'"  @click="handleStatusChange(vendor, 'approved')"  class="text-primary font-semibold">Approve</button>
          <button v-if="vendor.status !== 'suspended'" @click="handleStatusChange(vendor, 'suspended')" class="text-danger font-semibold">Suspend</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>
