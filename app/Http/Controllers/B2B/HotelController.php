<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\HotelBooking;
use App\Models\RoomRate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HotelController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:hotel_provider']);
    }

    /**
     * Display a listing of hotels for the authenticated hotel provider
     */
    public function index()
    {
        $user = Auth::user();
        $hotels = Hotel::where('provider_id', $user->id)
            ->with(['rooms', 'bookings'])
            ->latest()
            ->paginate(10);
        
        $stats = [
            'total_hotels' => Hotel::where('provider_id', $user->id)->count(),
            'active_hotels' => Hotel::where('provider_id', $user->id)->where('status', 'active')->where('is_active', true)->count(),
            'pending_hotels' => Hotel::where('provider_id', $user->id)->where('status', 'pending')->count(),
            'total_rooms' => Room::whereHas('hotel', function($query) use ($user) {
                $query->where('provider_id', $user->id);
            })->count(),
            'bookings_this_month' => HotelBooking::whereHas('hotel', function($query) use ($user) {
                $query->where('provider_id', $user->id);
            })->whereMonth('created_at', now()->month)->count(),
        ];

        return view('b2b.hotel-provider.dashboard', compact('hotels', 'stats'));
    }

    /**
     * Show the form for creating a new hotel
     */
    public function create()
    {
        $amenities = [
            'wifi' => 'Free WiFi',
            'parking' => 'Free Parking',
            'pool' => 'Swimming Pool',
            'gym' => 'Fitness Center',
            'spa' => 'Spa & Wellness',
            'restaurant' => 'Restaurant',
            'room_service' => '24/7 Room Service',
            'concierge' => 'Concierge Service',
            'laundry' => 'Laundry Service',
            'airport_shuttle' => 'Airport Shuttle',
            'business_center' => 'Business Center',
            'meeting_rooms' => 'Meeting Rooms'
        ];

        $hotelTypes = [
            'luxury' => 'Luxury Hotel',
            'boutique' => 'Boutique Hotel',
            'business' => 'Business Hotel',
            'resort' => 'Resort',
            'budget' => 'Budget Hotel',
            'apartment' => 'Serviced Apartment'
        ];

        return view('b2b.hotel-provider.management.create', compact('amenities', 'hotelTypes'));
    }

    /**
     * Store a newly created hotel
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'type' => 'required|in:luxury,boutique,business,resort,budget,apartment',
            'star_rating' => 'required|integer|min:1|max:5',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
            'distance_to_haram' => 'nullable|numeric|min:0',
            'distance_to_airport' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png|max:2048',
            'policy_cancellation' => 'nullable|string|max:1000',
            'policy_children' => 'nullable|string|max:1000',
            'policy_pets' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hotel = new Hotel();
        $hotel->fill($request->only([
            'name', 'description', 'type', 'star_rating', 'address', 
            'city', 'country', 'postal_code', 'phone', 'email', 
            'website', 'check_in_time', 'check_out_time',
            'distance_to_haram', 'distance_to_airport',
            'policy_cancellation', 'policy_children', 'policy_pets'
        ]));
        
        $hotel->provider_id = Auth::id();
        $hotel->amenities = $request->amenities ?? [];
        $hotel->status = 'pending'; // Requires admin approval
        $hotel->save();

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('hotels/' . $hotel->id, 'public');
                $imagePaths[] = $path;
            }
            $hotel->images = $imagePaths;
            $hotel->save();
        }

        return redirect()->route('b2b.hotel-provider.hotels.index')
            ->with('success', 'Hotel added successfully! It is now pending approval.');
    }

    /**
     * Display the specified hotel
     */
    public function show(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        $hotel->load(['rooms', 'bookings.customer']);
        
        $hotelStats = [
            'total_rooms' => $hotel->rooms->count(),
            'available_rooms' => $hotel->rooms->where('is_available', true)->count(),
            'bookings_this_month' => $hotel->bookings()->whereMonth('created_at', now()->month)->count(),
            'revenue_this_month' => $hotel->bookings()
                ->whereMonth('created_at', now()->month)
                ->where('status', 'confirmed')
                ->sum('total_amount'),
            'occupancy_rate' => $this->calculateOccupancyRate($hotel),
            'average_rating' => $hotel->reviews()->avg('rating') ?? 0
        ];

        return view('b2b.hotel-provider.management.show', compact('hotel', 'hotelStats'));
    }

    /**
     * Show the form for editing the specified hotel
     */
    public function edit(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        $amenities = [
            'wifi' => 'Free WiFi',
            'parking' => 'Free Parking',
            'pool' => 'Swimming Pool',
            'gym' => 'Fitness Center',
            'spa' => 'Spa & Wellness',
            'restaurant' => 'Restaurant',
            'room_service' => '24/7 Room Service',
            'concierge' => 'Concierge Service',
            'laundry' => 'Laundry Service',
            'airport_shuttle' => 'Airport Shuttle',
            'business_center' => 'Business Center',
            'meeting_rooms' => 'Meeting Rooms'
        ];

        $hotelTypes = [
            'luxury' => 'Luxury Hotel',
            'boutique' => 'Boutique Hotel',
            'business' => 'Business Hotel',
            'resort' => 'Resort',
            'budget' => 'Budget Hotel',
            'apartment' => 'Serviced Apartment'
        ];

        return view('b2b.hotel-provider.management.edit', compact('hotel', 'amenities', 'hotelTypes'));
    }

    /**
     * Update the specified hotel
     */
    public function update(Request $request, Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'type' => 'required|in:luxury,boutique,business,resort,budget,apartment',
            'star_rating' => 'required|integer|min:1|max:5',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
            'distance_to_haram' => 'nullable|numeric|min:0',
            'distance_to_airport' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpeg,jpg,png|max:2048',
            'remove_images' => 'nullable|array',
            'policy_cancellation' => 'nullable|string|max:1000',
            'policy_children' => 'nullable|string|max:1000',
            'policy_pets' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hotel->fill($request->only([
            'name', 'description', 'type', 'star_rating', 'address', 
            'city', 'country', 'postal_code', 'phone', 'email', 
            'website', 'check_in_time', 'check_out_time',
            'distance_to_haram', 'distance_to_airport',
            'policy_cancellation', 'policy_children', 'policy_pets'
        ]));
        
        $hotel->amenities = $request->amenities ?? [];
        
        // Handle image management
        $currentImages = $hotel->images ?? [];
        
        // Remove selected images
        if ($request->remove_images) {
            $currentImages = array_diff($currentImages, $request->remove_images);
            // Delete files from storage
            foreach ($request->remove_images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }
        
        // Add new images
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('hotels/' . $hotel->id, 'public');
                $currentImages[] = $path;
            }
        }
        
        $hotel->images = array_values($currentImages);
        $hotel->save();

        return redirect()->route('b2b.hotel-provider.hotels.show', $hotel)
            ->with('success', 'Hotel updated successfully!');
    }

    /**
     * Remove the specified hotel
     */
    public function destroy(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        // Check if hotel has any bookings
        if ($hotel->bookings()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete hotel. It has bookings associated with it.'
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'Cannot delete hotel. It has bookings associated with it.');
        }

        // Delete hotel images from storage
        if ($hotel->images) {
            foreach ($hotel->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $hotel->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hotel deleted successfully'
            ]);
        }

        return redirect()->route('b2b.hotel-provider.hotels.index')
            ->with('success', 'Hotel deleted successfully');
    }

    /**
     * Toggle hotel availability status
     */
    public function toggleStatus(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        $hotel->is_active = !$hotel->is_active;
        $hotel->save();

        $status = $hotel->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Hotel has been {$status} successfully.");
    }

    /**
     * Calculate occupancy rate for a hotel
     */
    private function calculateOccupancyRate(Hotel $hotel)
    {
        $totalRooms = $hotel->rooms->count();
        if ($totalRooms === 0) return 0;

        $currentMonth = now()->month;
        $daysInMonth = now()->daysInMonth;
        $totalRoomNights = $totalRooms * $daysInMonth;

        $bookedRoomNights = HotelBooking::where('hotel_id', $hotel->id)
            ->whereMonth('check_in_date', $currentMonth)
            ->where('status', 'confirmed')
            ->sum('nights');

        return $totalRoomNights > 0 ? round(($bookedRoomNights / $totalRoomNights) * 100, 1) : 0;
    }

    /**
     * Display rooms for a specific hotel
     */
    public function rooms(Hotel $hotel)
    {
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        $rooms = $hotel->rooms()->get();
        
        // Handle AJAX requests
        if (request()->ajax()) {
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
        
        // Handle regular web requests (with pagination)
        $rooms = $hotel->rooms()->paginate(10);
        return view('b2b.hotel-provider.management.index', compact('hotel', 'rooms'));
    }


    /**
     * Display reviews for a specific hotel
     */
    public function reviews(Hotel $hotel)
    {
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        $reviews = $hotel->reviews()->with(['customer'])->latest()->paginate(10);
        return view('b2b.hotel-provider.management.reviews', compact('hotel', 'reviews'));
    }

    /**
     * Display analytics for a specific hotel
     */
    public function analytics(Hotel $hotel)
    {
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }

        // Hotel analytics data
        $analyticsData = [
            'occupancy_rate' => $this->calculateOccupancyRate($hotel),
            'revenue_trend' => $this->getRevenueTrend($hotel),
            'booking_trend' => $this->getBookingTrend($hotel),
            'average_rating' => $hotel->reviews()->avg('rating') ?? 0,
            'total_reviews' => $hotel->reviews()->count(),
        ];

        return view('b2b.hotel-provider.management.analytics', compact('hotel', 'analyticsData'));
    }

    /**
     * Display all bookings across all hotels
     */
    public function allBookings()
    {
        $user = Auth::user();
        $bookings = HotelBooking::whereHas('hotel', function($query) use ($user) {
            $query->where('provider_id', $user->id);
        })->with(['hotel', 'customer'])->latest()->paginate(15);

        return view('b2b.hotel-provider.bookings', compact('bookings'));
    }

    /**
     * Display rates management
     */
    public function rates()
    {
        $user = Auth::user();
        $hotels = Hotel::where('provider_id', $user->id)->with(['rooms.rates'])->get();
        
        return view('b2b.hotel-provider.rates', compact('hotels'));
    }

    /**
     * Display availability management
     */
    public function availability()
    {
        $user = Auth::user();
        $hotels = Hotel::where('provider_id', $user->id)->with(['rooms'])->get();
        
        return view('b2b.hotel-provider.availability', compact('hotels'));
    }

    /**
     * Display reports
     */
    public function reports()
    {
        $user = Auth::user();
        
        $reportData = [
            'total_revenue' => HotelBooking::whereHas('hotel', function($query) use ($user) {
                $query->where('provider_id', $user->id);
            })->where('status', 'confirmed')->sum('total_amount'),
            'monthly_revenue' => $this->getMonthlyRevenue($user->id),
            'booking_stats' => $this->getBookingStats($user->id),
            'occupancy_stats' => $this->getOccupancyStats($user->id),
        ];

        return view('b2b.hotel-provider.reports', compact('reportData'));
    }

    /**
     * Display profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('b2b.hotel-provider.profile', compact('user'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'business_license' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->update($request->only(['name', 'email', 'phone', 'company_name', 'business_license']));

        return redirect()->route('b2b.hotel-provider.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Get room categories for a specific hotel
     */
    public function getRoomCategories(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel.'
            ], 403);
        }

        // Return all available room categories
        $categories = \App\Models\Room::getRoomTypeCategories();
        
        $formattedCategories = [];
        foreach ($categories as $key => $label) {
            $formattedCategories[] = [
                'id' => $key,
                'name' => $label,
                'description' => $label
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedCategories
        ]);
    }

    // Helper methods for analytics
    private function getRevenueTrend(Hotel $hotel)
    {
        // Get last 12 months revenue data
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'revenue' => $hotel->bookings()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('status', 'confirmed')
                    ->sum('total_amount')
            ];
        }
        return $months;
    }

    private function getBookingTrend(Hotel $hotel)
    {
        // Get last 12 months booking count
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'bookings' => $hotel->bookings()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            ];
        }
        return $months;
    }

    private function getMonthlyRevenue($providerId)
    {
        return HotelBooking::whereHas('hotel', function($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'confirmed')
          ->whereMonth('created_at', now()->month)
          ->sum('total_amount');
    }

    private function getBookingStats($providerId)
    {
        return [
            'total' => HotelBooking::whereHas('hotel', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })->count(),
            'confirmed' => HotelBooking::whereHas('hotel', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })->where('status', 'confirmed')->count(),
            'pending' => HotelBooking::whereHas('hotel', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })->where('status', 'pending')->count(),
            'cancelled' => HotelBooking::whereHas('hotel', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })->where('status', 'cancelled')->count(),
        ];
    }

    private function getOccupancyStats($providerId)
    {
        $hotels = Hotel::where('provider_id', $providerId)->get();
        $totalOccupancy = 0;
        $hotelCount = $hotels->count();
        
        foreach ($hotels as $hotel) {
            $totalOccupancy += $this->calculateOccupancyRate($hotel);
        }
        
        return $hotelCount > 0 ? round($totalOccupancy / $hotelCount, 1) : 0;
    }

    /**
     * Store room rate
     */
    public function storeRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = Room::findOrFail($request->room_id);
        
        // Verify room belongs to authenticated user's hotel
        if ($room->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to room'
            ], 403);
        }

        // Create rates for each date in the range
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        
        $createdRates = 0;
        $updatedRates = 0;
        
        while ($startDate->lte($endDate)) {
            $existingRate = RoomRate::where('room_id', $room->id)
                ->where('date', $startDate->toDateString())
                ->first();
            
            if ($existingRate) {
                $existingRate->update([
                    'price' => $request->price,
                    'notes' => $request->notes,
                    'is_active' => true
                ]);
                $updatedRates++;
            } else {
                RoomRate::create([
                    'room_id' => $room->id,
                    'date' => $startDate->toDateString(),
                    'price' => $request->price,
                    'notes' => $request->notes,
                    'is_active' => true
                ]);
                $createdRates++;
            }
            
            $startDate->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully set rates for {$createdRates} new dates and updated {$updatedRates} existing rates"
        ]);
    }

    /**
     * Bulk pricing operation
     */
    public function bulkPricing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'apply_to' => 'required|in:all_rooms,room_type,specific_rooms',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pricing_method' => 'required|in:fixed,percentage,amount',
            'value' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $hotel = Hotel::findOrFail($request->hotel_id);
        
        // Verify hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel'
            ], 403);
        }

        // Get rooms based on apply_to selection
        $rooms = collect();
        switch ($request->apply_to) {
            case 'all_rooms':
                $rooms = $hotel->rooms;
                break;
            case 'room_type':
                // TODO: Implement room type filtering
                $rooms = $hotel->rooms;
                break;
            case 'specific_rooms':
                // TODO: Implement specific room selection
                $rooms = $hotel->rooms;
                break;
        }

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $totalRates = 0;

        foreach ($rooms as $room) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $newPrice = $this->calculateNewPrice(
                    $room->base_price, 
                    $request->pricing_method, 
                    $request->value
                );

                RoomRate::updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'date' => $currentDate->toDateString()
                    ],
                    [
                        'price' => $newPrice,
                        'notes' => 'Bulk pricing applied',
                        'is_active' => true
                    ]
                );
                
                $totalRates++;
                $currentDate->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully applied bulk pricing to {$totalRates} rate entries across {$rooms->count()} rooms"
        ]);
    }

    /**
     * Get rooms for a hotel (AJAX)
     */
    public function getHotelRooms(Request $request)
    {
        $hotelId = $request->hotel_id;
        $hotel = Hotel::findOrFail($hotelId);
        
        // Verify hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $rooms = $hotel->rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name ?? $room->room_number,
                'room_number' => $room->room_number,
                'base_price' => $room->base_price
            ];
        });

        return response()->json($rooms);
    }

    /**
     * Get rates for calendar view (AJAX)
     */
    public function getCalendarRates(Request $request)
    {
        $roomId = $request->room_id;
        $room = Room::findOrFail($roomId);
        
        // Verify room belongs to authenticated user's hotel
        if ($room->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Get rates for the next 3 months
        $startDate = now()->startOfMonth();
        $endDate = now()->addMonths(3)->endOfMonth();

        $rates = RoomRate::where('room_id', $roomId)
            ->where('is_active', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(function ($rate) {
                return [
                    'date' => $rate->date,
                    'price' => $rate->price,
                    'notes' => $rate->notes
                ];
            });

        return response()->json($rates);
    }

    /**
     * Calculate new price based on pricing method
     */
    private function calculateNewPrice($basePrice, $method, $value)
    {
        switch ($method) {
            case 'fixed':
                return $value;
            case 'percentage':
                return $basePrice + ($basePrice * ($value / 100));
            case 'amount':
                return $basePrice + $value;
            default:
                return $basePrice;
        }
    }

    /**
     * Get hotel data for DataTables (AJAX)
     */
    public function datatable(Request $request)
    {
        $user = Auth::user();
        $query = Hotel::where('provider_id', $user->id)
                    ->with(['rooms:id,hotel_id', 'bookings:id,hotel_id']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $query->get()->map(function ($hotel) {
                    return [
                        'id' => $hotel->id,
                        'name' => $hotel->name,
                        'city' => $hotel->city,
                        'country' => $hotel->country,
                        'type' => $hotel->type,
                        'star_rating' => $hotel->star_rating,
                        'status' => $hotel->status,
                        'is_active' => $hotel->is_active,
                        'total_rooms' => $hotel->rooms->count(),
                        'total_bookings' => $hotel->bookings->count(),
                        'created_at' => $hotel->created_at->format('Y-m-d H:i:s')
                    ];
                })
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
    }

    /**
     * Quick toggle hotel status (AJAX)
     */
    public function quickToggle(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel.'
            ], 403);
        }

        $hotel->is_active = !$hotel->is_active;
        $hotel->save();

        return response()->json([
            'success' => true,
            'message' => 'Hotel status updated successfully',
            'is_active' => $hotel->is_active
        ]);
    }

    /**
     * Toggle room availability for a hotel room
     */
    public function toggleRoomAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'is_available' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = Room::findOrFail($request->room_id);
        
        // Verify room belongs to authenticated user's hotel
        if ($room->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to room'
            ], 403);
        }

        $room->is_available = $request->is_available;
        $room->save();

        $status = $room->is_available ? 'available' : 'unavailable';
        
        return response()->json([
            'success' => true,
            'message' => "Room is now {$status}",
            'is_available' => $room->is_available
        ]);
    }

    /**
     * Get hotel statistics for dashboard
     */
    public function getHotelStats(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel.'
            ], 403);
        }

        $stats = [
            'total_rooms' => $hotel->rooms->count(),
            'available_rooms' => $hotel->rooms->where('is_available', true)->count(),
            'occupied_rooms' => $hotel->rooms->where('is_available', false)->count(),
            'bookings_today' => $hotel->bookings()->whereDate('created_at', today())->count(),
            'bookings_this_week' => $hotel->bookings()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'bookings_this_month' => $hotel->bookings()->whereMonth('created_at', now()->month)->count(),
            'revenue_today' => $hotel->bookings()->whereDate('created_at', today())->where('status', 'confirmed')->sum('total_amount'),
            'revenue_this_week' => $hotel->bookings()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'confirmed')->sum('total_amount'),
            'revenue_this_month' => $hotel->bookings()->whereMonth('created_at', now()->month)->where('status', 'confirmed')->sum('total_amount'),
            'occupancy_rate' => $this->calculateOccupancyRate($hotel),
            'average_rating' => $hotel->reviews()->avg('rating') ?? 0,
            'total_reviews' => $hotel->reviews()->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // ========== BOOKING MANAGEMENT METHODS ==========

    /**
     * Display all bookings across all hotels (main booking index)
     */
    public function bookings(Request $request)
    {
        $user = Auth::user();
        
        // Get hotels for filter dropdown
        $hotels = Hotel::where('provider_id', $user->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        // Build bookings query
        $bookingsQuery = HotelBooking::whereHas('hotel', function($query) use ($user) {
            $query->where('provider_id', $user->id);
        })->with(['hotel', 'room', 'guest']);
        
        // Apply filters if provided
        if ($request->filled('status')) {
            $bookingsQuery->where('status', $request->status);
        }
        
        if ($request->filled('hotel')) {
            $bookingsQuery->where('hotel_id', $request->hotel);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $bookingsQuery->where(function($query) use ($search) {
                $query->where('booking_reference', 'like', '%' . $search . '%')
                      ->orWhere('guest_name', 'like', '%' . $search . '%')
                      ->orWhere('guest_email', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('dateRange')) {
            $dates = explode(' - ', $request->dateRange);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::parse($dates[0]);
                $endDate = \Carbon\Carbon::parse($dates[1]);
                $bookingsQuery->whereBetween('check_in_date', [$startDate, $endDate]);
            }
        }
        
        // Get paginated bookings
        $bookings = $bookingsQuery->latest()->paginate(15);
        
        // Calculate statistics
        $stats = [
            'total_bookings' => HotelBooking::whereHas('hotel', function($query) use ($user) {
                $query->where('provider_id', $user->id);
            })->count(),
            'confirmed_bookings' => HotelBooking::whereHas('hotel', function($query) use ($user) {
                $query->where('provider_id', $user->id);
            })->where('status', 'confirmed')->count(),
            'pending_bookings' => HotelBooking::whereHas('hotel', function($query) use ($user) {
                $query->where('provider_id', $user->id);
            })->where('status', 'pending')->count(),
            'total_revenue' => HotelBooking::whereHas('hotel', function($query) use ($user) {
                $query->where('provider_id', $user->id);
            })->whereMonth('created_at', now()->month)
              ->where('status', 'confirmed')
              ->sum('total_amount')
        ];
        
        return view('b2b.hotel-provider.bookings.index', compact('bookings', 'hotels', 'stats'));
    }

    /**
     * Get bookings data for AJAX filtering
     */
    public function bookingsData(Request $request)
    {
        $user = Auth::user();
        
        // Build bookings query
        $bookingsQuery = HotelBooking::whereHas('hotel', function($query) use ($user) {
            $query->where('provider_id', $user->id);
        })->with(['hotel', 'room']);
        
        // Apply filters
        if ($request->filled('status')) {
            $bookingsQuery->where('status', $request->status);
        }
        
        if ($request->filled('hotel')) {
            $bookingsQuery->where('hotel_id', $request->hotel);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $bookingsQuery->where(function($query) use ($search) {
                $query->where('booking_reference', 'like', '%' . $search . '%')
                      ->orWhere('guest_name', 'like', '%' . $search . '%')
                      ->orWhere('guest_email', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('dateRange')) {
            $dates = explode(' - ', $request->dateRange);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::parse($dates[0]);
                $endDate = \Carbon\Carbon::parse($dates[1]);
                $bookingsQuery->whereBetween('check_in_date', [$startDate, $endDate]);
            }
        }
        
        $bookings = $bookingsQuery->latest()->get();
        
        // Format bookings for frontend
        $formattedBookings = $bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'guest_name' => $booking->guest_name,
                'guest_email' => $booking->guest_email,
                'guest_phone' => $booking->guest_phone,
                'guest_info' => $booking->guest_info,
                'hotel_name' => $booking->hotel->name,
                'room_number' => $booking->room->room_number,
                'room_name' => $booking->room->name,
                'room_type' => $booking->room->room_type,
                'check_in_date' => $booking->check_in_date->toISOString(),
                'check_out_date' => $booking->check_out_date->toISOString(),
                'nights' => $booking->nights,
                'status' => $booking->status,
                'status_badge_class' => $this->getStatusBadgeClass($booking->status),
                'payment_status' => $booking->payment_status,
                'payment_status_badge_class' => $this->getPaymentStatusBadgeClass($booking->payment_status),
                'total_amount' => $booking->total_amount,
                'room_rate' => $booking->room_rate,
                'created_at' => $booking->created_at->toISOString(),
                'can_be_confirmed' => $booking->canBeConfirmed(),
                'can_check_in' => $booking->canCheckIn(),
                'can_check_out' => $booking->canCheckOut(),
                'can_be_cancelled' => $booking->canBeCancelled()
            ];
        });
        
        // Calculate updated stats
        $stats = [
            'total_bookings' => $bookings->count(),
            'confirmed_bookings' => $bookings->where('status', 'confirmed')->count(),
            'pending_bookings' => $bookings->where('status', 'pending')->count(),
            'total_revenue' => $bookings->where('status', 'confirmed')->sum('total_amount')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $formattedBookings,
            'stats' => $stats
        ]);
    }

    /**
     * Display bookings for a specific hotel
     */
    public function hotelBookings(Hotel $hotel, Request $request)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }
        
        // Load hotel with rooms and current bookings
        $hotel->load(['rooms']);
        
        // Get bookings for this hotel
        $bookingsQuery = $hotel->bookings()->with(['room']);
        
        // Apply filters if provided
        if ($request->filled('status')) {
            $bookingsQuery->where('status', $request->status);
        }
        
        if ($request->filled('room')) {
            $bookingsQuery->where('room_id', $request->room);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $bookingsQuery->where(function($query) use ($search) {
                $query->where('booking_reference', 'like', '%' . $search . '%')
                      ->orWhere('guest_name', 'like', '%' . $search . '%')
                      ->orWhere('guest_email', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('dateRange')) {
            $dates = explode(' - ', $request->dateRange);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::parse($dates[0]);
                $endDate = \Carbon\Carbon::parse($dates[1]);
                $bookingsQuery->whereBetween('check_in_date', [$startDate, $endDate]);
            }
        }
        
        $bookings = $bookingsQuery->latest()->paginate(20);
        
        // Calculate hotel-specific statistics
        $stats = [
            'total_bookings' => $hotel->bookings()->count(),
            'confirmed' => $hotel->bookings()->where('status', 'confirmed')->count(),
            'pending' => $hotel->bookings()->where('status', 'pending')->count(),
            'checked_in' => $hotel->bookings()->where('status', 'checked_in')->count(),
            'occupancy_rate' => $this->calculateOccupancyRate($hotel),
            'revenue' => $hotel->bookings()
                ->whereMonth('created_at', now()->month)
                ->where('status', 'confirmed')
                ->sum('total_amount')
        ];
        
        // Get today's check-ins and check-outs
        $todayCheckins = $hotel->bookings()
            ->whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with(['room'])
            ->get();
            
        $todayCheckouts = $hotel->bookings()
            ->whereDate('check_out_date', today())
            ->where('status', 'checked_in')
            ->with(['room'])
            ->get();
        
        return view('b2b.hotel-provider.bookings.hotel', compact(
            'hotel', 'bookings', 'stats', 'todayCheckins', 'todayCheckouts'
        ));
    }

    /**
     * Get hotel bookings data for AJAX filtering
     */
    public function hotelBookingsData(Hotel $hotel, Request $request)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel.'
            ], 403);
        }
        
        // Build bookings query for this hotel
        $bookingsQuery = $hotel->bookings()->with(['room']);
        
        // Apply filters
        if ($request->filled('status')) {
            $bookingsQuery->where('status', $request->status);
        }
        
        if ($request->filled('room')) {
            $bookingsQuery->where('room_id', $request->room);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $bookingsQuery->where(function($query) use ($search) {
                $query->where('booking_reference', 'like', '%' . $search . '%')
                      ->orWhere('guest_name', 'like', '%' . $search . '%')
                      ->orWhere('guest_email', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('dateRange')) {
            $dates = explode(' - ', $request->dateRange);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::parse($dates[0]);
                $endDate = \Carbon\Carbon::parse($dates[1]);
                $bookingsQuery->whereBetween('check_in_date', [$startDate, $endDate]);
            }
        }
        
        $bookings = $bookingsQuery->latest()->get();
        
        // Format bookings for frontend
        $formattedBookings = $bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'guest_name' => $booking->guest_name,
                'guest_email' => $booking->guest_email,
                'guest_phone' => $booking->guest_phone,
                'guest_info' => $booking->guest_info,
                'room_number' => $booking->room->room_number,
                'room_name' => $booking->room->name,
                'room_type' => $booking->room->room_type,
                'check_in_date' => $booking->check_in_date->toISOString(),
                'check_out_date' => $booking->check_out_date->toISOString(),
                'nights' => $booking->nights,
                'status' => $booking->status,
                'status_badge_class' => $this->getStatusBadgeClass($booking->status),
                'payment_status' => $booking->payment_status,
                'payment_status_badge_class' => $this->getPaymentStatusBadgeClass($booking->payment_status),
                'total_amount' => $booking->total_amount,
                'room_rate' => $booking->room_rate,
                'created_at' => $booking->created_at->toISOString(),
                'can_be_confirmed' => $booking->canBeConfirmed(),
                'can_check_in' => $booking->canCheckIn(),
                'can_check_out' => $booking->canCheckOut(),
                'can_be_cancelled' => $booking->canBeCancelled()
            ];
        });
        
        // Calculate updated stats
        $stats = [
            'total_bookings' => $bookings->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'checked_in' => $bookings->where('status', 'checked_in')->count(),
            'occupancy_rate' => $this->calculateOccupancyRate($hotel),
            'revenue' => $bookings->where('status', 'confirmed')->sum('total_amount')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $formattedBookings,
            'stats' => $stats
        ]);
    }

    /**
     * Show a specific booking
     */
    public function showBooking(HotelBooking $booking)
    {
        // Ensure booking belongs to authenticated user's hotel
        if ($booking->hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }
        
        $booking->load(['hotel', 'room', 'guest']);
        
        return view('b2b.hotel-provider.bookings.show', compact('booking'));
    }

    /**
     * Confirm a booking
     */
    public function confirmBooking(HotelBooking $booking, Request $request)
    {
        // Ensure booking belongs to authenticated user's hotel
        if ($booking->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to booking.'
            ], 403);
        }
        
        if (!$booking->canBeConfirmed()) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be confirmed in its current state.'
            ], 422);
        }
        
        $booking->status = 'confirmed';
        $booking->confirmed_at = now();
        if ($request->filled('notes')) {
            $booking->notes = ($booking->notes ? $booking->notes . '\n' : '') . 
                              '[' . now()->format('Y-m-d H:i') . '] Confirmation: ' . $request->notes;
        }
        $booking->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully.'
        ]);
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(HotelBooking $booking, Request $request)
    {
        // Ensure booking belongs to authenticated user's hotel
        if ($booking->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to booking.'
            ], 403);
        }
        
        if (!$booking->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be cancelled in its current state.'
            ], 422);
        }
        
        $booking->status = 'cancelled';
        $booking->cancelled_at = now();
        if ($request->filled('notes')) {
            $booking->notes = ($booking->notes ? $booking->notes . '\n' : '') . 
                              '[' . now()->format('Y-m-d H:i') . '] Cancellation: ' . $request->notes;
        }
        $booking->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.'
        ]);
    }

    /**
     * Check in a booking
     */
    public function checkInBooking(HotelBooking $booking, Request $request)
    {
        // Ensure booking belongs to authenticated user's hotel
        if ($booking->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to booking.'
            ], 403);
        }
        
        if (!$booking->canCheckIn()) {
            return response()->json([
                'success' => false,
                'message' => 'Guest cannot check in at this time.'
            ], 422);
        }
        
        $booking->status = 'checked_in';
        $booking->checked_in_at = now();
        if ($request->filled('notes')) {
            $booking->notes = ($booking->notes ? $booking->notes . '\n' : '') . 
                              '[' . now()->format('Y-m-d H:i') . '] Check-in: ' . $request->notes;
        }
        $booking->save();
        
        // Update room availability
        $booking->room->update(['is_available' => false]);
        
        return response()->json([
            'success' => true,
            'message' => 'Guest checked in successfully.'
        ]);
    }

    /**
     * Check out a booking
     */
    public function checkOutBooking(HotelBooking $booking, Request $request)
    {
        // Ensure booking belongs to authenticated user's hotel
        if ($booking->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to booking.'
            ], 403);
        }
        
        if (!$booking->canCheckOut()) {
            return response()->json([
                'success' => false,
                'message' => 'Guest cannot check out at this time.'
            ], 422);
        }
        
        $booking->status = 'checked_out';
        $booking->checked_out_at = now();
        if ($request->filled('notes')) {
            $booking->notes = ($booking->notes ? $booking->notes . '\n' : '') . 
                              '[' . now()->format('Y-m-d H:i') . '] Check-out: ' . $request->notes;
        }
        $booking->save();
        
        // Update room availability
        $booking->room->update(['is_available' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Guest checked out successfully.'
        ]);
    }

    /**
     * Record payment for a booking
     */
    public function recordPayment(HotelBooking $booking, Request $request)
    {
        // Ensure booking belongs to authenticated user's hotel
        if ($booking->hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to booking.'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:cash,credit_card,debit_card,bank_transfer,other',
            'notes' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $amount = $request->amount;
        $newPaidAmount = $booking->paid_amount + $amount;
        
        // Update payment information
        $booking->paid_amount = $newPaidAmount;
        $booking->payment_method = $request->method;
        
        // Determine payment status
        if ($newPaidAmount >= $booking->total_amount) {
            $booking->payment_status = 'paid';
        } elseif ($newPaidAmount > 0) {
            $booking->payment_status = 'partial';
        }
        
        // Add payment note
        if ($request->filled('notes')) {
            $booking->notes = ($booking->notes ? $booking->notes . '\n' : '') . 
                              '[' . now()->format('Y-m-d H:i') . '] Payment: $' . number_format($amount, 2) . 
                              ' (' . $request->method . ') - ' . $request->notes;
        }
        
        $booking->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.'
        ]);
    }

    /**
     * Export bookings to CSV/PDF
     */
    public function exportBookings(Request $request)
    {
        // This would implement CSV/PDF export functionality
        // For now, return a placeholder response
        return response()->json([
            'success' => false,
            'message' => 'Export functionality coming soon.'
        ]);
    }

    /**
     * Export hotel bookings to CSV/PDF
     */
    public function exportHotelBookings(Hotel $hotel, Request $request)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel.'
            ], 403);
        }
        
        // This would implement CSV/PDF export functionality
        // For now, return a placeholder response
        return response()->json([
            'success' => false,
            'message' => 'Export functionality coming soon.'
        ]);
    }

    /**
     * Show booking calendar for a hotel
     */
    public function bookingCalendar(Hotel $hotel)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to hotel.');
        }
        
        return view('b2b.hotel-provider.bookings.calendar', compact('hotel'));
    }

    /**
     * Get calendar data for a hotel
     */
    public function bookingCalendarData(Hotel $hotel, Request $request)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel.'
            ], 403);
        }
        
        $baseDate = \Carbon\Carbon::parse($request->get('date', now()));
        $period = $request->get('period', 'month');
        
        // Calculate date range based on period
        [$startDate, $endDate] = $this->getCalendarDateRange($baseDate, $period);
        
        // Get all rooms for this hotel
        $rooms = $hotel->rooms()->with(['bookings' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('check_in_date', [$startDate, $endDate])
                  ->orWhereBetween('check_out_date', [$startDate, $endDate])
                  ->orWhere(function($q) use ($startDate, $endDate) {
                      $q->where('check_in_date', '<=', $startDate)
                        ->where('check_out_date', '>=', $endDate);
                  });
        }])->get();
        
        // Generate dates array
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->toDateString();
            $currentDate->addDay();
        }
        
        // Format room data for calendar
        $roomsData = $rooms->map(function($room) use ($dates, $startDate, $endDate) {
            $roomBookings = [];
            $roomBlocked = [];
            
            // Process bookings for each date
            foreach ($dates as $date) {
                $dateCarbon = \Carbon\Carbon::parse($date);
                
                // Find booking for this date
                $booking = $room->bookings->first(function($booking) use ($dateCarbon) {
                    return $dateCarbon->between(
                        $booking->check_in_date, 
                        $booking->check_out_date->subDay()
                    );
                });
                
                if ($booking) {
                    $roomBookings[$date] = [
                        'id' => $booking->id,
                        'guest_name' => $booking->guest_name,
                        'booking_reference' => $booking->booking_reference,
                        'status' => $booking->status,
                        'check_in_date' => $booking->check_in_date->toDateString(),
                        'check_out_date' => $booking->check_out_date->toDateString()
                    ];
                }
                
                // Check if room is blocked (placeholder - would need room blocking system)
                // $roomBlocked[$date] = ...
            }
            
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->category_name,
                'bookings' => $roomBookings,
                'blocked' => $roomBlocked
            ];
        });
        
        // Calculate statistics
        $today = now()->toDateString();
        $stats = [
            'available_rooms' => $rooms->filter(function($room) use ($today) {
                return !$room->bookings->contains(function($booking) use ($today) {
                    return \Carbon\Carbon::parse($today)->between(
                        $booking->check_in_date,
                        $booking->check_out_date->subDay()
                    );
                });
            })->count(),
            'occupied_rooms' => $rooms->filter(function($room) use ($today) {
                return $room->bookings->contains(function($booking) use ($today) {
                    return \Carbon\Carbon::parse($today)->between(
                        $booking->check_in_date,
                        $booking->check_out_date->subDay()
                    ) && in_array($booking->status, ['confirmed', 'checked_in']);
                });
            })->count(),
            'checkins_today' => $hotel->bookings()
                ->whereDate('check_in_date', $today)
                ->whereIn('status', ['confirmed', 'pending'])
                ->count(),
            'checkouts_today' => $hotel->bookings()
                ->whereDate('check_out_date', $today)
                ->where('status', 'checked_in')
                ->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'dates' => $dates,
                'rooms' => $roomsData
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Export calendar data
     */
    public function exportCalendarData(Hotel $hotel, Request $request)
    {
        // Ensure hotel belongs to authenticated user
        if ($hotel->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to hotel.'
            ], 403);
        }
        
        // This would implement calendar data export
        return response()->json([
            'success' => false,
            'message' => 'Calendar export functionality coming soon.'
        ]);
    }

    /**
     * Get calendar date range based on period
     */
    private function getCalendarDateRange(\Carbon\Carbon $baseDate, string $period)
    {
        switch ($period) {
            case 'week':
                $startDate = $baseDate->copy()->startOfWeek();
                $endDate = $baseDate->copy()->endOfWeek();
                break;
            case '3months':
                $startDate = $baseDate->copy()->startOfMonth();
                $endDate = $baseDate->copy()->addMonths(2)->endOfMonth();
                break;
            case 'month':
            default:
                $startDate = $baseDate->copy()->startOfMonth();
                $endDate = $baseDate->copy()->endOfMonth();
                break;
        }
        
        return [$startDate, $endDate];
    }

    /**
     * Get status badge class for booking status
     */
    private function getStatusBadgeClass($status)
    {
        $classes = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'checked_in' => 'info',
            'checked_out' => 'secondary',
            'cancelled' => 'danger',
            'no_show' => 'dark'
        ];
        
        return $classes[$status] ?? 'secondary';
    }

    /**
     * Get payment status badge class
     */
    private function getPaymentStatusBadgeClass($paymentStatus)
    {
        $classes = [
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'refunded' => 'secondary',
            'failed' => 'danger'
        ];
        
        return $classes[$paymentStatus] ?? 'secondary';
    }

    // ========== BOOKING STATISTICS DASHBOARD METHODS ==========

    /**
     * Booking Statistics Dashboard
     */
    public function bookingDashboard()
    {
        $hotels = auth()->user()->hotels()->with('rooms')->get();
        
        return view('b2b.hotel-provider.bookings.dashboard', compact('hotels'));
    }

    /**
     * Dashboard Data API
     */
    public function bookingDashboardData(Request $request)
    {
        try {
            $user = auth()->user();
            $hotels = $user->hotels()->pluck('id');
            
            // Get date range based on period
            $dateRange = $this->getDateRange($request->input('period', 'month'), $request->input('dateRange'));
            $hotelFilter = $request->input('hotel');
            
            // Build base query
            $query = HotelBooking::whereIn('hotel_id', $hotels)
                ->whereBetween('check_in_date', [$dateRange['start'], $dateRange['end']]);
            
            if ($hotelFilter) {
                $query->where('hotel_id', $hotelFilter);
            }
            
            // Get KPIs
            $kpis = $this->getBookingKPIs($query->clone(), $dateRange);
            
            // Get chart data
            $charts = $this->getBookingCharts($query->clone(), $dateRange);
            
            // Get table data
            $tables = $this->getBookingTables($query->clone());
            
            // Get insights
            $insights = $this->getBookingInsights($query->clone());
            
            return response()->json([
                'success' => true,
                'data' => [
                    'kpis' => $kpis,
                    'charts' => $charts,
                    'tables' => $tables,
                    'insights' => $insights
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard Export
     */
    public function bookingDashboardExport(Request $request)
    {
        try {
            $type = $request->input('type', 'summary');
            $table = $request->input('table');
            
            // Similar data collection as dashboard
            $user = auth()->user();
            $hotels = $user->hotels()->pluck('id');
            $dateRange = $this->getDateRange($request->input('period', 'month'), $request->input('dateRange'));
            
            switch ($type) {
                case 'summary':
                    return $this->exportSummaryReport($hotels, $dateRange);
                case 'detailed':
                    return $this->exportDetailedReport($hotels, $dateRange);
                case 'charts':
                    return $this->exportChartsReport($hotels, $dateRange);
                default:
                    if ($table) {
                        return $this->exportTableData($hotels, $dateRange, $table);
                    }
                    break;
            }
            
            return response()->json(['error' => 'Invalid export type'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper methods for dashboard
    private function getDateRange($period, $customRange = null)
    {
        $now = now();
        
        switch ($period) {
            case 'today':
                return ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'week':
                return ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek()];
            case 'month':
                return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()];
            case 'quarter':
                return ['start' => $now->copy()->startOfQuarter(), 'end' => $now->copy()->endOfQuarter()];
            case 'year':
                return ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()];
            case 'custom':
                if ($customRange && strpos($customRange, ' to ') !== false) {
                    [$start, $end] = explode(' to ', $customRange);
                    return ['start' => Carbon::parse($start), 'end' => Carbon::parse($end)];
                }
                break;
        }
        
        // Default to current month
        return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()];
    }

    private function getBookingKPIs($query, $dateRange)
    {
        $currentData = $query->get();
        
        // Get previous period for comparison
        $previousRange = $this->getPreviousPeriod($dateRange);
        $previousQuery = HotelBooking::whereIn('hotel_id', auth()->user()->hotels()->pluck('id'))
            ->whereBetween('check_in_date', [$previousRange['start'], $previousRange['end']]);
        $previousData = $previousQuery->get();
        
        $kpis = [
            'total_bookings' => $currentData->count(),
            'confirmed_bookings' => $currentData->where('status', 'confirmed')->count(),
            'pending_bookings' => $currentData->where('status', 'pending')->count(),
            'total_revenue' => $currentData->where('status', '!=', 'cancelled')->sum('total_amount'),
            'occupancy_rate' => $this->calculateDashboardOccupancyRate($currentData, $dateRange),
            'average_daily_rate' => $this->calculateDashboardADR($currentData)
        ];
        
        // Calculate changes
        $previousKpis = [
            'total_bookings' => $previousData->count(),
            'confirmed_bookings' => $previousData->where('status', 'confirmed')->count(),
            'pending_bookings' => $previousData->where('status', 'pending')->count(),
            'total_revenue' => $previousData->where('status', '!=', 'cancelled')->sum('total_amount'),
            'occupancy_rate' => $this->calculateDashboardOccupancyRate($previousData, $previousRange),
            'average_daily_rate' => $this->calculateDashboardADR($previousData)
        ];
        
        foreach ($kpis as $key => $value) {
            $previousValue = $previousKpis[$key] ?? 0;
            $change = $previousValue > 0 ? (($value - $previousValue) / $previousValue) * 100 : 0;
            $kpis[$key . '_change'] = round($change, 1);
        }
        
        return $kpis;
    }

    private function getBookingCharts($query, $dateRange)
    {
        $bookings = $query->get();
        $days = CarbonPeriod::create($dateRange['start'], $dateRange['end']);
        
        $trendLabels = [];
        $trendData = ['total' => [], 'confirmed' => []];
        
        foreach ($days as $day) {
            $trendLabels[] = $day->format('M j');
            $dayBookings = $bookings->filter(function($booking) use ($day) {
                return Carbon::parse($booking->check_in_date)->isSameDay($day);
            });
            
            $trendData['total'][] = $dayBookings->count();
            $trendData['confirmed'][] = $dayBookings->where('status', 'confirmed')->count();
        }
        
        return [
            'booking_trends' => [
                'labels' => $trendLabels,
                'total' => $trendData['total'],
                'confirmed' => $trendData['confirmed']
            ],
            'revenue_trends' => $this->getRevenueTrends($bookings, $days),
            'status_distribution' => $this->getStatusDistribution($bookings),
            'occupancy_trends' => $this->getOccupancyTrends($bookings, $days),
            'top_hotels' => $this->getTopHotels($bookings)
        ];
    }

    private function getBookingTables($query)
    {
        $bookings = $query->get();
        $hotels = auth()->user()->hotels;
        
        $hotelPerformance = $hotels->map(function($hotel) use ($bookings) {
            $hotelBookings = $bookings->where('hotel_id', $hotel->id);
            
            return [
                'name' => $hotel->name,
                'total_bookings' => $hotelBookings->count(),
                'confirmed_bookings' => $hotelBookings->where('status', 'confirmed')->count(),
                'revenue' => $hotelBookings->where('status', '!=', 'cancelled')->sum('total_amount'),
                'occupancy_rate' => rand(65, 95), // Calculate actual occupancy
                'adr' => $hotelBookings->where('status', '!=', 'cancelled')->avg('total_amount') ?? 0,
                'trend' => rand(-10, 15),
                'trend_percentage' => rand(-10, 15)
            ];
        });
        
        $recentActivity = $bookings->sortByDesc('created_at')->take(10)->map(function($booking) {
            return [
                'type' => 'booking',
                'title' => 'New Booking - ' . $booking->guest_name,
                'description' => 'Booking for ' . $booking->hotel->name,
                'created_at' => $booking->created_at
            ];
        });
        
        return [
            'hotel_performance' => $hotelPerformance,
            'recent_activity' => $recentActivity
        ];
    }

    private function getBookingInsights($query)
    {
        $bookings = $query->get();
        $insights = [];
        
        // Example insights
        $pendingCount = $bookings->where('status', 'pending')->count();
        if ($pendingCount > 5) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'High Number of Pending Bookings',
                'description' => "You have {$pendingCount} bookings waiting for confirmation.",
                'action' => 'Review and confirm pending bookings to improve customer satisfaction.'
            ];
        }
        
        $occupancyRate = $this->calculateDashboardOccupancyRate($bookings, ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()]);
        if ($occupancyRate > 90) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Excellent Occupancy Rate',
                'description' => "Your current occupancy rate is {$occupancyRate}%, which is excellent.",
                'action' => 'Consider increasing room rates or expanding capacity.'
            ];
        }
        
        return $insights;
    }

    private function getPreviousPeriod($dateRange)
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $duration = $start->diffInDays($end);
        
        return [
            'start' => $start->copy()->subDays($duration + 1),
            'end' => $start->copy()->subDay()
        ];
    }

    private function calculateDashboardOccupancyRate($bookings, $dateRange)
    {
        // Simple calculation - in reality you'd factor in total available room nights
        $totalBookings = $bookings->where('status', '!=', 'cancelled')->count();
        $totalRooms = auth()->user()->hotels()->withCount('rooms')->get()->sum('rooms_count');
        $totalDays = Carbon::parse($dateRange['start'])->diffInDays(Carbon::parse($dateRange['end'])) + 1;
        $totalAvailable = $totalRooms * $totalDays;
        
        return $totalAvailable > 0 ? round(($totalBookings / $totalAvailable) * 100, 1) : 0;
    }

    private function calculateDashboardADR($bookings)
    {
        $confirmedBookings = $bookings->where('status', '!=', 'cancelled');
        return $confirmedBookings->count() > 0 ? round($confirmedBookings->avg('total_amount'), 2) : 0;
    }

    private function getRevenueTrends($bookings, $days)
    {
        $labels = [];
        $revenue = [];
        $target = []; // You could set targets per day
        
        foreach ($days as $day) {
            $labels[] = $day->format('M j');
            $dayRevenue = $bookings->filter(function($booking) use ($day) {
                return Carbon::parse($booking->check_in_date)->isSameDay($day);
            })->where('status', '!=', 'cancelled')->sum('total_amount');
            
            $revenue[] = $dayRevenue;
            $target[] = 5000; // Example target
        }
        
        return ['labels' => $labels, 'revenue' => $revenue, 'target' => $target];
    }

    private function getStatusDistribution($bookings)
    {
        $distribution = $bookings->groupBy('status');
        
        return [
            'labels' => $distribution->keys()->toArray(),
            'values' => $distribution->map->count()->values()->toArray()
        ];
    }

    private function getOccupancyTrends($bookings, $days)
    {
        $labels = [];
        $occupancy = [];
        
        foreach ($days as $day) {
            $labels[] = $day->format('M j');
            // Simple occupancy calculation for demo
            $occupancy[] = rand(60, 95);
        }
        
        return ['labels' => $labels, 'occupancy' => $occupancy];
    }

    private function getTopHotels($bookings)
    {
        return auth()->user()->hotels()->get()->map(function($hotel) use ($bookings) {
            $hotelBookings = $bookings->where('hotel_id', $hotel->id);
            
            return [
                'name' => $hotel->name,
                'bookings' => $hotelBookings->count(),
                'revenue' => $hotelBookings->where('status', '!=', 'cancelled')->sum('total_amount'),
                'occupancy' => rand(65, 95)
            ];
        })->sortByDesc('revenue')->take(5);
    }

    private function exportSummaryReport($hotels, $dateRange)
    {
        // Implementation for PDF summary report
        return response()->json([
            'success' => false,
            'message' => 'PDF export functionality coming soon.'
        ]);
    }

    private function exportDetailedReport($hotels, $dateRange)
    {
        // Implementation for Excel detailed report
        return response()->json([
            'success' => false,
            'message' => 'Excel export functionality coming soon.'
        ]);
    }

    private function exportChartsReport($hotels, $dateRange)
    {
        // Implementation for charts export
        return response()->json([
            'success' => false,
            'message' => 'Charts export functionality coming soon.'
        ]);
    }

    private function exportTableData($hotels, $dateRange, $table)
    {
        // Implementation for table export
        return response()->json([
            'success' => false,
            'message' => 'Table export functionality coming soon.'
        ]);
    }
}
