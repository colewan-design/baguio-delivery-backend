import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { User } from '~/types/models';
import apiClient from '~/lib/apiClient';

const TOKEN_KEY = 'bd_token';

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY));
  const user = ref<User | null>(null);

  const isVendor = computed(() => user.value?.role === 'vendor');
  const isAdmin  = computed(() => user.value?.role === 'admin');
  const isRider  = computed(() => user.value?.role === 'rider');

  function setSession(newUser: User, newToken: string) {
    token.value = newToken;
    user.value = newUser;
    localStorage.setItem(TOKEN_KEY, newToken);
  }

  function logout() {
    token.value = null;
    user.value = null;
    localStorage.removeItem(TOKEN_KEY);
  }

  async function init() {
    if (!token.value || user.value) return;
    try {
      const { data } = await apiClient.get<User>('/user');
      user.value = data;
    } catch (e: any) {
      if (e?.response?.status === 401) logout();
    }
  }

  return { token, user, isVendor, isAdmin, isRider, setSession, logout, init };
});
