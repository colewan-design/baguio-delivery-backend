import apiClient from '~/lib/apiClient';

export function activateAccount(userId: number, query: Record<string, string>, password: string, passwordConfirmation: string) {
  return apiClient.post(
    `/activate/${userId}`,
    { password, password_confirmation: passwordConfirmation },
    { params: query }
  ).then((r) => r.data);
}
