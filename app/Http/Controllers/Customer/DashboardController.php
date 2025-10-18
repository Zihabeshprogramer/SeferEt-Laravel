<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Only require auth for specific routes
        $this->middleware('auth')->except(['home', 'packages', 'packageDetails', 'about', 'contact', 'submitContact']);
    }

    /**
     * Display public home page (e-commerce style).
     *
     * @return View
     */
    public function home(): View
    {
        // Sample featured packages for the new home page
        $featuredPackages = [
            [
                'id' => 1,
                'name' => 'Premium Umrah Experience',
                'description' => 'Complete spiritual journey with 5-star accommodations in Makkah and Madinah, guided tours, and premium transportation services.',
                'price' => 3500,
                'duration' => 14,
                'rating' => 4.8,
                'featured' => true
            ],
            [
                'id' => 2,
                'name' => 'Family Umrah Package',
                'description' => 'Perfect for families seeking a meaningful spiritual experience with child-friendly services and spacious accommodations.',
                'price' => 2800,
                'duration' => 10,
                'rating' => 4.7,
                'featured' => true
            ],
            [
                'id' => 3,
                'name' => 'Budget Spiritual Journey',
                'description' => 'Affordable yet comprehensive Umrah package without compromising on the essential spiritual experience.',
                'price' => 1800,
                'duration' => 7,
                'rating' => 4.5,
                'featured' => true
            ],
            [
                'id' => 4,
                'name' => 'Luxury Umrah Retreat',
                'description' => 'Ultimate luxury experience with exclusive services, private transfers, and personalized spiritual guidance.',
                'price' => 5200,
                'duration' => 21,
                'rating' => 4.9,
                'featured' => true
            ]
        ];
        
        $testimonials = []; // TODO: Get customer testimonials
        $stats = [
            'happyCustomers' => 2500,
            'successfulTrips' => 1200,
            'partnerCompanies' => 85,
            'yearsOfService' => 8,
        ];

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
    public function flights(): View
    {
        $customer = Auth::user();
        
        // Sample flight results
        $flights = [
            [
                'id' => 1,
                'airline' => 'Saudia',
                'from' => 'New York (JFK)',
                'to' => 'Jeddah (JED)',
                'departure' => '2024-05-20 10:30',
                'arrival' => '2024-05-21 06:45',
                'duration' => '14h 15m',
                'stops' => 1,
                'price' => 1250,
                'class' => 'Economy'
            ],
            [
                'id' => 2,
                'airline' => 'Emirates',
                'from' => 'London (LHR)',
                'to' => 'Jeddah (JED)',
                'departure' => '2024-05-20 14:20',
                'arrival' => '2024-05-21 02:30',
                'duration' => '10h 10m',
                'stops' => 0,
                'price' => 1450,
                'class' => 'Economy'
            ]
        ];
        
        return view('customer.flights', compact('flights', 'customer'));
    }
    
    /**
     * Display hotels search page (public access).
     *
     * @return View
     */
    public function hotels(): View
    {
        $customer = Auth::user();
        
        // Sample hotel results
        $hotels = [
            [
                'id' => 1,
                'name' => 'Hilton Makkah Convention Hotel',
                'location' => 'Makkah',
                'distance_to_haram' => '0.5 km',
                'stars' => 5,
                'price' => 320,
                'rating' => 4.7,
                'amenities' => ['WiFi', 'Restaurant', 'Spa', 'Fitness Center'],
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=250&fit=crop'
            ],
            [
                'id' => 2,
                'name' => 'Dar Al Eiman Royal Hotel',
                'location' => 'Madinah', 
                'distance_to_haram' => '0.2 km',
                'stars' => 4,
                'price' => 180,
                'rating' => 4.5,
                'amenities' => ['WiFi', 'Restaurant', 'Room Service', 'Laundry'],
                'image' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=400&h=250&fit=crop'
            ]
        ];
        
        return view('customer.hotels', compact('hotels', 'customer'));
    }

    /**
     * Display available packages for booking (public access).
     *
     * @return View
     */
    public function packages(): View
    {
        $customer = Auth::user(); // Will be null if not logged in
        
        // TODO: Implement package filtering and search
        $packages = []; // TODO: Get available packages from B2B partners
        $filters = [
            'destinations' => ['Makkah', 'Madinah', 'Makkah & Madinah'],
            'price_ranges' => [
                '0-1000' => '$0 - $1,000',
                '1000-2500' => '$1,000 - $2,500', 
                '2500-5000' => '$2,500 - $5,000',
                '5000+' => '$5,000+'
            ],
            'durations' => [
                '7' => '7 days',
                '14' => '14 days',
                '21' => '21 days',
                '30' => '30+ days'
            ],
        ];

        return view('customer.packages', compact('packages', 'filters', 'customer'));
    }

    /**
     * Display package details (public access).
     *
     * @param  int  $id
     * @return View
     */
    public function packageDetails($id): View
    {
        $customer = Auth::user();
        
        // TODO: Get package details from database
        $package = [ // Mock data
            'id' => $id,
            'name' => 'Premium Umrah Package',
            'description' => 'Complete Umrah experience with luxury accommodation',
            'price' => 2500,
            'duration' => 14,
            'includes' => ['Flight', 'Hotel', 'Transportation', 'Visa', 'Guide'],
            'partner' => 'ABC Travel Company',
            'images' => [],
        ];
        
        $relatedPackages = []; // TODO: Get related packages

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
        
        // Sample hotel data
        $hotel = [
            'id' => $id,
            'name' => 'Hilton Makkah Convention Hotel',
            'location' => 'Makkah, Saudi Arabia',
            'distance_to_center' => '0.5 km',
            'address' => 'King Abdul Aziz Rd, Makkah 24231, Saudi Arabia',
            'stars' => 5,
            'price_per_night' => 320,
            'rating' => 4.7,
            'rating_text' => 'Excellent',
            'review_count' => 1250,
            'description' => 'Experience luxury and comfort at the Hilton Makkah Convention Hotel, ideally located just steps away from the Grand Mosque. This premium hotel offers world-class amenities, spacious rooms, and exceptional service to make your pilgrimage memorable.',
            'total_rooms' => 650,
            'payment_policy' => 'Pay at hotel',
            'images' => [
                'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800&h=600&fit=crop'
            ],
            'room_types' => [
                [
                    'name' => 'Deluxe King Room',
                    'description' => 'Spacious room with city or courtyard views',
                    'price_per_night' => 280,
                    'max_guests' => 2,
                    'beds' => '1 King Bed',
                    'size' => '35 sqm',
                    'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=400&h=300&fit=crop'
                ],
                [
                    'name' => 'Family Suite',
                    'description' => 'Perfect for families with separate living area',
                    'price_per_night' => 450,
                    'max_guests' => 4,
                    'beds' => '1 King + 2 Singles',
                    'size' => '65 sqm',
                    'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=300&fit=crop'
                ],
                [
                    'name' => 'Executive Suite',
                    'description' => 'Luxurious suite with panoramic city views',
                    'price_per_night' => 680,
                    'max_guests' => 3,
                    'beds' => '1 King Bed',
                    'size' => '85 sqm',
                    'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400&h=300&fit=crop'
                ]
            ],
            'rating_breakdown' => [
                'location' => 4.8,
                'cleanliness' => 4.7,
                'service' => 4.6,
                'value' => 4.5,
                'amenities' => 4.8
            ],
            'recent_reviews' => [
                [
                    'name' => 'Ahmed Al-Rashid',
                    'rating' => 5,
                    'date' => '2 days ago',
                    'comment' => 'Exceptional location and service. The proximity to the Grand Mosque made our pilgrimage so much easier. Highly recommend!'
                ],
                [
                    'name' => 'Fatima Hassan',
                    'rating' => 4,
                    'date' => '1 week ago',
                    'comment' => 'Beautiful hotel with great amenities. The staff was very helpful throughout our stay. The breakfast buffet was excellent.'
                ],
                [
                    'name' => 'Mohamed Ibrahim',
                    'rating' => 5,
                    'date' => '2 weeks ago',
                    'comment' => 'Perfect for Umrah pilgrims. Clean rooms, professional staff, and the location cannot be beaten. Will definitely stay here again.'
                ]
            ],
            'nearby_attractions' => [
                ['name' => 'Masjid al-Haram', 'distance' => '0.1 km'],
                ['name' => 'Kaaba', 'distance' => '0.2 km'],
                ['name' => 'Safa and Marwa', 'distance' => '0.3 km'],
                ['name' => 'Clock Tower Museum', 'distance' => '0.8 km'],
                ['name' => 'Mount Arafat', 'distance' => '25 km']
            ],
            'airport_distance' => '95 km to King Abdulaziz Airport',
            'train_distance' => '8 km to Makkah Central Station',
            'bus_distance' => '0.2 km to nearest bus stop'
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
