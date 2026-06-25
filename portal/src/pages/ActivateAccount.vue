<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { activateAccount } from '~/api/activation';
import Button from '~/components/Button.vue';

const route = useRoute();
const router = useRouter();

const userId = Number(route.params.user);
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const done = ref(false);
const error = ref<string | null>(null);

async function handleSubmit() {
  error.value = null;
  loading.value = true;
  try {
    await activateAccount(
      userId,
      { expires: String(route.query.expires ?? ''), signature: String(route.query.signature ?? '') },
      password.value,
      passwordConfirmation.value
    );
    done.value = true;
    setTimeout(() => router.push('/vendor/login'), 2000);
  } catch {
    error.value = 'This activation link is invalid, expired, or already used.';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <section class="max-w-md mx-auto px-6 py-16">
    <div v-if="done" class="rounded-card bg-chip-bg p-8 text-center">
      <p class="font-semibold">Your account is active. You can now log in.</p>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="rounded-card bg-white border border-border p-8 space-y-4">
      <h1 class="text-2xl font-extrabold">Set your password</h1>
      <input v-model="password" required minlength="8" type="password" placeholder="New password" class="w-full rounded-2xl bg-chip-bg px-4 py-3 text-sm" />
      <input v-model="passwordConfirmation" required minlength="8" type="password" placeholder="Confirm password" class="w-full rounded-2xl bg-chip-bg px-4 py-3 text-sm" />
      <p v-if="error" class="text-danger text-sm">{{ error }}</p>
      <Button type="submit" :loading="loading">Activate Account</Button>
    </form>
  </section>
</template>
