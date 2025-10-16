<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
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
            'property_id' => 'required|exists:properties,id',
            'agent_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after:now',
            'notes' => 'sometimes|string|max:1000',
        ];

        // For update, make some fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['property_id'] = 'sometimes|exists:properties,id';
            $rules['agent_id'] = 'sometimes|exists:users,id';
            $rules['appointment_date'] = 'sometimes|date|after:now';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'property_id.required' => 'Property selection is required',
            'property_id.exists' => 'Selected property does not exist',
            'agent_id.required' => 'Agent selection is required',
            'agent_id.exists' => 'Selected agent does not exist',
            'appointment_date.required' => 'Appointment date and time is required',
            'appointment_date.date' => 'Please select a valid date and time',
            'appointment_date.after' => 'Appointment must be scheduled for a future date and time',
        ];
    }
}