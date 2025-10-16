import api from './api';
import type { Property, PropertyFilters, PaginatedResponse, Appointment, Inquiry } from '../types';

class PropertyService {
  async getProperties(filters: PropertyFilters = {}): Promise<PaginatedResponse<Property>> {
    const response = await api.get<PaginatedResponse<Property>>('/properties', {
      params: filters,
    });
    return response.data;
  }

  async getFeaturedProperties(): Promise<Property[]> {
    const response = await api.get<{ data: Property[] }>('/properties/featured');
    return response.data.data;
  }

  async getProperty(id: number): Promise<Property> {
    const response = await api.get<{ property: Property }>(`/properties/${id}`);
    return response.data.property;
  }

  async createProperty(propertyData: FormData): Promise<Property> {
    const response = await api.post<{ property: Property }>('/properties', propertyData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data.property;
  }

  async updateProperty(id: number, propertyData: FormData): Promise<Property> {
    const response = await api.post<{ property: Property }>(`/properties/${id}`, propertyData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data.property;
  }

  async deleteProperty(id: number): Promise<void> {
    await api.delete(`/properties/${id}`);
  }

  async toggleFavorite(propertyId: number): Promise<void> {
    await api.post(`/properties/${propertyId}/favorite`);
  }

  async getFavorites(): Promise<Property[]> {
    const response = await api.get<{ data: Property[] }>('/favorites');
    return response.data.data;
  }

  // Appointment methods
  async getAppointments(): Promise<Appointment[]> {
    const response = await api.get<{ data: Appointment[] }>('/appointments');
    return response.data.data;
  }

  async createAppointment(appointmentData: {
    property_id: number;
    appointment_date: string;
    notes?: string;
  }): Promise<Appointment> {
    const response = await api.post<{ appointment: Appointment }>('/appointments', appointmentData);
    return response.data.appointment;
  }

  async updateAppointment(id: number, status: string): Promise<Appointment> {
    const response = await api.put<{ appointment: Appointment }>(`/appointments/${id}`, { status });
    return response.data.appointment;
  }

  async deleteAppointment(id: number): Promise<void> {
    await api.delete(`/appointments/${id}`);
  }

  // Inquiry methods
  async createInquiry(propertyId: number, inquiryData: {
    name: string;
    email: string;
    phone?: string;
    message: string;
  }): Promise<Inquiry> {
    const response = await api.post<{ inquiry: Inquiry }>(
      `/properties/${propertyId}/inquiry`,
      inquiryData
    );
    return response.data.inquiry;
  }

  async getInquiries(): Promise<Inquiry[]> {
    const response = await api.get<{ data: Inquiry[] }>('/inquiries');
    return response.data.data;
  }
}

export const propertyService = new PropertyService();