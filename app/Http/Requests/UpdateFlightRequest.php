<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Flight;

class UpdateFlightRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('travel_agent');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'airline' => 'required|string|max:255',
            'flight_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z]{2}[0-9]{1,4}[A-Z]?$/',
                Rule::unique('flights')->where(function ($query) {
                    return $query->where('departure_datetime', $this->departure_datetime)
                                 ->where('id', '!=', $this->route('flight')->id);
                })
            ],
            'departure_airport' => 'required|string|max:255',
            'arrival_airport' => 'required|string|max:255|different:departure_airport',
            'departure_datetime' => 'required|date',
            'arrival_datetime' => 'required|date|after:departure_datetime',
            'total_seats' => 'required|integer|min:1|max:1000',
            'available_seats' => 'required|integer|min:0|lte:total_seats',
            'economy_price' => 'required|numeric|min:0|max:999999.99',
            'business_price' => 'nullable|numeric|min:0|max:999999.99|gt:economy_price',
            'first_class_price' => 'nullable|numeric|min:0|max:999999.99|gt:business_price',
            'currency' => 'required|string|size:3',
            'aircraft_type' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'status' => ['nullable', Rule::in(Flight::STATUSES)],
            'baggage_allowance' => 'nullable|array',
            'baggage_allowance.carry_on' => 'nullable|string|max:100',
            'baggage_allowance.checked' => 'nullable|string|max:100',
            'meal_service' => 'nullable|array',
            'meal_service.economy' => 'nullable|string|max:255',
            'meal_service.business' => 'nullable|string|max:255',
            'meal_service.first_class' => 'nullable|string|max:255',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'airline.required' => 'Airline name is required.',
            'flight_number.required' => 'Flight number is required.',
            'flight_number.regex' => 'Flight number must be in valid format (e.g., AA123, BA456A).',
            'flight_number.unique' => 'A flight with this number already exists for the selected departure time.',
            'departure_airport.required' => 'Departure airport is required.',
            'arrival_airport.required' => 'Arrival airport is required.',
            'arrival_airport.different' => 'Arrival airport must be different from departure airport.',
            'departure_datetime.required' => 'Departure date and time is required.',
            'arrival_datetime.required' => 'Arrival date and time is required.',
            'arrival_datetime.after' => 'Arrival must be after departure.',
            'total_seats.required' => 'Total seats is required.',
            'total_seats.min' => 'Total seats must be at least 1.',
            'available_seats.required' => 'Available seats is required.',
            'available_seats.lte' => 'Available seats cannot exceed total seats.',
            'economy_price.required' => 'Economy class price is required.',
            'economy_price.min' => 'Economy class price must be at least 0.',
            'business_price.gt' => 'Business class price must be higher than economy price.',
            'first_class_price.gt' => 'First class price must be higher than business class price.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a valid 3-character code.',
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'departure_datetime' => 'departure date and time',
            'arrival_datetime' => 'arrival date and time',
            'total_seats' => 'total seats',
            'available_seats' => 'available seats',
            'economy_price' => 'economy class price',
            'business_price' => 'business class price',
            'first_class_price' => 'first class price',
            'aircraft_type' => 'aircraft type',
            'baggage_allowance' => 'baggage allowance',
            'meal_service' => 'meal service',
        ];
    }
}
