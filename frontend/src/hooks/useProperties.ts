/* eslint-disable @typescript-eslint/no-explicit-any */
import { useState, useEffect, useCallback } from 'react';
import type { Property, PropertyFilters } from '../types';
import { propertyService } from '../services/propertyService';

interface UsePropertiesReturn {
  properties: Property[];
  loading: boolean;
  error: string | null;
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  refetch: () => Promise<void>;
  setFilters: (newFilters: PropertyFilters) => void;
}

export const useProperties = (initialFilters: PropertyFilters = {}): UsePropertiesReturn => {
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<PropertyFilters>(initialFilters);
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });

  const fetchProperties = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await propertyService.getProperties(filters);
      setProperties(response.data);
      setPagination({
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      });
    } catch (err: any) {
      setError(err.message || 'Failed to fetch properties');
    } finally {
      setLoading(false);
    }
  }, [filters]);

  // Only run when filters change
  useEffect(() => {
    fetchProperties();
  }, [filters, fetchProperties]);

  const refetch = useCallback(async () => {
    await fetchProperties();
  }, [fetchProperties]);

  return {
    properties,
    loading,
    error,
    pagination,
    refetch,
    setFilters, // rename from updateFilters
  };
};
