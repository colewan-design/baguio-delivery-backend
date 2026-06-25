import apiClient from '~/lib/apiClient';
import type { MenuItem, Order, OrderStatus, PaginatedResponse, Vendor } from '~/types/models';

export function listVendorOrders(status?: OrderStatus) {
  return apiClient.get<PaginatedResponse<Order>>('/vendor/orders', { params: { status } }).then((r) => r.data);
}

export function acceptOrder(id: number) { return apiClient.patch<Order>(`/vendor/orders/${id}/accept`).then((r) => r.data); }
export function rejectOrder(id: number) { return apiClient.patch<Order>(`/vendor/orders/${id}/reject`).then((r) => r.data); }
export function markOrderReady(id: number) { return apiClient.patch<Order>(`/vendor/orders/${id}/ready`).then((r) => r.data); }

export function listMenuItems() { return apiClient.get<MenuItem[]>('/vendor/menu').then((r) => r.data); }

export interface MenuItemPayload {
  name: string; description?: string | null; price: number;
  photo?: File | null; category?: string | null; is_available?: boolean;
}

function toFormData(payload: Record<string, unknown>): FormData {
  const fd = new FormData();
  for (const [key, val] of Object.entries(payload)) {
    if (val === null || val === undefined) continue;
    if (val instanceof File) fd.append(key, val);
    else fd.append(key, String(val));
  }
  return fd;
}

export function createMenuItem(payload: MenuItemPayload) {
  return apiClient.post<MenuItem>('/vendor/menu', toFormData(payload as unknown as Record<string, unknown>), {
    headers: { 'Content-Type': 'multipart/form-data' },
  }).then((r: { data: MenuItem }) => r.data);
}

export function updateMenuItem(id: number, payload: Partial<MenuItemPayload>) {
  if (payload.photo) {
    const fd = toFormData({ ...(payload as unknown as Record<string, unknown>), _method: 'PATCH' });
    return apiClient.post<MenuItem>(`/vendor/menu/${id}`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }).then((r: { data: MenuItem }) => r.data);
  }
  return apiClient.patch<MenuItem>(`/vendor/menu/${id}`, payload).then((r: { data: MenuItem }) => r.data);
}

export function deleteMenuItem(id: number) { return apiClient.delete(`/vendor/menu/${id}`); }

export function updateVendorOpenStatus(isOpen: boolean) {
  return apiClient.patch<Vendor>('/vendor/status', { is_open: isOpen }).then((r) => r.data);
}
