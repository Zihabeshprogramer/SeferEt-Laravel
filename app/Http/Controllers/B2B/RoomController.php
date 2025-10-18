<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:hotel_provider']);
    }

    /**
     * Display a listing of rooms
     */
    public function index(Request $request)
    {
        $query = Room::with(['hotel']);
        // Filter by hotel if provided  
        if ($request->hotel) {
            $query->where('hotel_id', $request->hotel);
        }
        
        // Filter by category if provided
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }

        if ($request->ajax()) {
            $rooms = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $rooms->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'name' => $room->name,
                        'hotel_id' => $room->hotel_id,
                        'hotel_name' => $room->hotel->name ?? 'N/A',
                        'category' => $room->category,
                        'category_name' => $room->category_name,
                        'base_price' => $room->base_price,
                        'max_occupancy' => $room->max_occupancy,
                        'bed_type' => $room->bed_type,
                        'bed_count' => $room->bed_count,
                        'size_sqm' => $room->size_sqm,
                        'amenities' => $room->amenities,
                        'is_active' => $room->is_active,
                        'is_available' => $room->is_available,
                        'description' => $room->description,
                        'images' => $room->images,
                        'created_at' => $room->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $room->updated_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);
        }

        $rooms = $query->orderBy('created_at', 'desc')->paginate(15);
        $hotels = Hotel::select('id', 'name')->orderBy('name')->get();
        $roomTypeCategories = Room::getRoomTypeCategories();

        return view('b2b.hotel-provider.rooms.index', compact('rooms', 'hotels', 'roomTypeCategories'));
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        $hotels = Hotel::select('id', 'name')->orderBy('name')->get();
        
        // Get room type categories from the model
        $roomTypeCategories = Room::getRoomTypeCategories();
        
        // Get bed types from the model
        $bedTypes = Room::BED_TYPES;

        $amenities = [
            'wifi' => 'Wi-Fi',
            'air_conditioning' => 'Air Conditioning',
            'heating' => 'Heating',
            'television' => 'Television',
            'cable_tv' => 'Cable TV',
            'satellite_tv' => 'Satellite TV',
            'telephone' => 'Telephone',
            'minibar' => 'Minibar',
            'refrigerator' => 'Refrigerator',
            'coffee_maker' => 'Coffee Maker',
            'safe' => 'Safe',
            'balcony' => 'Balcony',
            'terrace' => 'Terrace',
            'sea_view' => 'Sea View',
            'mountain_view' => 'Mountain View',
            'city_view' => 'City View',
            'garden_view' => 'Garden View',
            'private_bathroom' => 'Private Bathroom',
            'shared_bathroom' => 'Shared Bathroom',
            'bathtub' => 'Bathtub',
            'shower' => 'Shower',
            'hairdryer' => 'Hair Dryer',
            'toiletries' => 'Toiletries',
            'towels' => 'Towels',
            'iron' => 'Iron',
            'ironing_board' => 'Ironing Board',
            'desk' => 'Work Desk',
            'chair' => 'Chair',
            'wardrobe' => 'Wardrobe',
            'slippers' => 'Slippers',
            'room_service' => 'Room Service'
        ];

        return view('b2b.hotel-provider.rooms.create', compact('hotels', 'roomTypeCategories', 'bedTypes', 'amenities'));
    }

    /**
     * Store a newly created room
     */
    public function store(StoreRoomRequest $request)
    {
        try {
            // Parse room number input
            $roomNumberData = Room::parseRoomNumberInput($request->room_number_input);
            
            // Handle image uploads
            $imagesPaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('rooms', 'public');
                    $imagesPaths[] = $path;
                }
            }
            
            // Check for existing room numbers in this hotel
            $existingRoomNumbers = Room::where('hotel_id', $request->hotel_id)
                ->whereIn('room_number', $roomNumberData['numbers'])
                ->pluck('room_number')
                ->toArray();
                
            if (!empty($existingRoomNumbers)) {
                throw new \Exception('Room numbers already exist: ' . implode(', ', $existingRoomNumbers));
            }
            
            $createdRooms = [];
            
            // Create rooms based on the parsed input
            foreach ($roomNumberData['numbers'] as $roomNumber) {
                $roomData = [
                    'room_number' => (string)$roomNumber,
                    'room_number_start' => $roomNumberData['start'],
                    'room_number_end' => $roomNumberData['end'],
                    'name' => $request->name,
                    'hotel_id' => $request->hotel_id,
                    'category' => $request->category,
                    'base_price' => $request->base_price,
                    'max_occupancy' => $request->max_occupancy,
                    'bed_type' => $request->bed_type,
                    'bed_count' => $request->bed_count ?? 1,
                    'size_sqm' => $request->size_sqm,
                    'amenities' => $request->amenities ?? [],
                    'description' => $request->description,
                    'is_active' => $request->has('is_active'),
                    'is_available' => true, // Default to available
                    'images' => $imagesPaths
                ];
                
                $room = Room::create($roomData);
                $createdRooms[] = $room;
            }
            
            $roomCount = count($createdRooms);
            $message = $roomCount === 1 
                ? 'Room created successfully' 
                : "Successfully created {$roomCount} rooms";
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $createdRooms,
                    'count' => $roomCount
                ]);
            }
            
            return redirect()->route('b2b.hotel-provider.rooms.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors(['room_number_input' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified room
     */
    public function show(Room $room)
    {
        $room->load(['hotel']);
        
        // Get room statistics
        $stats = [
            'total_bookings' => 0, // TODO: Calculate actual bookings
            'revenue' => 0, // TODO: Calculate total revenue
            'occupancy_rate' => 0, // TODO: Calculate occupancy rate
            'average_rating' => 0 // TODO: Calculate average rating
        ];

        return view('b2b.hotel-provider.rooms.show', compact('room', 'stats'));
    }

    /**
     * Show the form for editing the specified room
     */
    public function edit(Room $room)
    {
        $hotels = Hotel::select('id', 'name')->orderBy('name')->get();
        
        // Get room type categories from the model
        $roomTypeCategories = Room::getRoomTypeCategories();
        
        // Get bed types from the model
        $bedTypes = Room::BED_TYPES;

        $amenities = [
            'wifi' => 'Wi-Fi',
            'air_conditioning' => 'Air Conditioning',
            'heating' => 'Heating',
            'television' => 'Television',
            'cable_tv' => 'Cable TV',
            'satellite_tv' => 'Satellite TV',
            'telephone' => 'Telephone',
            'minibar' => 'Minibar',
            'refrigerator' => 'Refrigerator',
            'coffee_maker' => 'Coffee Maker',
            'safe' => 'Safe',
            'balcony' => 'Balcony',
            'terrace' => 'Terrace',
            'sea_view' => 'Sea View',
            'mountain_view' => 'Mountain View',
            'city_view' => 'City View',
            'garden_view' => 'Garden View',
            'private_bathroom' => 'Private Bathroom',
            'shared_bathroom' => 'Shared Bathroom',
            'bathtub' => 'Bathtub',
            'shower' => 'Shower',
            'hairdryer' => 'Hair Dryer',
            'toiletries' => 'Toiletries',
            'towels' => 'Towels',
            'iron' => 'Iron',
            'ironing_board' => 'Ironing Board',
            'desk' => 'Work Desk',
            'chair' => 'Chair',
            'wardrobe' => 'Wardrobe',
            'slippers' => 'Slippers',
            'room_service' => 'Room Service'
        ];

        return view('b2b.hotel-provider.rooms.edit', compact('room', 'hotels', 'roomTypeCategories', 'bedTypes', 'amenities'));
    }

    /**
     * Update the specified room
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'hotel_id' => 'required|exists:hotels,id',
            'category' => 'required|string|in:' . implode(',', array_keys(Room::ROOM_TYPE_CATEGORIES)),
            'base_price' => 'required|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'bed_type' => 'required|string|max:50',
            'bed_count' => 'required|integer|min:1|max:10',
            'size_sqm' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'description' => 'nullable|string|max:2000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string'
        ]);

        // Check for unique room number within hotel (excluding current room)
        $validator->after(function ($validator) use ($request, $room) {
            if (Room::where('hotel_id', $request->hotel_id)
                   ->where('room_number', $request->room_number)
                   ->where('id', '!=', $room->id)
                   ->exists()) {
                $validator->errors()->add('room_number', 'Room number already exists in this hotel.');
            }
        });

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle existing images (from edit form)
        $existingImages = $request->existing_images ?? [];
        
        // Remove any images that were deleted via JavaScript
        $currentImages = $room->images ?? [];
        $imagesToDelete = array_diff($currentImages, $existingImages);
        
        foreach ($imagesToDelete as $imageToDelete) {
            Storage::disk('public')->delete($imageToDelete);
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('rooms', 'public');
                $existingImages[] = $path;
            }
        }

        $room->update([
            'room_number' => $request->room_number,
            'name' => $request->name,
            'hotel_id' => $request->hotel_id,
            'category' => $request->category,
            'base_price' => $request->base_price,
            'max_occupancy' => $request->max_occupancy,
            'bed_type' => $request->bed_type,
            'bed_count' => $request->bed_count ?? 1,
            'size_sqm' => $request->size_sqm,
            'amenities' => $request->amenities ?? [],
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'images' => $existingImages
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully',
                'data' => $room->load(['hotel'])
            ]);
        }

        return redirect()->route('b2b.hotel-provider.rooms.show', $room)
            ->with('success', 'Room updated successfully');
    }

    /**
     * Remove the specified room
     */
    public function destroy(Room $room)
    {
        // TODO: Check if room has any active bookings
        // if ($room->hasActiveBookings()) {
        //     // Handle error
        // }

        // Delete room images from storage
        if ($room->images) {
            foreach ($room->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $room->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);
        }

        return redirect()->route('b2b.hotel-provider.rooms.index')
            ->with('success', 'Room deleted successfully');
    }

    /**
     * Toggle room status (active/inactive)
     */
    public function toggleStatus(Room $room)
    {
        $room->is_active = !$room->is_active;
        $room->save();

        $status = $room->is_active ? 'activated' : 'deactivated';

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Room has been {$status} successfully",
                'is_active' => $room->is_active
            ]);
        }

        return redirect()->back()
            ->with('success', "Room has been {$status} successfully");
    }

    /**
     * Toggle room availability
     */
    public function toggleAvailability(Room $room)
    {
        $room->is_available = !$room->is_available;
        $room->save();

        $status = $room->is_available ? 'available' : 'unavailable';

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Room is now {$status}",
                'is_available' => $room->is_available
            ]);
        }

        return redirect()->back()
            ->with('success', "Room is now {$status}");
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,make_available,make_unavailable,delete',
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:rooms,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rooms = Room::whereIn('id', $request->room_ids);
        $count = $rooms->count();

        switch ($request->action) {
            case 'activate':
                $rooms->update(['is_active' => true]);
                $message = "Successfully activated {$count} rooms";
                break;

            case 'deactivate':
                $rooms->update(['is_active' => false]);
                $message = "Successfully deactivated {$count} rooms";
                break;

            case 'make_available':
                $rooms->update(['is_available' => true]);
                $message = "Successfully made {$count} rooms available";
                break;

            case 'make_unavailable':
                $rooms->update(['is_available' => false]);
                $message = "Successfully made {$count} rooms unavailable";
                break;

            case 'delete':
                // TODO: Check for active bookings before deletion
                $roomsToDelete = $rooms->get();
                foreach ($roomsToDelete as $room) {
                    // Delete images
                    if ($room->images) {
                        foreach ($room->images as $image) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                }
                $rooms->delete();
                $message = "Successfully deleted {$count} rooms";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get rooms by hotel
     */
    public function getByHotel(Request $request, $hotelId)
    {
        // Ensure hotel belongs to authenticated user
        $hotel = Hotel::where('id', $hotelId)
            ->where('provider_id', Auth::id())
            ->first();
            
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel not found or unauthorized access.'
            ], 404);
        }

        $rooms = Room::where('hotel_id', $hotelId)
            ->orderBy('room_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rooms->map(function($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'room_number' => $room->room_number,
                    'base_price' => $room->base_price,
                    'max_occupancy' => $room->max_occupancy,
                    'category' => $room->category,
                    'category_name' => $room->category_name,
                    'bed_type' => $room->bed_type,
                    'bed_count' => $room->bed_count,
                    'is_active' => $room->is_active,
                    'is_available' => $room->is_available
                ];
            })
        ]);
    }

    /**
     * Get available rooms for booking
     */
    public function getAvailable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Implement availability check based on bookings
        $rooms = Room::where('hotel_id', $request->hotel_id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->where('max_occupancy', '>=', $request->guests)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rooms,
            'count' => $rooms->count()
        ]);
    }
}
