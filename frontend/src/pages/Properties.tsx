import React, { useState } from 'react';
// eslint-disable-next-line @typescript-eslint/no-unused-vars
import type { Property, PropertyFilters } from '../types';
import { PropertyCard } from '../components/ui/PropertyCard';
import { SearchBar } from '../components/ui/SearchBar';
import { Loader } from '../components/common/Loader';
import { useProperties } from '../hooks/useProperties';
import { propertyService } from '../services/propertyService';

const Properties: React.FC = () => {
  const [filters, setFilters] = useState<PropertyFilters>({});
  const { properties, loading, error, pagination, refetch, updateFilters } = useProperties(filters);

  const handleSearch = (newFilters: PropertyFilters) => {
    setFilters(newFilters);
    updateFilters(newFilters);
  };

  const handleToggleFavorite = async (propertyId: number) => {
    try {
      await propertyService.toggleFavorite(propertyId);
      await refetch(); // Refresh the list to update favorite status
    } catch (error) {
      console.error('Error toggling favorite:', error);
    }
  };

  const handlePageChange = (page: number) => {
    updateFilters({ ...filters, page });
  };

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <div className="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto">
              <svg className="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 className="text-lg font-medium text-red-800 mb-2">Error Loading Properties</h3>
              <p className="text-red-700">{error}</p>
              <button
                onClick={refetch}
                className="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
              >
                Try Again
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Browse Properties</h1>
          <p className="text-lg text-gray-600">
            Discover {pagination.total} properties matching your criteria
          </p>
        </div>

        {/* Search Bar */}
        <SearchBar onSearch={handleSearch} initialFilters={filters} />

        {/* Properties Grid */}
        {loading ? (
          <div className="flex justify-center py-12">
            <Loader size="lg" text="Loading properties..." />
          </div>
        ) : (
          <>
            {/* Results Count */}
            <div className="mb-6 flex justify-between items-center">
              <p className="text-gray-600">
                Showing {properties.length} of {pagination.total} properties
              </p>
              
              {/* Sort Options */}
              <select className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="newest">Newest First</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="featured">Featured First</option>
              </select>
            </div>

            {/* Properties Grid */}
            {properties.length > 0 ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                {properties.map((property) => (
                  <PropertyCard
                    key={property.id}
                    property={property}
                    onToggleFavorite={handleToggleFavorite}
                  />
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <svg className="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 className="text-lg font-medium text-gray-900 mb-2">No Properties Found</h3>
                <p className="text-gray-500 mb-4">
                  We couldn't find any properties matching your search criteria.
                </p>
                <button
                  onClick={() => {
                    setFilters({});
                    updateFilters({});
                  }}
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
                >
                  Clear Filters
                </button>
              </div>
            )}

            {/* Pagination */}
            {pagination.last_page > 1 && (
              <div className="flex justify-center items-center space-x-2">
                <button
                  onClick={() => handlePageChange(pagination.current_page - 1)}
                  disabled={pagination.current_page === 1}
                  className="px-3 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors duration-200"
                >
                  Previous
                </button>

                {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => (
                  <button
                    key={page}
                    onClick={() => handlePageChange(page)}
                    className={`px-3 py-2 border rounded-lg transition-colors duration-200 ${
                      pagination.current_page === page
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'border-gray-300 hover:bg-gray-50'
                    }`}
                  >
                    {page}
                  </button>
                ))}

                <button
                  onClick={() => handlePageChange(pagination.current_page + 1)}
                  disabled={pagination.current_page === pagination.last_page}
                  className="px-3 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors duration-200"
                >
                  Next
                </button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default Properties;