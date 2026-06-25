export type UserRole = 'customer' | 'vendor' | 'rider' | 'admin';

export interface User {
  id: number;
  name: string;
  email: string;
  phone: string;
  role: UserRole;
  email_verified_at: string | null;
  vendor?: Vendor;
  rider?: Rider;
}

export interface MenuItem {
  id: number;
  vendor_id: number;
  name: string;
  description: string | null;
  price: string;
  photo_url: string | null;
  is_available: boolean;
  category: string | null;
}

export interface Vendor {
  id: number;
  user_id: number;
  lead_id: number | null;
  business_name: string;
  category: 'food' | 'groceries' | 'pharmacy' | 'errands';
  address: string;
  lat: string;
  lng: string;
  phone: string;
  is_open: boolean;
  opens_at: string | null;
  closes_at: string | null;
  logo_url: string | null;
  status: 'pending' | 'approved' | 'suspended';
  user?: User;
}

export interface Rider {
  id: number;
  user_id: number;
  lead_id: number | null;
  current_lat: string | null;
  current_lng: string | null;
  status: 'offline' | 'available' | 'busy';
  vehicle_type: string | null;
  user?: User;
}

export type OrderStatus =
  | 'pending'
  | 'accepted'
  | 'rejected'
  | 'rider_assigned'
  | 'ready_for_pickup'
  | 'out_for_delivery'
  | 'completed'
  | 'cancelled';

export interface OrderItem {
  id: number;
  menu_item_id: number;
  quantity: number;
  price_at_order: string;
  menu_item?: { id: number; name: string };
}

export interface Order {
  id: number;
  customer_id: number;
  vendor_id: number;
  rider_id: number | null;
  status: OrderStatus;
  delivery_address: string;
  subtotal: string;
  delivery_fee: string;
  total: string;
  notes: string | null;
  items: OrderItem[];
  customer?: User;
  vendor?: Vendor;
  rider?: Rider;
  created_at: string;
}

export interface Lead {
  id: number;
  type: 'vendor' | 'rider';
  name: string;
  contact: string;
  message: string | null;
  status: 'new' | 'contacted' | 'converted' | 'dismissed';
  created_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}
