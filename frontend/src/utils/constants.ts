export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

export const PROPERTY_TYPES = {
  house: 'House',
  apartment: 'Apartment',
  condo: 'Condo',
  townhouse: 'Townhouse',
  land: 'Land',
  commercial: 'Commercial',
} as const;

export const PROPERTY_STATUS = {
  available: 'Available',
  sold: 'Sold',
  pending: 'Pending',
  rented: 'Rented',
} as const;

export const LISTING_TYPES = {
  sale: 'For Sale',
  rent: 'For Rent',
} as const;

export const AMENITIES = [
  'Swimming Pool',
  'Garden',
  'Garage',
  'Parking',
  'Security',
  'Elevator',
  'Air Conditioning',
  'Heating',
  'Balcony',
  'Fireplace',
  'Gym',
  'Pet Friendly',
  'Furnished',
  'Internet',
  'Cable TV',
];

export const STATES = [
  'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware',
  'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky',
  'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi',
  'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico',
  'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania',
  'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
  'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
];