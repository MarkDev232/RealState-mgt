import type { User } from "./User";

export interface Property {
  id: number;
  agent_id: number;
  title: string;
  description: string;
  address: string;
  city: string;
  state: string;
  zip_code: string;
  country: string;
  price: number;
  bedrooms?: number;
  bathrooms?: number;
  square_feet?: number;
  lot_size?: number;
  property_type: PropertyType;
  status: PropertyStatus;
  listing_type: ListingType;
  year_built?: number;
  amenities: string[];
  images: string[];
  featured: boolean;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  agent?: User;
  is_favorite?: boolean;
  primary_image?: string;
}

export type PropertyType = 'house' | 'apartment' | 'condo' | 'townhouse' | 'land' | 'commercial';
export type PropertyStatus = 'available' | 'sold' | 'pending' | 'rented';
export type ListingType = 'sale' | 'rent';

export interface PropertyFilters {
  search?: string;
  type?: PropertyType;
  listing_type?: ListingType;
  min_price?: number;
  max_price?: number;
  bedrooms?: number;
  bathrooms?: number;
  city?: string;
  state?: string;
  featured?: boolean;
  page?: number;
  per_page?: number;
}

export interface Appointment {
  id: number;
  user_id: number;
  property_id: number;
  agent_id: number;
  appointment_date: string;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  notes?: string;
  created_at: string;
  updated_at: string;
  user?: User;
  property?: Property;
  agent?: User;
}

export interface Inquiry {
  id: number;
  property_id: number;
  name: string;
  email: string;
  phone?: string;
  message: string;
  status: 'new' | 'contacted' | 'follow_up' | 'closed';
  created_at: string;
  updated_at: string;
  property?: Property;
}