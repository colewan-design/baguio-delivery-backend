<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { listLeads, updateLeadStatus } from '~/api/admin';
import type { Lead } from '~/types/models';

const router = useRouter();
const leads = ref<Lead[]>([]);
const loading = ref(true);

async function refresh() { loading.value = true; leads.value = (await listLeads()).data; loading.value = false; }
async function setStatus(lead: Lead, status: Lead['status']) { await updateLeadStatus(lead.id, status); await refresh(); }

function onboardPath(lead: Lead) {
  const base = lead.type === 'rider' ? '/admin/riders' : '/admin/vendors';
  return `${base}?lead_id=${lead.id}&name=${encodeURIComponent(lead.name)}`;
}

onMounted(refresh);
</script>

<template>
  <h1 class="text-2xl font-extrabold mb-6">Leads</h1>

  <table v-if="!loading" class="w-full text-sm">
    <thead>
      <tr class="text-left text-text-muted border-b border-border">
        <th class="py-2">Type</th><th class="py-2">Name</th><th class="py-2">Contact</th><th class="py-2">Message</th><th class="py-2">Status</th><th class="py-2"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="lead in leads" :key="lead.id" class="border-b border-border">
        <td class="py-3 capitalize">{{ lead.type }}</td>
        <td class="py-3 font-semibold">{{ lead.name }}</td>
        <td class="py-3 text-text-muted">{{ lead.contact }}</td>
        <td class="py-3 text-text-muted max-w-xs truncate">{{ lead.message }}</td>
        <td class="py-3 capitalize">{{ lead.status }}</td>
        <td class="py-3 space-x-2">
          <button v-if="lead.status === 'new'" @click="setStatus(lead, 'contacted')" class="text-primary font-semibold">Mark Contacted</button>
          <button v-if="lead.status !== 'converted'" @click="router.push(onboardPath(lead))" class="text-primary font-semibold">Onboard →</button>
          <button v-if="lead.status !== 'dismissed'" @click="setStatus(lead, 'dismissed')" class="text-danger font-semibold">Dismiss</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>
