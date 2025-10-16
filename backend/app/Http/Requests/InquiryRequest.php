<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquiryRequest extends FormRequest
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
        return [
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'sometimes|string|max:20',
            'message' => 'required|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'property_id.required' => 'Property selection is required',
            'property_id.exists' => 'Selected property does not exist',
            'name.required' => 'Your name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'message.required' => 'Please enter your message',
            'message.max' => 'Message must not exceed 2000 characters',
        ];
   }
}