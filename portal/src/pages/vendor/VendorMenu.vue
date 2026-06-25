<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { createMenuItem, deleteMenuItem, listMenuItems, updateMenuItem } from '~/api/vendor';
import type { MenuItem } from '~/types/models';
import Button from '~/components/Button.vue';

const items = ref<MenuItem[]>([]);
const loading = ref(true);
const creating = ref(false);
const error = ref<string | null>(null);

const form = reactive({ name: '', description: '', price: '', category: '', photo: null as File | null });
const photoPreview = ref<string | null>(null);

function onPhotoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null;
  form.photo = file;
  photoPreview.value = file ? URL.createObjectURL(file) : null;
}

async function refresh() {
  loading.value = true;
  items.value = await listMenuItems();
  loading.value = false;
}

async function handleCreate() {
  error.value = null; creating.value = true;
  try {
    await createMenuItem({ name: form.name, description: form.description || null, price: Number(form.price), category: form.category || null, photo: form.photo });
    Object.assign(form, { name: '', description: '', price: '', category: '', photo: null });
    photoPreview.value = null;
    await refresh();
  } catch { error.value = 'Could not add item.'; } finally { creating.value = false; }
}

async function toggleAvailability(item: MenuItem) {
  await updateMenuItem(item.id, { is_available: !item.is_available });
  await refresh();
}

async function handleDelete(item: MenuItem) { await deleteMenuItem(item.id); await refresh(); }

// Edit modal
const editingItem = ref<MenuItem | null>(null);
const editSaving = ref(false);
const editError = ref<string | null>(null);
const editForm = reactive({ name: '', description: '', price: '', category: '', photo: null as File | null, is_available: true });
const editPhotoPreview = ref<string | null>(null);

function openEdit(item: MenuItem) {
  editingItem.value = item;
  Object.assign(editForm, { name: item.name, description: item.description ?? '', price: String(item.price), category: item.category ?? '', photo: null, is_available: item.is_available });
  editPhotoPreview.value = item.photo_url ?? null;
  editError.value = null;
}
function closeEdit() { editingItem.value = null; editPhotoPreview.value = null; }
function onEditPhotoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null;
  editForm.photo = file;
  editPhotoPreview.value = file ? URL.createObjectURL(file) : null;
}
async function handleEdit() {
  if (!editingItem.value) return;
  editError.value = null; editSaving.value = true;
  try {
    await updateMenuItem(editingItem.value.id, { name: editForm.name, description: editForm.description || null, price: Number(editForm.price), category: editForm.category || null, photo: editForm.photo, is_available: editForm.is_available });
    closeEdit(); await refresh();
  } catch { editError.value = 'Could not save changes.'; } finally { editSaving.value = false; }
}

onMounted(refresh);
</script>

<template>
  <h1 class="text-2xl font-extrabold mb-6">Menu</h1>

  <form @submit.prevent="handleCreate" class="rounded-card bg-chip-bg p-5 mb-8 grid md:grid-cols-5 gap-3 items-end">
    <input v-model="form.name" required placeholder="Item name" class="rounded-xl bg-white px-3 py-2 text-sm md:col-span-2" />
    <input v-model="form.category" placeholder="Category" class="rounded-xl bg-white px-3 py-2 text-sm" />
    <input v-model="form.price" required type="number" min="0" step="0.01" placeholder="Price" class="rounded-xl bg-white px-3 py-2 text-sm" />
    <button type="submit" :disabled="creating" class="rounded-pill bg-primary text-white px-4 py-2 text-sm font-semibold disabled:opacity-50">Add Item</button>
    <input v-model="form.description" placeholder="Description (optional)" class="rounded-xl bg-white px-3 py-2 text-sm md:col-span-4" />
    <label class="flex items-center gap-2 cursor-pointer md:col-span-5">
      <span class="text-xs text-text-muted">Photo</span>
      <input type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
      <span class="rounded-xl bg-white px-3 py-2 text-sm text-text-muted border border-border">{{ form.photo ? form.photo.name : 'Choose image…' }}</span>
      <img v-if="photoPreview" :src="photoPreview" class="h-10 w-10 rounded-lg object-cover" />
    </label>
  </form>

  <p v-if="error" class="text-danger text-sm mb-4">{{ error }}</p>

  <table v-if="!loading" class="w-full text-sm">
    <thead>
      <tr class="text-left text-text-muted border-b border-border">
        <th class="py-2 w-12"></th><th class="py-2">Name</th><th class="py-2">Category</th><th class="py-2">Price</th><th class="py-2">Available</th><th class="py-2"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id" class="border-b border-border">
        <td class="py-3">
          <img v-if="item.photo_url" :src="item.photo_url" class="h-10 w-10 rounded-lg object-cover" />
          <div v-else class="h-10 w-10 rounded-lg bg-chip-bg" />
        </td>
        <td class="py-3">{{ item.name }}</td>
        <td class="py-3">{{ item.category ?? '—' }}</td>
        <td class="py-3">₱{{ Number(item.price).toFixed(2) }}</td>
        <td class="py-3">
          <button class="rounded-pill px-3 py-1.5 text-xs font-semibold" :class="item.is_available ? 'bg-chip-bg text-primary' : 'bg-chip-bg text-text-muted'" @click="toggleAvailability(item)">
            {{ item.is_available ? 'Available' : 'Unavailable' }}
          </button>
        </td>
        <td class="py-3 flex gap-3">
          <button class="text-primary font-semibold" @click="openEdit(item)">Edit</button>
          <button class="text-danger font-semibold" @click="handleDelete(item)">Delete</button>
        </td>
      </tr>
    </tbody>
  </table>

  <div v-if="editingItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeEdit">
    <div class="bg-white rounded-card w-full max-w-md p-6 space-y-4 shadow-xl">
      <h2 class="text-lg font-extrabold">Edit Item</h2>
      <form @submit.prevent="handleEdit" class="space-y-3">
        <input v-model="editForm.name" required placeholder="Item name" class="w-full rounded-xl bg-chip-bg px-3 py-2 text-sm" />
        <div class="grid grid-cols-2 gap-3">
          <input v-model="editForm.category" placeholder="Category" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
          <input v-model="editForm.price" required type="number" min="0" step="0.01" placeholder="Price" class="rounded-xl bg-chip-bg px-3 py-2 text-sm" />
        </div>
        <input v-model="editForm.description" placeholder="Description (optional)" class="w-full rounded-xl bg-chip-bg px-3 py-2 text-sm" />
        <label class="flex items-center gap-3 cursor-pointer">
          <span class="text-sm text-text-muted">Photo</span>
          <input type="file" accept="image/*" class="hidden" @change="onEditPhotoChange" />
          <span class="rounded-xl bg-chip-bg px-3 py-2 text-sm text-text-muted border border-border">{{ editForm.photo ? editForm.photo.name : 'Replace image…' }}</span>
          <img v-if="editPhotoPreview" :src="editPhotoPreview" class="h-10 w-10 rounded-lg object-cover" />
        </label>
        <label class="flex items-center gap-2 text-sm cursor-pointer">
          <input type="checkbox" v-model="editForm.is_available" class="rounded" /> Available
        </label>
        <p v-if="editError" class="text-danger text-sm">{{ editError }}</p>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" class="rounded-pill px-4 py-2 text-sm font-semibold bg-chip-bg" @click="closeEdit">Cancel</button>
          <Button type="submit" :loading="editSaving">Save Changes</Button>
        </div>
      </form>
    </div>
  </div>
</template>
