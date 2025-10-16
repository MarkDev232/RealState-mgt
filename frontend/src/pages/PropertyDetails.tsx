/* eslint-disable @typescript-eslint/no-explicit-any */
import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import type { Property } from '../types';
import { Loader } from '../components/common/Loader';
import { Button } from '../components/common/Button';
import { Modal } from '../components/common/Modal';
import { Input } from '../components/common/Input';
import { propertyService } from '../services/propertyService';
import { useAuthContext } from '../context/AuthContext';
import { formatPrice, getImageUrl, formatDate } from '../utils/helpers';
import { PROPERTY_TYPES, LISTING_TYPES } from '../utils/constants';

const PropertyDetails: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { user, isAuthenticated } = useAuthContext();
  
  const [property, setProperty] = useState<Property | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeImageIndex, setActiveImageIndex] = useState(0);
  const [isInquiryModalOpen, setIsInquiryModalOpen] = useState(false);
  const [isAppointmentModalOpen, setIsAppointmentModalOpen] = useState(false);
  const [isFavorite, setIsFavorite] = useState(false);
  const [inquiryLoading, setInquiryLoading] = useState(false);
  const [appointmentLoading, setAppointmentLoading] = useState(false);

  // Inquiry form state
  const [inquiryData, setInquiryData] = useState({
    name: '',
    email: '',
    phone: '',
    message: '',
  });

  // Appointment form state
  const [appointmentData, setAppointmentData] = useState({
    appointment_date: '',
    notes: '',
  });

  useEffect(() => {
    const loadProperty = async () => {
      if (!id) return;
      
      try {
        setLoading(true);
        const propertyData = await propertyService.getProperty(parseInt(id));
        setProperty(propertyData);
        setIsFavorite(propertyData.is_favorite || false);
      } catch (err: any) {
        setError(err.message || 'Failed to load property');
      } finally {
        setLoading(false);
      }
    };

    loadProperty();
  }, [id]);

  const handleToggleFavorite = async () => {
    if (!property || !isAuthenticated) return;
    
    try {
      await propertyService.toggleFavorite(property.id);
      setIsFavorite(!isFavorite);
    } catch (error) {
      console.error('Error toggling favorite:', error);
    }
  };

  const handleInquirySubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!property) return;

    setInquiryLoading(true);
    try {
      await propertyService.createInquiry(property.id, inquiryData);
      setIsInquiryModalOpen(false);
      setInquiryData({ name: '', email: '', phone: '', message: '' });
      alert('Your inquiry has been sent successfully!');
    } catch (error: any) {
      alert(error.message || 'Failed to send inquiry');
    } finally {
      setInquiryLoading(false);
    }
  };

  const handleAppointmentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!property) return;

    setAppointmentLoading(true);
    try {
      await propertyService.createAppointment({
        property_id: property.id,
        appointment_date: appointmentData.appointment_date,
        notes: appointmentData.notes,
      });
      setIsAppointmentModalOpen(false);
      setAppointmentData({ appointment_date: '', notes: '' });
      alert('Appointment scheduled successfully!');
    } catch (error: any) {
      alert(error.message || 'Failed to schedule appointment');
    } finally {
      setAppointmentLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <Loader size="lg" text="Loading property details..." />
      </div>
    );
  }

  if (error || !property) {
    return (
      <div className="min-h-screen bg-gray-50 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <div className="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto">
              <svg className="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 className="text-lg font-medium text-red-800 mb-2">
                {error || 'Property not found'}
              </h3>
              <Link
                to="/properties"
                className="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
              >
                Back to Properties
              </Link>
            </div>
          </div>
        </div>
      </div>
    );
  }

  const images = property.images && property.images.length > 0 
    ? property.images 
    : ['default-property.jpg'];

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Breadcrumb */}
        <nav className="mb-8">
          <ol className="flex items-center space-x-2 text-sm text-gray-600">
            <li>
              <Link to="/" className="hover:text-blue-600 transition-colors duration-200">
                Home
              </Link>
            </li>
            <li className="flex items-center">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
              <Link to="/properties" className="ml-2 hover:text-blue-600 transition-colors duration-200">
                Properties
              </Link>
            </li>
            <li className="flex items-center">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
              <span className="ml-2 text-gray-900 font-medium">{property.title}</span>
            </li>
          </ol>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2">
            {/* Image Gallery */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
              <div className="relative h-96 bg-gray-200">
                <img
                  src={getImageUrl(images[activeImageIndex])}
                  alt={property.title}
                  className="w-full h-full object-cover"
                />
                
                {/* Favorite Button */}
                {isAuthenticated && (
                  <button
                    onClick={handleToggleFavorite}
                    className="absolute top-4 right-4 p-3 bg-white rounded-full shadow-lg hover:bg-gray-50 transition-colors duration-200"
                  >
                    <svg
                      className={`w-6 h-6 ${
                        isFavorite ? 'text-red-500 fill-current' : 'text-gray-400'
                      }`}
                      fill={isFavorite ? 'currentColor' : 'none'}
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

                {/* Image Navigation */}
                {images.length > 1 && (
                  <>
                    <button
                      onClick={() => setActiveImageIndex(prev => prev > 0 ? prev - 1 : images.length - 1)}
                      className="absolute left-4 top-1/2 transform -translate-y-1/2 p-2 bg-white rounded-full shadow-lg hover:bg-gray-50 transition-colors duration-200"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>
                    <button
                      onClick={() => setActiveImageIndex(prev => prev < images.length - 1 ? prev + 1 : 0)}
                      className="absolute right-4 top-1/2 transform -translate-y-1/2 p-2 bg-white rounded-full shadow-lg hover:bg-gray-50 transition-colors duration-200"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </>
                )}
              </div>

              {/* Thumbnails */}
              {images.length > 1 && (
                <div className="p-4 border-t border-gray-200">
                  <div className="flex space-x-2 overflow-x-auto">
                    {images.map((image, index) => (
                      <button
                        key={index}
                        onClick={() => setActiveImageIndex(index)}
                        className={`flex-shrink-0 w-20 h-20 rounded-lg border-2 overflow-hidden ${
                          activeImageIndex === index ? 'border-blue-500' : 'border-gray-300'
                        }`}
                      >
                        <img
                          src={getImageUrl(image)}
                          alt={`${property.title} ${index + 1}`}
                          className="w-full h-full object-cover"
                        />
                      </button>
                    ))}
                  </div>
                </div>
              )}
            </div>

            {/* Property Details */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <h1 className="text-3xl font-bold text-gray-900 mb-4">{property.title}</h1>
              
              <div className="flex items-center space-x-4 mb-6">
                <span className="text-3xl font-bold text-blue-600">
                  {formatPrice(property.price)}
                  {property.listing_type === 'rent' && <span className="text-lg text-gray-600">/month</span>}
                </span>
                <span className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                  property.status === 'available' ? 'bg-green-100 text-green-800' :
                  property.status === 'sold' ? 'bg-red-100 text-red-800' :
                  property.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-blue-100 text-blue-800'
                }`}>
                  {property.status.charAt(0).toUpperCase() + property.status.slice(1)}
                </span>
                {property.featured && (
                  <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    Featured
                  </span>
                )}
              </div>

              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                {property.bedrooms && (
                  <div className="text-center p-3 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">{property.bedrooms}</div>
                    <div className="text-sm text-gray-600">Bedrooms</div>
                  </div>
                )}
                {property.bathrooms && (
                  <div className="text-center p-3 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">{property.bathrooms}</div>
                    <div className="text-sm text-gray-600">Bathrooms</div>
                  </div>
                )}
                {property.square_feet && (
                  <div className="text-center p-3 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">
                      {property.square_feet.toLocaleString()}
                    </div>
                    <div className="text-sm text-gray-600">Square Feet</div>
                  </div>
                )}
                {property.year_built && (
                  <div className="text-center p-3 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">{property.year_built}</div>
                    <div className="text-sm text-gray-600">Year Built</div>
                  </div>
                )}
              </div>

              <div className="prose max-w-none">
                <h3 className="text-xl font-semibold text-gray-900 mb-3">Description</h3>
                <p className="text-gray-700 leading-relaxed">{property.description}</p>
              </div>
            </div>

            {/* Amenities */}
            {property.amenities && property.amenities.length > 0 && (
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 className="text-xl font-semibold text-gray-900 mb-4">Amenities</h3>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                  {property.amenities.map((amenity, index) => (
                    <div key={index} className="flex items-center space-x-2">
                      <svg className="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-gray-700">{amenity}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Location */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-xl font-semibold text-gray-900 mb-4">Location</h3>
              <div className="space-y-2 text-gray-700">
                <p>{property.address}</p>
                <p>{property.city}, {property.state} {property.zip_code}</p>
                <p>{property.country}</p>
              </div>
              <div className="mt-4 h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                <p className="text-gray-500">Map would be displayed here</p>
              </div>
            </div>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Agent Info */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Contact Agent</h3>
              {property.agent && (
                <div className="flex items-center space-x-3 mb-4">
                  <div className="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                    <span className="text-lg font-medium text-gray-600">
                      {property.agent.name.charAt(0).toUpperCase()}
                    </span>
                  </div>
                  <div>
                    <h4 className="font-medium text-gray-900">{property.agent.name}</h4>
                    <p className="text-sm text-gray-600">Real Estate Agent</p>
                  </div>
                </div>
              )}
              <div className="space-y-3">
                <Button
                  onClick={() => setIsInquiryModalOpen(true)}
                  className="w-full"
                >
                  Send Inquiry
                </Button>
                <Button
                  onClick={() => setIsAppointmentModalOpen(true)}
                  variant="outline"
                  className="w-full"
                >
                  Schedule Viewing
                </Button>
              </div>
            </div>

            {/* Property Facts */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Property Facts</h3>
              <dl className="space-y-3">
                <div className="flex justify-between">
                  <dt className="text-gray-600">Type</dt>
                  <dd className="font-medium text-gray-900">{PROPERTY_TYPES[property.property_type]}</dd>
                </div>
                <div className="flex justify-between">
                  <dt className="text-gray-600">Listing Type</dt>
                  <dd className="font-medium text-gray-900">{LISTING_TYPES[property.listing_type]}</dd>
                </div>
                {property.lot_size && (
                  <div className="flex justify-between">
                    <dt className="text-gray-600">Lot Size</dt>
                    <dd className="font-medium text-gray-900">{property.lot_size.toLocaleString()} sq ft</dd>
                  </div>
                )}
                <div className="flex justify-between">
                  <dt className="text-gray-600">Listed</dt>
                  <dd className="font-medium text-gray-900">{formatDate(property.created_at)}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>

      {/* Inquiry Modal */}
      <Modal
        isOpen={isInquiryModalOpen}
        onClose={() => setIsInquiryModalOpen(false)}
        title="Send Inquiry"
        size="md"
      >
        <form onSubmit={handleInquirySubmit} className="space-y-4">
          <Input
            label="Name"
            value={inquiryData.name}
            onChange={(e) => setInquiryData(prev => ({ ...prev, name: e.target.value }))}
            required
          />
          <Input
            label="Email"
            type="email"
            value={inquiryData.email}
            onChange={(e) => setInquiryData(prev => ({ ...prev, email: e.target.value }))}
            required
          />
          <Input
            label="Phone"
            value={inquiryData.phone}
            onChange={(e) => setInquiryData(prev => ({ ...prev, phone: e.target.value }))}
          />
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Message
            </label>
            <textarea
              value={inquiryData.message}
              onChange={(e) => setInquiryData(prev => ({ ...prev, message: e.target.value }))}
              rows={4}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            />
          </div>
          <div className="flex space-x-3 pt-4">
            <Button
              type="submit"
              loading={inquiryLoading}
              className="flex-1"
            >
              Send Inquiry
            </Button>
            <Button
              type="button"
              variant="outline"
              onClick={() => setIsInquiryModalOpen(false)}
            >
              Cancel
            </Button>
          </div>
        </form>
      </Modal>

      {/* Appointment Modal */}
      <Modal
        isOpen={isAppointmentModalOpen}
        onClose={() => setIsAppointmentModalOpen(false)}
        title="Schedule Viewing"
        size="md"
      >
        <form onSubmit={handleAppointmentSubmit} className="space-y-4">
          <Input
            label="Date and Time"
            type="datetime-local"
            value={appointmentData.appointment_date}
            onChange={(e) => setAppointmentData(prev => ({ ...prev, appointment_date: e.target.value }))}
            required
          />
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Notes (Optional)
            </label>
            <textarea
              value={appointmentData.notes}
              onChange={(e) => setAppointmentData(prev => ({ ...prev, notes: e.target.value }))}
              rows={3}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Any specific requirements or questions..."
            />
          </div>
          <div className="flex space-x-3 pt-4">
            <Button
              type="submit"
              loading={appointmentLoading}
              className="flex-1"
            >
              Schedule Viewing
            </Button>
            <Button
              type="button"
              variant="outline"
              onClick={() => setIsAppointmentModalOpen(false)}
            >
              Cancel
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
};

export default PropertyDetails;