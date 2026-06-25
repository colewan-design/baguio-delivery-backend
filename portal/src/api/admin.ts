import apiClient from '~/lib/apiClient';
import type { Lead, Order, PaginatedResponse, Rider, Vendor } from '~/types/models';

export function listVendors(status?: Vendor['status']) {
  return apiClient.get<PaginatedResponse<Vendor>>('/admin/vendors', { params: { status } }).then((r) => r.data);
}

export interface CreateVendorPayload {
  name: string; email: string; phone: string;
  business_name: string; category: 'food' | 'groceries' | 'pharmacy' | 'errands';
  address: string; lat: number; lng: number; vendor_phone: string;
  opens_at?: string; closes_at?: string; logo_url?: string; lead_id?: number;
}

export function createVendor(payload: CreateVendorPayload) {
  return apiClient.post<Vendor>('/admin/vendors', payload).then((r) => r.data);
}

export function updateVendorStatus(vendorId: number, status: Vendor['status']) {
  return apiClient.patch<Vendor>(`/admin/vendors/${vendorId}`, { status }).then((r) => r.data);
}

export function listOrders(status?: string) {
  return apiClient.get<PaginatedResponse<Order>>('/admin/orders', { params: { status } }).then((r) => r.data);
}

export function getOrder(orderId: number) {
  return apiClient.get<Order>(`/admin/orders/${orderId}`).then((r) => r.data);
}

export function reassignRider(orderId: number, riderId: number) {
  return apiClient.patch<Order>(`/admin/orders/${orderId}/reassign-rider`, { rider_id: riderId }).then((r) => r.data);
}

export function listRiders(status?: Rider['status']) {
  return apiClient.get<PaginatedResponse<Rider>>('/admin/riders', { params: { status } }).then((r) => r.data);
}

export interface CreateRiderPayload {
  name: string; email: string; phone: string; vehicle_type?: string; lead_id?: number;
}

export function createRider(payload: CreateRiderPayload) {
  return apiClient.post<Rider>('/admin/riders', payload).then((r) => r.data);
}

export function listLeads(status?: Lead['status']) {
  return apiClient.get<PaginatedResponse<Lead>>('/admin/leads', { params: { status } }).then((r) => r.data);
}

export function updateLeadStatus(leadId: number, status: Lead['status']) {
  return apiClient.patch<Lead>(`/admin/leads/${leadId}`, { status }).then((r) => r.data);
}
