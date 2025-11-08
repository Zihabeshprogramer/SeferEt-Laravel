<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\B2CPackageService;
use App\Services\AmadeusHotelService;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class DashboardController extends Controller
{
    protected B2CPackageService $packageService;
    protected AmadeusHotelService $amadeusHotelService;
    
    /**
     * Create a new controller instance.
     */
    public function __construct(
        B2CPackageService $packageService,
        AmadeusHotelService $amadeusHotelService
    ) {
        $this->packageService = $packageService;
        $this->amadeusHotelService = $amadeusHotelService;
        // Only require auth for specific routes
        // Guests can browse: home, packages, hotels, flights, and their details
        $this->middleware('auth')->except([
            'home', 
            'packages', 
            'packageDetails', 
            'hotels',
            'hotelDetails',
            'flights',
            'flightDetails',
            'about', 
            'contact', 
            'submitContact',
            'explore',
            'getDepartureCities',
            'getDestinations',
            'searchPackages'
        ]);
    }

    /**
     * Display public home page (e-commerce style).
     * 
     * Shows live featured packages from the Featured Products module
     *
     * @return View
     */
    public function home(): View
    {
        try {
            // Get featured packages from service (uses Featured Products module)
            $featuredPackages = $this->packageService->getFeaturedPackages(4);
            
            // Get package statistics
            $packageStats = $this->packageService->getStatistics();
            
            $testimonials = []; // TODO: Get customer testimonials
            $stats = [
                'happyCustomers' => 2500,
                'successfulTrips' => $packageStats['total_bookings'] ?: 1200,
                'partnerCompanies' => 85,
                'yearsOfService' => 8,
                'totalPackages' => $packageStats['total_packages'] ?: 0,
                'averageRating' => round($packageStats['average_rating'], 1) ?: 4.8,
            ];
        } catch (\Exception $e) {
            // Log error but don't break the page
            Log::error('Error loading home page data: ' . $e->getMessage());
            
            // Provide fallback data
            $featuredPackages = collect([]);
            $testimonials = [];
            $stats = [
                'happyCustomers' => 2500,
                'successfulTrips' => 1200,
                'partnerCompanies' => 85,
                'yearsOfService' => 8,
                'totalPackages' => 0,
                'averageRating' => 4.8,
            ];
        }

        return view('customer.home', compact('featuredPackages', 'testimonials', 'stats'));
    }

    /**
     * Display customer dashboard (requires login).
     *
     * @return View
     */
    public function dashboard(): View
    {
        $customer = Auth::user();
        
        // Sample stats for dashboard
        $stats = [
            'totalBookings' => 3,
            'upcomingTrips' => 1,
            'completedTrips' => 2,
            'totalSpent' => 7500,
        ];

        // Sample recent bookings
        $recentBookings = [
            [
                'id' => 1,
                'package_name' => 'Premium Umrah Package',
                'booking_date' => 'Mar 15, 2024',
                'travel_date' => 'May 20, 2024',
                'status' => 'Confirmed',
                'status_color' => 'success',
                'amount' => 3500,
            ],
            [
                'id' => 2,
                'package_name' => 'Essential Umrah Journey',
                'booking_date' => 'Jan 10, 2024',
                'travel_date' => 'Feb 25, 2024',
                'status' => 'Completed',
                'status_color' => 'primary',
                'amount' => 2500,
            ],
            [
                'id' => 3,
                'package_name' => 'Luxury Umrah Experience',
                'booking_date' => 'Dec 5, 2023',
                'travel_date' => 'Jan 15, 2024',
                'status' => 'Completed',
                'status_color' => 'primary',
                'amount' => 4500,
            ],
        ];
        
        // Sample upcoming trips
        $upcomingTrips = [
            [
                'id' => 1,
                'package_name' => 'Premium Umrah Package',
                'travel_date' => 'May 20, 2024',
                'days_until' => 45,
                'status' => 'Confirmed'
            ]
        ];
        
        // Sample available packages
        $availablePackages = [
            [
                'id' => 1,
                'name' => 'Grand Umrah Experience',
                'description' => 'Experience the ultimate spiritual journey with luxury accommodations in Makkah and Madinah, complete with guided tours and premium transportation.',
                'price' => 4200,
                'duration' => 14,
                'rating' => 4.8,
                'featured' => true,
                'includes' => ['5-star Hotels', 'Direct Flights', 'Airport Transfers', 'Guided Tours', 'Visa Processing'],
                'partner' => 'Al-Safar Travel'
            ],
            [
                'id' => 2,
                'name' => 'Spiritual Umrah Journey',
                'description' => 'A comprehensive Umrah package designed for a meaningful spiritual experience with comfortable accommodations and essential services.',
                'price' => 2800,
                'duration' => 10,
                'rating' => 4.6,
                'featured' => false,
                'includes' => ['4-star Hotels', 'Round-trip Flights', 'Group Transportation', 'Basic Guidance', 'Visa Support'],
                'partner' => 'Blessed Journeys'
            ],
            [
                'id' => 3,
                'name' => 'Family Umrah Package',
                'description' => 'Perfect for families seeking to perform Umrah together, with spacious accommodations and family-friendly services throughout the journey.',
                'price' => 3200,
                'duration' => 12,
                'rating' => 4.7,
                'featured' => true,
                'includes' => ['Family Rooms', 'Child Care', 'Educational Tours', 'Group Activities', 'Complete Support'],
                'partner' => 'Family First Travel'
            ],
            [
                'id' => 4,
                'name' => 'Budget Umrah Package',
                'description' => 'An affordable Umrah package that doesn\'t compromise on the essential spiritual experience, perfect for first-time pilgrims.',
                'price' => 1800,
                'duration' => 7,
                'rating' => 4.3,
                'featured' => false,
                'includes' => ['3-star Hotels', 'Economy Flights', 'Shared Transport', 'Basic Guidance', 'Visa Processing'],
                'partner' => 'Simple Pilgrim'
            ],
        ];

        return view('customer.dashboard', compact('stats', 'recentBookings', 'upcomingTrips', 'availablePackages', 'customer'));
    }

    /**
     * Display customer bookings.
     *
     * @return View
     */
    public function bookings(): View
    {
        $customer = Auth::user();
        
        $stats = [
            'total' => 0,
            'confirmed' => 0,
            'pending' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];

        $bookings = []; // TODO: Implement actual booking data

        return view('customer.bookings', compact('bookings', 'stats', 'customer'));
    }

    /**
     * Display flights search page (public access).
     *
     * @return View
     */
    public function flights(Request $request): View
    {
        $customer = Auth::user();
        
        return view('customer.flights', compact('customer'));
    }

    /**
     * Display flight booking page (requires authentication).
     *
     * @param  string  $hash
     * @return View
     */
    public function flightBooking(string $hash): View
    {
        $customer = Auth::user();
        
        return view('customer.flight-booking', compact('hash', 'customer'));
    }

    /**
     * Display user's flight bookings (requires authentication).
     *
     * @return View
     */
    public function myFlightBookings(): View
    {
        $customer = Auth::user();
        
        return view('customer.my-flight-bookings', compact('customer'));
    }
    
    /**
     * Display hotels search page (public access).
     *
     * @return View
     */
    public function hotels(Request $request): View
    {
        $customer = Auth::user();
        $hotels = [];
        $searchPerformed = false;
        $searchParams = [];
        
        // Check if search parameters are provided
        if ($request->has(['location', 'check_in', 'check_out'])) {
            $searchPerformed = true;
            $location = $request->input('location', 'makkah');
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');
            $guests = $request->input('guests', 1);
            $rooms = $request->input('rooms', 1);
            $starRating = $request->input('stars');
            
            $searchParams = [
                'location' => $location,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => $guests,
                'rooms' => $rooms,
                'star_rating' => $starRating,
            ];
            
            try {
                // Search local hotels directly
                $localHotels = $this->searchLocalHotels($location, $checkIn, $checkOut, $guests, $starRating);
                
                Log::info('Local hotels search completed', [
                    'location' => $location,
                    'count' => count($localHotels)
                ]);
                
                // Search Amadeus hotels (skip if taking too long)
                $amadeusHotels = [];
                try {
                    $amadeusResults = $this->amadeusHotelService->searchHotels(
                        $location,
                        $checkIn,
                        $checkOut,
                        $guests,
                        $rooms
                    );
                    
                    Log::info('Amadeus hotels search completed', [
                        'location' => $location,
                        'count' => count($amadeusResults['data'] ?? [])
                    ]);
                    
                    if (!empty($amadeusResults['data'])) {
                        foreach ($amadeusResults['data'] as $hotelData) {
                            $transformed = $this->amadeusHotelService->transformToUnifiedFormat($hotelData);
                            
                            // Apply filters
                            if ($starRating && $transformed['star_rating'] != $starRating) {
                                continue;
                            }
                            
                            $amadeusHotels[] = $transformed;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Amadeus search failed', ['error' => $e->getMessage()]);
                    // Continue with local hotels only
                }
                
                // Merge results - local first, then Amadeus
                $hotels = array_merge($localHotels, $amadeusHotels);
                
                // Log final results
                Log::info('Hotel search completed', [
                    'location' => $location,
                    'local_count' => count($localHotels),
                    'amadeus_count' => count($amadeusHotels),
                    'total_count' => count($hotels)
                ]);
                
                // If no hotels found, provide helpful message
                if (empty($hotels)) {
                    session()->flash('info', 'No hotels found for "' . $location . '". Try searching for nearby cities or check your dates.');
                }
                
            } catch (\Exception $e) {
                Log::error('Hotel search failed', [
                    'error' => $e->getMessage(),
                    'params' => $searchParams
                ]);
                session()->flash('error', 'Unable to search hotels at this time. Please try again later.');
            }
        }
        
        return view('customer.hotels', compact('hotels', 'customer', 'searchPerformed', 'searchParams'));
    }

    /**
     * Search local hotels
     */
    private function searchLocalHotels(
        string $location,
        string $checkIn,
        string $checkOut,
        int $guests,
        ?int $starRating = null
    ): array {
        // Map IATA codes to city names for local search
        $cityMap = [
            'MKK' => 'Mecca|Makkah',  // Support both spellings
            'MED' => 'Medina|Madinah',  // Support both spellings
            'JED' => 'Jeddah',
            'RUH' => 'Riyadh',
            'DXB' => 'Dubai',
            'AUH' => 'Abu Dhabi',
        ];
        
        // Convert code to city name if it's a known code
        $searchLocation = $cityMap[$location] ?? $location;
        
        // Split city variations (e.g., "Mecca|Makkah")
        $cityVariations = explode('|', $searchLocation);
        
        $query = Hotel::query()
            ->with(['rooms'])
            ->where('status', 'active')
            ->where('is_active', true)
            ->where(function ($q) use ($cityVariations, $location) {
                foreach ($cityVariations as $cityName) {
                    $q->orWhere('city', 'like', "%{$cityName}%")
                      ->orWhere('address', 'like', "%{$cityName}%")
                      ->orWhere('country', 'like', "%{$cityName}%");
                }
                // Also search by the original location code
                $q->orWhere('city', 'like', "%{$location}%")
                  ->orWhere('address', 'like', "%{$location}%");
            });

        if ($starRating) {
            $query->where('star_rating', $starRating);
        }

        $hotels = $query->get();
        
        Log::info('Local hotels query executed', [
            'location' => $location,
            'search_location' => $searchLocation,
            'hotels_found' => $hotels->count(),
            'hotel_ids' => $hotels->pluck('id')->toArray()
        ]);

        $results = [];
        foreach ($hotels as $hotel) {
            // Check availability
            $availableRooms = $hotel->rooms()
                ->where('is_available', true)
                ->where('max_occupancy', '>=', $guests)
                ->get();
            
            Log::info('Checking hotel rooms', [
                'hotel_id' => $hotel->id,
                'hotel_name' => $hotel->name,
                'total_rooms' => $hotel->rooms()->count(),
                'available_rooms' => $availableRooms->count(),
                'guests_needed' => $guests
            ]);

            if ($availableRooms->isEmpty()) {
                Log::warning('Hotel skipped - no available rooms', [
                    'hotel_id' => $hotel->id,
                    'hotel_name' => $hotel->name
                ]);
                continue;
            }

            // Get minimum price
            $minPrice = $availableRooms->min('base_price');
            
            // Get first image or use placeholder
            $images = $hotel->images ?? [];
            $firstImage = is_array($images) && !empty($images) 
                ? $images[0] 
                : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop';
            
            // Parse amenities
            $amenities = $hotel->amenities ?? [];
            if (is_string($amenities)) {
                $amenities = json_decode($amenities, true) ?? [];
            }

            $hotelData = [
                'id' => (string) $hotel->id,
                'source' => 'local',
                'name' => $hotel->name,
                'description' => $hotel->description,
                'type' => $hotel->type,
                'stars' => $hotel->star_rating ?? 3,  // View expects 'stars'
                'rating' => 4.5,  // Default rating (you can add this to DB later)
                'image' => $firstImage,  // View expects 'image'
                'location' => $hotel->city . ', ' . $hotel->country,  // View expects 'location'
                'address' => $hotel->address,
                'city' => $hotel->city,
                'country' => $hotel->country,
                'distance_to_haram' => $hotel->distance_to_haram 
                    ? number_format($hotel->distance_to_haram, 1) . ' km' 
                    : 'N/A',  // View expects 'distance_to_haram'
                'amenities' => is_array($amenities) ? $amenities : [],
                'price' => $minPrice,  // View expects simple price number
                'available_rooms' => $availableRooms->count(),
            ];

            $results[] = $hotelData;
        }

        return $results;
    }

    /**
     * Display available packages for booking (public access).
     *
     * @return View
     */
    public function packages(Request $request): View
    {
        $customer = Auth::user(); // Will be null if not logged in
        
        // Get filters from request
        $filters = $this->buildFiltersFromRequest($request);

        // Handle search query
        if ($request->filled('search')) {
            $packages = $this->packageService->searchPackages(
                $request->get('search'),
                $filters
            );
        } else {
            $packages = $this->packageService->getPaginatedPackages($filters);
        }
        
        // Get filter options for the sidebar
        $filterOptions = $this->packageService->getFilterOptions();

        return view('customer.packages', compact('packages', 'filterOptions', 'customer'));
    }

    /**
     * Display package details (public access).
     *
     * @param  mixed  $identifier
     * @return View
     */
    public function packageDetails($identifier): View
    {
        $customer = Auth::user();
        
        // Get package details from service
        $package = $this->packageService->getPackageDetails($identifier);
        
        if (!$package) {
            abort(404, 'Package not found');
        }
        
        // Get related packages
        $relatedPackages = $this->packageService->getRelatedPackages(
            \App\Models\Package::find($package->id),
            4
        );
        

        return view('customer.package-details', compact('package', 'relatedPackages', 'customer'));
    }

    /**
     * Display explore page.
     *
     * @return View
     */
    public function explore(): View
    {
        $customer = Auth::user();
        
        // Sample destinations for explore page
        $destinations = [
            [
                'id' => 1,
                'name' => 'Makkah Al-Mukarramah',
                'description' => 'The holiest city in Islam, home to the Grand Mosque and the Kaaba',
                'image' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=800&h=600&fit=crop',
                'attractions' => ['Masjid al-Haram', 'Kaaba', 'Safa and Marwa', 'Mina', 'Muzdalifah'],
                'best_time' => 'Year-round'
            ],
            [
                'id' => 2,
                'name' => 'Madinah Al-Munawwarah',
                'description' => 'The radiant city, second holiest city in Islam and final resting place of Prophet Muhammad (PBUH)',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop',
                'attractions' => ['Masjid an-Nabawi', "Prophet's Mosque", 'Quba Mosque', 'Mount Uhud', 'Jannat al-Baqi'],
                'best_time' => 'Year-round'
            ],
            [
                'id' => 3,
                'name' => 'Jeddah',
                'description' => 'The gateway to Makkah, historic port city with rich Islamic heritage',
                'image' => 'https://images.unsplash.com/photo-1549308509-7e78b5f4b8a9?w=800&h=600&fit=crop',
                'attractions' => ['Al-Balad Historic District', 'King Fahd Fountain', 'Corniche', 'Red Sea Mall'],
                'best_time' => 'November to March'
            ],
            [
                'id' => 4,
                'name' => 'Taif',
                'description' => 'The summer capital, known for its pleasant climate and rose gardens',
                'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?w=800&h=600&fit=crop',
                'attractions' => ['Shubra Palace', 'Al Rudaf Park', 'Al Hada Mountain', 'Rose Gardens'],
                'best_time' => 'May to September'
            ]
        ];
        
        // Sample experiences
        $experiences = [
            [
                'id' => 1,
                'title' => 'Spiritual Journey to Makkah',
                'description' => 'Experience the spiritual heart of Islam with guided tours',
                'duration' => '3 days',
                'price' => 450,
                'rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=400&h=300&fit=crop'
            ],
            [
                'id' => 2,
                'title' => 'Madinah Sacred Sites Tour',
                'description' => 'Visit the sacred sites and learn Islamic history',
                'duration' => '2 days',
                'price' => 320,
                'rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop'
            ],
            [
                'id' => 3,
                'title' => 'Historical Jeddah Walking Tour',
                'description' => 'Explore the UNESCO World Heritage Al-Balad district',
                'duration' => '1 day',
                'price' => 85,
                'rating' => 4.6,
                'image' => 'https://images.unsplash.com/photo-1549308509-7e78b5f4b8a9?w=400&h=300&fit=crop'
            ]
        ];
        
        return view('customer.explore', compact('destinations', 'experiences', 'customer'));
    }

    /**
     * Display about page.
     *
     * @return View
     */
    public function about(): View
    {
        $customer = Auth::user();
        
        return view('customer.about', compact('customer'));
    }

    /**
     * Display contact page.
     *
     * @return View
     */
    public function contact(): View
    {
        $customer = Auth::user();
        
        return view('customer.contact', compact('customer'));
    }

    /**
     * Handle contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // TODO: Send email or store contact message
        
        return redirect()->route('contact')->with('success', 'Thank you for your message! We will get back to you soon.');
    }

    /**
     * Display customer profile.
     *
     * @return View
     */
    public function profile(): View
    {
        $customer = Auth::user();
        
        return view('customer.profile', compact('customer'));
    }

    /**
     * Update customer profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $customer = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'has_umrah_experience' => 'boolean',
            'completed_umrah_count' => 'nullable|integer|min:0',
            'special_requirements' => 'nullable|string|max:1000',
        ]);

        $customer->update($request->only([
            'name', 'phone', 'date_of_birth', 'gender', 'nationality',
            'address', 'city', 'country', 'emergency_contact_name',
            'emergency_contact_phone', 'has_umrah_experience',
            'completed_umrah_count', 'special_requirements'
        ]));

        return redirect()->route('customer.profile')->with('success', 'Profile updated successfully!');
    }

    /**
     * Start booking process (requires authentication).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startBooking(Request $request, $id)
    {
        $customer = Auth::user();
        
        // TODO: Create booking record and redirect to checkout
        
        return redirect()->route('customer.checkout', ['bookingId' => 'temp_id'])
                        ->with('success', 'Package added to booking! Please complete your details.');
    }

    /**
     * Display checkout page (requires authentication).
     *
     * @param  string  $bookingId
     * @return View
     */
    public function checkout($bookingId): View
    {
        $customer = Auth::user();
        
        // TODO: Get booking details
        $booking = [
            'id' => $bookingId,
            'package' => 'Premium Umrah Package',
            'price' => 2500,
            'duration' => 14,
        ];

        return view('customer.checkout', compact('booking', 'customer'));
    }

    /**
     * Process checkout (requires authentication).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $bookingId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCheckout(Request $request, $bookingId)
    {
        $customer = Auth::user();
        
        // TODO: Validate payment and create booking
        
        return redirect()->route('customer.dashboard')
                        ->with('success', 'Booking confirmed! You will receive confirmation details via email.');
    }

    /**
     * Display booking details (requires authentication).
     *
     * @param  int  $id
     * @return View
     */
    public function bookingDetails($id): View
    {
        $customer = Auth::user();
        
        // TODO: Get booking details
        $booking = [
            'id' => $id,
            'package' => 'Premium Umrah Package',
            'status' => 'confirmed',
            'booking_date' => now(),
            'travel_date' => now()->addMonths(2),
        ];

        return view('customer.booking-details', compact('booking', 'customer'));
    }

    /**
     * Display flight details (public access).
     *
     * @param  int  $id
     * @return View
     */
    public function flightDetails($id): View
    {
        $customer = Auth::user();
        
        // Sample flight data
        $flight = [
            'id' => $id,
            'airline' => 'Saudia',
            'from' => 'New York (JFK)',
            'to' => 'Jeddah (JED)',
            'departure' => '2024-05-20 10:30:00',
            'arrival' => '2024-05-21 06:45:00',
            'duration' => '14h 15m',
            'stops' => 0,
            'price' => 1250,
            'class' => 'Business'
        ];

        return view('customer.flight-details', compact('flight', 'customer'));
    }

    /**
     * Display hotel details (public access).
     *
     * @param  int  $id
     * @return View
     */
    public function hotelDetails($id): View
    {
        $customer = Auth::user();
        
        // Fetch hotel from database
        $hotelModel = Hotel::with(['rooms'])->findOrFail($id);
        
        // Parse images
        $images = $hotelModel->images ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }
        if (empty($images)) {
            $images = [
                'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&h=600&fit=crop'
            ];
        }
        
        // Parse amenities
        $amenities = $hotelModel->amenities ?? [];
        if (is_string($amenities)) {
            $amenities = json_decode($amenities, true) ?? [];
        }
        
        // Get room types
        $room_types = [];
        foreach ($hotelModel->rooms as $room) {
            $roomImages = $room->images ?? [];
            if (is_string($roomImages)) {
                $roomImages = json_decode($roomImages, true) ?? [];
            }
            $roomImage = !empty($roomImages) ? $roomImages[0] : 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=400&h=300&fit=crop';
            
            $room_types[] = [
                'id' => $room->id,
                'name' => $room->name ?? ucfirst($room->category),
                'description' => $room->description ?? 'Comfortable room with modern amenities',
                'price_per_night' => $room->base_price,
                'max_guests' => $room->max_occupancy,
                'beds' => $room->bed_count . ' ' . ucfirst($room->bed_type),
                'size' => $room->size_sqm ? $room->size_sqm . ' sqm' : 'N/A',
                'image' => $roomImage,
                'is_available' => $room->is_available && $room->is_active
            ];
        }
        
        // Calculate price range
        $prices = $hotelModel->rooms->pluck('base_price')->filter();
        $minPrice = $prices->min() ?? 0;
        
        // Format hotel data for view
        $hotel = [
            'id' => $hotelModel->id,
            'name' => $hotelModel->name,
            'location' => $hotelModel->city . ', ' . $hotelModel->country,
            'distance_to_center' => $hotelModel->distance_to_haram 
                ? number_format($hotelModel->distance_to_haram, 1) . ' km to Haram' 
                : 'N/A',
            'address' => $hotelModel->address,
            'stars' => $hotelModel->star_rating ?? 3,
            'price_per_night' => $minPrice,
            'rating' => 4.5, // Default (add reviews table later)
            'rating_text' => 'Very Good',
            'review_count' => 0, // Default (add reviews table later)
            'description' => $hotelModel->description ?? 'Experience comfort and convenience at ' . $hotelModel->name . '. Located in ' . $hotelModel->city . ', this hotel offers excellent amenities and service.',
            'total_rooms' => $hotelModel->rooms->count(),
            'payment_policy' => 'Pay at hotel',
            'images' => $images,
            'room_types' => $room_types,
            'amenities' => $amenities,
            'check_in_time' => $hotelModel->check_in_time ?? '15:00',
            'check_out_time' => $hotelModel->check_out_time ?? '11:00',
            'phone' => $hotelModel->phone,
            'email' => $hotelModel->email,
            'website' => $hotelModel->website,
            'rating_breakdown' => [
                'location' => 4.5,
                'cleanliness' => 4.5,
                'service' => 4.5,
                'value' => 4.5,
                'amenities' => 4.5
            ],
            'recent_reviews' => [], // TODO: Add reviews system
            'nearby_attractions' => $hotelModel->city === 'Mecca' || $hotelModel->city === 'Makkah' ? [
                ['name' => 'Masjid al-Haram', 'distance' => $hotelModel->distance_to_haram ? number_format($hotelModel->distance_to_haram, 1) . ' km' : 'N/A'],
                ['name' => 'Kaaba', 'distance' => 'Inside Masjid al-Haram'],
                ['name' => 'Safa and Marwa', 'distance' => 'Inside Masjid al-Haram']
            ] : [],
            'airport_distance' => $hotelModel->distance_to_airport 
                ? number_format($hotelModel->distance_to_airport, 1) . ' km' 
                : 'N/A',
            'train_distance' => 'N/A',
            'bus_distance' => 'N/A'
        ];

        // Similar hotels
        $similar_hotels = [
            [
                'name' => 'Conrad Makkah',
                'stars' => 5,
                'location' => 'Makkah',
                'price' => 420,
                'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=300&h=200&fit=crop'
            ],
            [
                'name' => 'Swissôtel Makkah',
                'stars' => 5,
                'location' => 'Makkah',
                'price' => 380,
                'image' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=300&h=200&fit=crop'
            ],
            [
                'name' => 'Pullman Zamzam',
                'stars' => 5,
                'location' => 'Makkah',
                'price' => 350,
                'image' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=300&h=200&fit=crop'
            ]
        ];

        return view('customer.hotel-details', compact('hotel', 'similar_hotels', 'customer'));
    }
    
    /**
     * Handle package search API endpoint
     */
    public function searchPackages(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'filters' => 'sometimes|array',
        ]);
        
        $filters = $this->buildFiltersFromRequest($request);
        
        $packages = $this->packageService->searchPackages(
            $request->get('query'),
            $filters,
            10 // Limit for AJAX requests
        );
        
        return response()->json([
            'packages' => $packages->items(),
            'pagination' => [
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'per_page' => $packages->perPage(),
                'total' => $packages->total(),
            ]
        ]);
    }
    
    /**
     * Get unique departure cities from all packages
     */
    public function getDepartureCities(Request $request)
    {
        $query = $request->get('query', '');
        
        // Get all unique departure cities from packages
        $departureCities = \App\Models\Package::whereNotNull('departure_cities')
            ->where('status', 'active')
            ->get()
            ->pluck('departure_cities')
            ->flatten()
            ->unique()
            ->values()
            ->filter(function($city) use ($query) {
                return empty($query) || stripos($city, $query) !== false;
            })
            ->sort()
            ->values()
            ->take(10);
        
        return response()->json([
            'cities' => $departureCities
        ]);
    }
    
    /**
     * Get unique destinations from all packages
     */
    public function getDestinations(Request $request)
    {
        $query = $request->get('query', '');
        
        // Get all unique destinations from packages
        $destinations = \App\Models\Package::whereNotNull('destinations')
            ->where('status', 'active')
            ->get()
            ->pluck('destinations')
            ->flatten()
            ->unique()
            ->values()
            ->filter(function($destination) use ($query) {
                return empty($query) || stripos($destination, $query) !== false;
            })
            ->sort()
            ->values()
            ->take(10);
        
        return response()->json([
            'destinations' => $destinations
        ]);
    }
    
    /**
     * Build filters array from request parameters
     */
    protected function buildFiltersFromRequest(Request $request): array
    {
        $filters = [];
        
        if ($request->filled('departure')) {
            $filters['departure'] = $request->get('departure');
        }
        
        if ($request->filled('departure_date')) {
            $filters['departure_date'] = $request->get('departure_date');
        }
        
        if ($request->filled('travelers')) {
            $filters['travelers'] = (int) $request->get('travelers');
        }
        
        if ($request->filled('budget')) {
            $filters['budget'] = $request->get('budget');
        }
        
        if ($request->filled('price_range')) {
            $filters['price_range'] = (array) $request->get('price_range');
        }
        
        if ($request->filled('duration')) {
            $filters['duration'] = $request->get('duration');
        }
        
        // Handle both singular 'destination' and plural 'destinations'
        if ($request->filled('destination')) {
            $filters['destination'] = $request->get('destination');
        }
        
        if ($request->filled('destinations')) {
            $filters['destinations'] = (array) $request->get('destinations');
        }
        
        if ($request->filled('type')) {
            $filters['type'] = (array) $request->get('type');
        }
        
        if ($request->filled('rating')) {
            $filters['rating'] = (float) $request->get('rating');
        }
        
        if ($request->filled('features')) {
            $filters['features'] = (array) $request->get('features');
        }
        
        if ($request->filled('sort')) {
            $filters['sort'] = $request->get('sort');
        }
        
        return $filters;
    }

    /**
     * Flight checkout page.
     *
     * @param  int  $id
     * @return View
     */
    public function flightCheckout($id): View
    {
        $customer = Auth::user();
        
        // Sample flight data for checkout
        $flight = [
            'id' => $id,
            'airline' => 'Saudia',
            'from' => 'New York (JFK)',
            'to' => 'Jeddah (JED)',
            'departure' => '2024-05-20 10:30:00',
            'arrival' => '2024-05-21 06:45:00',
            'price' => 1250
        ];

        return view('customer.flight-checkout', compact('flight', 'customer'));
    }

    /**
     * Hotel checkout page.
     *
     * @param  int  $id
     * @return View
     */
    public function hotelCheckout($id): View
    {
        $customer = Auth::user();
        
        // Sample hotel data for checkout
        $hotel = [
            'id' => $id,
            'name' => 'Hilton Makkah Convention Hotel',
            'location' => 'Makkah, Saudi Arabia',
            'price_per_night' => 320
        ];

        return view('customer.hotel-checkout', compact('hotel', 'customer'));
    }

    /**
     * Package checkout page.
     *
     * @param  int  $id
     * @return View
     */
    public function packageCheckout($id): View
    {
        $customer = Auth::user();
        
        // Sample package data for checkout
        $package = [
            'id' => $id,
            'name' => 'Premium Umrah Package',
            'description' => 'Complete Umrah experience with luxury accommodation',
            'price' => 2500,
            'duration' => 14
        ];

        return view('customer.package-checkout', compact('package', 'customer'));
    }
}
