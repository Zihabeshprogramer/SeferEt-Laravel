<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Room;

class UpdateRoomRequest extends FormRequest
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
        $room = $this->route('room');
        
        return [
            'hotel_id' => 'required|exists:hotels,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('rooms')->where(function ($query) use ($room) {
                    return $query->where('hotel_id', $this->hotel_id)
                                 ->where('id', '!=', $room->id);
                })
            ],
            'room_number' => [
                'required',
                'string',
                'max:20',
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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
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
            'room_number.required' => 'Room number is required.',
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
            'category' => 'room category',
            'max_occupancy' => 'maximum occupancy',
            'bed_count' => 'number of beds',
            'size_sqm' => 'room size',
        ];
    }
}
