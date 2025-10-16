/* eslint-disable @typescript-eslint/no-explicit-any */
import React, { useState, useEffect } from 'react';
import type { PropertyFilters } from '../../types';
import { PROPERTY_TYPES, LISTING_TYPES, STATES } from '../../utils/constants';
import { debounce } from '../../utils/helpers';

interface SearchBarProps {
  onSearch: (filters: PropertyFilters) => void;
  initialFilters?: PropertyFilters;
}

export const SearchBar: React.FC<SearchBarProps> = ({
  onSearch,
  initialFilters = {},
}) => {
  const [filters, setFilters] = useState<PropertyFilters>({
    search: ' ',
    type: 'apartment',
    listing_type: undefined,
    min_price: undefined,
    max_price: undefined,
    bedrooms: undefined,
    bathrooms: undefined,
    city: '',
    state: '',
    ...initialFilters,
  });

  const [showAdvanced, setShowAdvanced] = useState(false);

  // Debounced search to avoid too many API calls
  const debouncedSearch = debounce((newFilters: PropertyFilters) => {
    onSearch(newFilters);
  }, 500);

  useEffect(() => {
    debouncedSearch(filters);
  }, [filters, debouncedSearch]);

  const handleInputChange = (field: keyof PropertyFilters, value: any) => {
    setFilters(prev => ({
      ...prev,
      [field]: value === '' ? undefined : value,
    }));
  };

  const handlePriceChange = (min: number, max: number) => {
    setFilters(prev => ({
      ...prev,
      min_price: min > 0 ? min : undefined,
      max_price: max > 0 ? max : undefined,
    }));
  };

  const clearFilters = () => {
    setFilters({
      search: '',
      type: 'apartment',
      listing_type: 'rent',
      min_price: undefined,
      max_price: undefined,
      bedrooms: undefined,
      bathrooms: undefined,
      city: '',
      state: '',
    });
  };

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      {/* Basic Search */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        {/* Search Input */}
        <div className="md:col-span-2">
          <input
            type="text"
            placeholder="Search by title, location, or description..."
            value={filters.search || ''}
            onChange={(e) => handleInputChange('search', e.target.value)}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        {/* Property Type */}
        <div>
          <select
            value={filters.type || ''}
            onChange={(e) => handleInputChange('type', e.target.value)}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">All Types</option>
            {Object.entries(PROPERTY_TYPES).map(([value, label]) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
        </div>

        {/* Listing Type */}
        <div>
          <select
            value={filters.listing_type || ''}
            onChange={(e) => handleInputChange('listing_type', e.target.value)}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">All Listings</option>
            {Object.entries(LISTING_TYPES).map(([value, label]) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* Advanced Filters Toggle */}
      <div className="flex justify-between items-center mb-4">
        <button
          onClick={() => setShowAdvanced(!showAdvanced)}
          className="text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1"
        >
          <span>Advanced Filters</span>
          <svg
            className={`w-4 h-4 transition-transform duration-200 ${
              showAdvanced ? 'rotate-180' : ''
            }`}
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <button
          onClick={clearFilters}
          className="text-gray-600 hover:text-gray-700 text-sm"
        >
          Clear All
        </button>
      </div>

      {/* Advanced Filters */}
      {showAdvanced && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
          {/* Price Range */}
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Price Range</label>
            <div className="flex space-x-2">
              <input
                type="number"
                placeholder="Min"
                value={filters.min_price || ''}
                onChange={(e) => handlePriceChange(Number(e.target.value), filters.max_price || 0)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
              <input
                type="number"
                placeholder="Max"
                value={filters.max_price || ''}
                onChange={(e) => handlePriceChange(filters.min_price || 0, Number(e.target.value))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
          </div>

          {/* Bedrooms */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Bedrooms</label>
            <select
              value={filters.bedrooms || ''}
              onChange={(e) => handleInputChange('bedrooms', e.target.value ? Number(e.target.value) : undefined)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              <option value="">Any</option>
              <option value="1">1+</option>
              <option value="2">2+</option>
              <option value="3">3+</option>
              <option value="4">4+</option>
              <option value="5">5+</option>
            </select>
          </div>

          {/* Bathrooms */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Bathrooms</label>
            <select
              value={filters.bathrooms || ''}
              onChange={(e) => handleInputChange('bathrooms', e.target.value ? Number(e.target.value) : undefined)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              <option value="">Any</option>
              <option value="1">1+</option>
              <option value="2">2+</option>
              <option value="3">3+</option>
              <option value="4">4+</option>
            </select>
          </div>

          {/* Location */}
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Location</label>
            <div className="flex space-x-2">
              <input
                type="text"
                placeholder="City"
                value={filters.city || ''}
                onChange={(e) => handleInputChange('city', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
              <select
                value={filters.state || ''}
                onChange={(e) => handleInputChange('state', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="">State</option>
                {STATES.map(state => (
                  <option key={state} value={state}>
                    {state}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};