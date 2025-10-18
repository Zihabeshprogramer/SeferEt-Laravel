<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Room;

class StoreRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('hotel_provider');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => 'required|exists:hotels,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('rooms')->where(function ($query) {
                    return $query->where('hotel_id', $this->hotel_id);
                })
            ],
            'room_number_input' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d+(-\d+)?$/',
                function ($attribute, $value, $fail) {
                    try {
                        Room::parseRoomNumberInput($value);
                    } catch (\InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                },
            ],
            'category' => [
                'required',
                'string',
                Rule::in(array_keys(Room::getRoomTypeCategories()))
            ],
            'base_price' => 'required|numeric|min:0|max:99999.99',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'bed_type' => ['required', 'string', Rule::in(array_keys(Room::BED_TYPES))],
            'bed_count' => 'required|integer|min:1|max:10',
            'size_sqm' => 'nullable|numeric|min:0|max:9999.99',
            'description' => 'nullable|string|max:2000',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:50',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Room name is required.',
            'name.unique' => 'A room with this name already exists in this hotel.',
            'room_number_input.required' => 'Room number is required.',
            'room_number_input.regex' => 'Room number must be a single number (e.g., "105") or a range (e.g., "125-130").',
            'category.required' => 'Room category is required.',
            'category.in' => 'Please select a valid room category.',
            'base_price.required' => 'Base price is required.',
            'base_price.min' => 'Base price must be at least $0.00.',
            'max_occupancy.required' => 'Maximum occupancy is required.',
            'max_occupancy.min' => 'Maximum occupancy must be at least 1 guest.',
            'bed_type.required' => 'Bed type is required.',
            'bed_count.required' => 'Number of beds is required.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'room_number_input' => 'room number',
            'category' => 'room category',
            'max_occupancy' => 'maximum occupancy',
            'bed_count' => 'number of beds',
            'size_sqm' => 'room size',
        ];
    }
}
