<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RoomTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:hotel_provider']);
    }

    /**
     * Display a listing of room types
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            \Log::info('RoomType AJAX request received', [
                'is_ajax' => $request->ajax(),
                'headers' => $request->headers->all()
            ]);
            
            $roomTypes = RoomType::orderBy('created_at', 'desc')->get();
            
            \Log::info('Room types loaded', [
                'count' => $roomTypes->count(),
                'room_types' => $roomTypes->pluck('name')
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $roomTypes->map(function ($roomType) {
                    return [
                        'id' => $roomType->id,
                        'name' => $roomType->name,
                        'slug' => $roomType->slug,
                        'description' => $roomType->description,
                        'is_active' => $roomType->is_active,
                        'rooms_count' => $roomType->rooms->count(),
                        'default_amenities' => $roomType->default_amenities,
                        'created_at' => $roomType->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $roomType->updated_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);
        }

        $roomTypes = RoomType::withCount('rooms')->orderBy('created_at', 'desc')->paginate(10);
        return view('b2b.hotel-provider.room-types.index', compact('roomTypes'));
    }

    /**
     * Show the form for creating a new room type
     */
    public function create()
    {
        $defaultAmenities = [
            'air_conditioning' => 'Air Conditioning',
            'wifi' => 'Free WiFi',
            'tv' => 'TV',
            'minibar' => 'Minibar',
            'safe' => 'Safe',
            'balcony' => 'Balcony',
            'bathroom' => 'Private Bathroom',
            'shower' => 'Shower',
            'bathtub' => 'Bathtub',
            'hairdryer' => 'Hair Dryer',
            'telephone' => 'Telephone',
            'desk' => 'Work Desk',
            'seating_area' => 'Seating Area',
            'coffee_tea' => 'Coffee/Tea Maker',
            'room_service' => '24/7 Room Service',
            'laundry' => 'Laundry Service'
        ];

        return view('b2b.hotel-provider.room-types.create', compact('defaultAmenities'));
    }

    /**
     * Store a newly created room type
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:room_types,name',
            'description' => 'nullable|string|max:1000',
            'default_amenities' => 'nullable|array',
            'default_amenities.*' => 'string',
            'is_active' => 'boolean'
        ]);

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

        $roomType = RoomType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'default_amenities' => $request->default_amenities ?? [],
            'is_active' => $request->has('is_active') ? true : false
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room type created successfully',
                'data' => [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'slug' => $roomType->slug,
                    'description' => $roomType->description,
                    'is_active' => $roomType->is_active,
                    'rooms_count' => 0,
                    'default_amenities' => $roomType->default_amenities,
                    'created_at' => $roomType->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $roomType->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        }

        return redirect()->route('b2b.hotel-provider.room-types.index')
            ->with('success', 'Room type created successfully');
    }

    /**
     * Display the specified room type
     */
    public function show(RoomType $roomType)
    {
        $roomType->load(['rooms.hotel']);
        
        $stats = [
            'total_rooms' => $roomType->rooms->count(),
            'active_rooms' => $roomType->rooms->where('is_active', true)->count(),
            'hotels_using' => $roomType->rooms->pluck('hotel_id')->unique()->count(),
            'average_price' => $roomType->rooms->avg('base_price') ?? 0
        ];

        return view('b2b.hotel-provider.room-types.show', compact('roomType', 'stats'));
    }

    /**
     * Show the form for editing the specified room type
     */
    public function edit(RoomType $roomType)
    {
        $defaultAmenities = [
            'air_conditioning' => 'Air Conditioning',
            'wifi' => 'Free WiFi',
            'tv' => 'TV',
            'minibar' => 'Minibar',
            'safe' => 'Safe',
            'balcony' => 'Balcony',
            'bathroom' => 'Private Bathroom',
            'shower' => 'Shower',
            'bathtub' => 'Bathtub',
            'hairdryer' => 'Hair Dryer',
            'telephone' => 'Telephone',
            'desk' => 'Work Desk',
            'seating_area' => 'Seating Area',
            'coffee_tea' => 'Coffee/Tea Maker',
            'room_service' => '24/7 Room Service',
            'laundry' => 'Laundry Service'
        ];

        return view('b2b.hotel-provider.room-types.edit', compact('roomType', 'defaultAmenities'));
    }

    /**
     * Update the specified room type
     */
    public function update(Request $request, RoomType $roomType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:room_types,name,' . $roomType->id,
            'description' => 'nullable|string|max:1000',
            'default_amenities' => 'nullable|array',
            'default_amenities.*' => 'string',
            'is_active' => 'boolean'
        ]);

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

        $roomType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'default_amenities' => $request->default_amenities ?? [],
            'is_active' => $request->has('is_active') ? true : false
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room type updated successfully',
                'data' => [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'slug' => $roomType->slug,
                    'description' => $roomType->description,
                    'is_active' => $roomType->is_active,
                    'rooms_count' => $roomType->rooms->count(),
                    'default_amenities' => $roomType->default_amenities,
                    'created_at' => $roomType->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $roomType->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        }

        return redirect()->route('b2b.hotel-provider.room-types.show', $roomType)
            ->with('success', 'Room type updated successfully');
    }

    /**
     * Remove the specified room type
     */
    public function destroy(RoomType $roomType)
    {
        // Check if room type has rooms associated
        if ($roomType->rooms()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room type. It has rooms associated with it. Please reassign or delete the rooms first.'
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'Cannot delete room type. It has rooms associated with it. Please reassign or delete the rooms first.');
        }

        $roomType->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room type deleted successfully'
            ]);
        }

        return redirect()->route('b2b.hotel-provider.room-types.index')
            ->with('success', 'Room type deleted successfully');
    }

    /**
     * Toggle room type status
     */
    public function toggleStatus(RoomType $roomType)
    {
        $roomType->is_active = !$roomType->is_active;
        $roomType->save();

        $status = $roomType->is_active ? 'activated' : 'deactivated';

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Room type has been {$status} successfully",
                'is_active' => $roomType->is_active
            ]);
        }

        return redirect()->back()
            ->with('success', "Room type has been {$status} successfully");
    }

    /**
     * Get room types for select dropdown
     */
    public function getForSelect(Request $request)
    {
        $roomTypes = RoomType::active()
            ->select('id', 'name', 'description')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roomTypes->map(function ($roomType) {
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'description' => $roomType->description
                ];
            })
        ]);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'room_type_ids' => 'required|array',
            'room_type_ids.*' => 'exists:room_types,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $roomTypes = RoomType::whereIn('id', $request->room_type_ids);
        $count = $roomTypes->count();

        switch ($request->action) {
            case 'activate':
                $roomTypes->update(['is_active' => true]);
                $message = "Successfully activated {$count} room types";
                break;

            case 'deactivate':
                $roomTypes->update(['is_active' => false]);
                $message = "Successfully deactivated {$count} room types";
                break;

            case 'delete':
                // Check if any room types have rooms
                $roomTypesWithRooms = $roomTypes->withCount('rooms')
                    ->get()
                    ->filter(function ($roomType) {
                        return $roomType->rooms_count > 0;
                    });

                if ($roomTypesWithRooms->count() > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete some room types as they have rooms associated with them'
                    ], 422);
                }

                $roomTypes->delete();
                $message = "Successfully deleted {$count} room types";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
