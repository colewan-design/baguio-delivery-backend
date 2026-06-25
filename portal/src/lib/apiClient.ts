import axios from 'axios';
import { useAuthStore } from '~/stores/auth';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'https://baguiodelivery.salidumay.com/api',
  headers: { Accept: 'application/json' },
});

apiClient.interceptors.request.use((cfg) => {
  const token = useAuthStore().token;
  if (token) cfg.headers.Authorization = `Bearer ${token}`;
  return cfg;
});

apiClient.interceptors.response.use(
  (r) => r,
  (error) => {
    if (error.response?.status === 401) useAuthStore().logout();
    return Promise.reject(error);
  }
);

export default apiClient;
