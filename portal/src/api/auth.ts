import apiClient from '~/lib/apiClient';
import type { User } from '~/types/models';

interface AuthResponse { user: User; token: string; }

export function login(email: string, password: string) {
  return apiClient.post<AuthResponse>('/login', { email, password }).then((r) => r.data);
}

export function logout() {
  return apiClient.post('/logout');
}
