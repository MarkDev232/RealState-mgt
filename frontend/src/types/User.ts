export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'agent' | 'client';
  phone?: string;
  address?: string;
  avatar?: string;
  is_active: boolean;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role?: 'agent' | 'client';
  phone?: string;
  address?: string;
}

export interface AuthResponse {
  user: User;
  access_token: string;
  token_type: string;
}