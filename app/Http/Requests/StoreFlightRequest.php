<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Flight;

class StoreFlightRequest extends FormRequest
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
            // Basic flight information
            'airline' => 'required|string|max:255',
            'flight_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z]{2}[0-9]{1,4}[A-Z]?$/',
                Rule::unique('flights')->where(function ($query) {
                    return $query->where('departure_datetime', $this->departure_datetime);
                })
            ],
            'trip_type' => ['required', Rule::in(Flight::TRIP_TYPES)],
            'return_flight_number' => 'nullable|string|max:20|regex:/^[A-Z]{2}[0-9]{1,4}[A-Z]?$/',
            
            // Route and schedule
            'departure_airport' => 'required|string|max:255',
            'arrival_airport' => 'required|string|max:255|different:departure_airport',
            'departure_datetime' => 'required|date|after:now',
            'arrival_datetime' => 'required|date|after:departure_datetime',
            'return_departure_datetime' => 'nullable|date|after:arrival_datetime',
            'return_arrival_datetime' => 'nullable|date|after:return_departure_datetime',
            
            // Seating
            'total_seats' => 'required|integer|min:10|max:1000', // Minimum 10 for group bookings
            'available_seats' => 'required|integer|min:0|lte:total_seats',
            
            // Group booking settings
            'is_group_booking' => 'nullable|boolean',
            'min_group_size' => 'required_if:is_group_booking,true|integer|min:5|max:100',
            'max_group_size' => 'required_if:is_group_booking,true|integer|gte:min_group_size|lte:total_seats',
            'group_discount_percentage' => 'nullable|numeric|min:0|max:50',
            'booking_deadline' => 'nullable|date|after:today|before:departure_datetime',
            
            // Agent collaboration
            'allows_agent_collaboration' => 'nullable|boolean',
            'collaboration_commission_percentage' => 'required_if:allows_agent_collaboration,true|numeric|min:1|max:20',
            'collaboration_terms' => 'nullable|string|max:1000',
            
            // Pricing
            'economy_price' => 'required|numeric|min:0|max:999999.99',
            'group_economy_price' => 'nullable|numeric|min:0|max:999999.99|lte:economy_price',
            'business_price' => 'nullable|numeric|min:0|max:999999.99|gt:economy_price',
            'group_business_price' => 'nullable|numeric|min:0|max:999999.99|lte:business_price',
            'first_class_price' => 'nullable|numeric|min:0|max:999999.99|gt:business_price',
            'group_first_class_price' => 'nullable|numeric|min:0|max:999999.99|lte:first_class_price',
            'currency' => 'required|string|size:3',
            
            // Additional information
            'aircraft_type' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            'included_services' => 'nullable|array',
            'included_services.*' => 'string|max:200',
            'description' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:2000',
            'payment_terms' => ['required', Rule::in(Flight::PAYMENT_TERMS)],
            'is_active' => 'nullable|boolean',
            'status' => ['nullable', Rule::in(Flight::STATUSES)],
            
            // Services
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
            'departure_datetime.after' => 'Departure must be in the future.',
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
            
            // Group booking validation messages
            'min_group_size.required_if' => 'Minimum group size is required when group booking is enabled.',
            'min_group_size.integer' => 'Minimum group size must be a valid number.',
            'min_group_size.min' => 'Minimum group size must be at least 5 passengers.',
            'min_group_size.max' => 'Minimum group size cannot exceed 100 passengers.',
            
            'max_group_size.required_if' => 'Maximum group size is required when group booking is enabled.',
            'max_group_size.integer' => 'Maximum group size must be a valid number.',
            'max_group_size.gte' => 'Maximum group size must be greater than or equal to minimum group size.',
            'max_group_size.lte' => 'Maximum group size cannot exceed total seats.',
            
            'group_discount_percentage.numeric' => 'Group discount must be a valid percentage.',
            'group_discount_percentage.min' => 'Group discount cannot be negative.',
            'group_discount_percentage.max' => 'Group discount cannot exceed 50%.',
            
            'collaboration_commission_percentage.required_if' => 'Commission percentage is required when agent collaboration is enabled.',
            'collaboration_commission_percentage.numeric' => 'Commission percentage must be a valid number.',
            'collaboration_commission_percentage.min' => 'Commission percentage must be at least 1%.',
            'collaboration_commission_percentage.max' => 'Commission percentage cannot exceed 20%.',
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
            
            // Group booking attributes
            'min_group_size' => 'minimum group size',
            'max_group_size' => 'maximum group size',
            'group_discount_percentage' => 'group discount percentage',
            'booking_deadline' => 'booking deadline',
            'collaboration_commission_percentage' => 'collaboration commission percentage',
            'collaboration_terms' => 'collaboration terms',
            'group_economy_price' => 'group economy price',
            'group_business_price' => 'group business price',
            'group_first_class_price' => 'group first class price',
            'payment_terms' => 'payment terms',
            'trip_type' => 'trip type',
            'return_flight_number' => 'return flight number',
            'return_departure_datetime' => 'return departure date and time',
            'return_arrival_datetime' => 'return arrival date and time',
        ];
    }
}
