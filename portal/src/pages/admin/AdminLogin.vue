<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { login } from '~/api/auth';
import { useAuthStore } from '~/stores/auth';
import Button from '~/components/Button.vue';

const auth = useAuthStore();
const router = useRouter();
const email = ref(''); const password = ref(''); const loading = ref(false); const error = ref<string | null>(null);

async function handleSubmit() {
  error.value = null; loading.value = true;
  try {
    const { user, token } = await login(email.value, password.value);
    if (user.role !== 'admin') { error.value = 'This account does not have admin access.'; return; }
    auth.setSession(user, token);
    router.push('/admin');
  } catch { error.value = 'Invalid email or password.'; } finally { loading.value = false; }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-chip-bg">
    <form @submit.prevent="handleSubmit" class="bg-white rounded-card p-8 w-full max-w-sm shadow">
      <h1 class="text-xl font-extrabold mb-6">Admin Login</h1>
      <input v-model="email" type="email" required placeholder="Email" class="w-full rounded-2xl bg-chip-bg px-4 py-3 text-sm mb-3" />
      <input v-model="password" type="password" required placeholder="Password" class="w-full rounded-2xl bg-chip-bg px-4 py-3 text-sm mb-3" />
      <p v-if="error" class="text-danger text-sm mb-3">{{ error }}</p>
      <Button type="submit" :loading="loading" class="w-full">Log In</Button>
    </form>
  </div>
</template>
