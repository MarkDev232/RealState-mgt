import React from 'react';
import { Link } from 'react-router-dom';
import type { Property } from '../../types';
import { formatPrice, getImageUrl, truncateText } from '../../utils/helpers';
import { PROPERTY_TYPES, LISTING_TYPES } from '../../utils/constants';

interface PropertyCardProps {
  property: Property;
  onToggleFavorite?: (propertyId: number) => void;
}

export const PropertyCard: React.FC<PropertyCardProps> = ({
  property,
  onToggleFavorite,
}) => {
  const primaryImage = property.primary_image || 
    (property.images && property.images.length > 0 ? property.images[0] : null);

  const handleFavoriteClick = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (onToggleFavorite) {
      onToggleFavorite(property.id);
    }
  };

  return (
    <Link
      to={`/properties/${property.id}`}
      className="block bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 overflow-hidden group"
    >
      {/* Image */}
      <div className="relative h-48 bg-gray-200 overflow-hidden">
        {primaryImage ? (
          <img
            src={getImageUrl(primaryImage)}
            alt={property.title}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-gray-100">
            <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 22V12h6v10" />
            </svg>
          </div>
        )}
        
        {/* Favorite Button */}
        {onToggleFavorite && (
          <button
            onClick={handleFavoriteClick}
            className="absolute top-3 right-3 p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition-colors duration-200"
          >
            <svg
              className={`w-5 h-5 ${
                property.is_favorite ? 'text-red-500 fill-current' : 'text-gray-400'
              }`}
              fill={property.is_favorite ? 'currentColor' : 'none'}
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
              />
            </svg>
          </button>
        )}

        {/* Status Badge */}
        <div className="absolute top-3 left-3">
          <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
            property.status === 'available' ? 'bg-green-100 text-green-800' :
            property.status === 'sold' ? 'bg-red-100 text-red-800' :
            property.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
            'bg-blue-100 text-blue-800'
          }`}>
            {property.status.charAt(0).toUpperCase() + property.status.slice(1)}
          </span>
        </div>

        {/* Type Badge */}
        <div className="absolute bottom-3 left-3">
          <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {PROPERTY_TYPES[property.property_type]}
          </span>
        </div>
      </div>

      {/* Content */}
      <div className="p-4">
        {/* Price */}
        <div className="mb-2">
          <span className="text-2xl font-bold text-gray-900">
            {formatPrice(property.price)}
          </span>
          <span className="text-sm text-gray-500 ml-1">
            {property.listing_type === 'rent' ? '/month' : ''}
          </span>
        </div>

        {/* Title */}
        <h3 className="font-semibold text-gray-900 mb-2 line-clamp-1">
          {property.title}
        </h3>

        {/* Address */}
        <p className="text-gray-600 text-sm mb-3 line-clamp-1">
          {truncateText(property.address, 40)}
        </p>

        {/* Details */}
        <div className="flex items-center justify-between text-sm text-gray-500 mb-3">
          {property.bedrooms && (
            <div className="flex items-center space-x-1">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span>{property.bedrooms} bed</span>
            </div>
          )}
          
          {property.bathrooms && (
            <div className="flex items-center space-x-1">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{property.bathrooms} bath</span>
            </div>
          )}
          
          {property.square_feet && (
            <div className="flex items-center space-x-1">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
              </svg>
              <span>{property.square_feet.toLocaleString()} sq ft</span>
            </div>
          )}
        </div>

        {/* Listing Type */}
        <div className="flex items-center justify-between">
          <span className="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
            {LISTING_TYPES[property.listing_type]}
          </span>
          {property.featured && (
            <span className="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
              Featured
            </span>
          )}
        </div>
      </div>
    </Link>
  );
};