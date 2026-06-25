import apiClient from '~/lib/apiClient';
import type { Order, Rider } from '~/types/models';

export function listRiderJobs() { return apiClient.get<Order[]>('/rider/jobs').then((r) => r.data); }
export function pickupOrder(id: number) { return apiClient.patch<Order>(`/rider/orders/${id}/pickup`).then((r) => r.data); }
export function deliverOrder(id: number) { return apiClient.patch<Order>(`/rider/orders/${id}/deliver`).then((r) => r.data); }
export function updateRiderStatus(status: Rider['status']) {
  return apiClient.patch<Rider>('/rider/status', { status }).then((r) => r.data);
}
