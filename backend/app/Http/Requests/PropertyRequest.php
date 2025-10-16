<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'country' => 'sometimes|string|max:100',
            'price' => 'required|numeric|min:0',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'square_feet' => 'sometimes|integer|min:0',
            'lot_size' => 'sometimes|integer|min:0',
            'property_type' => 'required|in:house,apartment,condo,townhouse,land,commercial',
            'listing_type' => 'required|in:sale,rent',
            'year_built' => 'sometimes|integer|min:1800|max:' . (date('Y') + 1),
            'amenities' => 'sometimes|array',
            'amenities.*' => 'string|max:100',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'featured' => 'sometimes|boolean',
        ];

        // For update, make some fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['title'] = 'sometimes|string|max:255';
            $rules['description'] = 'sometimes|string';
            $rules['address'] = 'sometimes|string|max:500';
            $rules['price'] = 'sometimes|numeric|min:0';
            $rules['property_type'] = 'sometimes|in:house,apartment,condo,townhouse,land,commercial';
            $rules['listing_type'] = 'sometimes|in:sale,rent';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required',
            'description.required' => 'Property description is required',
            'address.required' => 'Property address is required',
            'city.required' => 'City is required',
            'state.required' => 'State is required',
            'zip_code.required' => 'ZIP code is required',
            'price.required' => 'Property price is required',
            'price.numeric' => 'Price must be a valid number',
            'property_type.required' => 'Property type is required',
            'listing_type.required' => 'Listing type is required',
            'images.*.image' => 'Each file must be an image',
            'images.*.max' => 'Each image must not exceed 5MB',
        ];
    }
}