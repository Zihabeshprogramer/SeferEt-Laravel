<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Package API Controller - Public endpoints for Flutter app
 */
class PackageController extends Controller
{
    /**
     * Get paginated list of active packages
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Package::with(['creator:id,name', 'hotels:id,name,star_rating', 'flights:id,airline,flight_number'])
                ->where('status', Package::STATUS_ACTIVE)
                ->where('approval_status', Package::APPROVAL_APPROVED);

            // Apply filters
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('min_price')) {
                $query->where('base_price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('base_price', '<=', $request->max_price);
            }

            if ($request->filled('duration')) {
                $query->where('duration', $request->duration);
            }

            if ($request->filled('destinations')) {
                $destinations = is_array($request->destinations) 
                    ? $request->destinations 
                    : explode(',', $request->destinations);
                
                foreach ($destinations as $destination) {
                    $query->whereJsonContains('destinations', trim($destination));
                }
            }

            if ($request->filled('features')) {
                $features = is_array($request->features) 
                    ? $request->features 
                    : explode(',', $request->features);
                
                foreach ($features as $feature) {
                    $query->whereJsonContains('features', trim($feature));
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            switch ($sortBy) {
                case 'price':
                    $query->orderBy('base_price', $sortOrder);
                    break;
                case 'rating':
                    $query->orderBy('average_rating', $sortOrder);
                    break;
                case 'duration':
                    $query->orderBy('duration', $sortOrder);
                    break;
                case 'popularity':
                    $query->orderBy('bookings_count', $sortOrder);
                    break;
                default:
                    $query->orderBy('created_at', $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 20), 50);
            $packages = $query->paginate($perPage);

            // Transform the data
            $transformedPackages = $packages->through(function ($package) {
                return $this->transformPackageForApi($package);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'packages' => $transformedPackages->items(),
                    'pagination' => [
                        'current_page' => $packages->currentPage(),
                        'last_page' => $packages->lastPage(),
                        'per_page' => $packages->perPage(),
                        'total' => $packages->total(),
                        'has_more_pages' => $packages->hasMorePages(),
                    ]
                ],
                'message' => 'Packages retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching packages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching packages',
                'data' => null
            ], 500);
        }
    }

    /**
     * Search packages by query
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1',
        ]);

        try {
            $searchQuery = $request->input('query');
            $perPage = min($request->get('per_page', 20), 50);

            $packages = Package::with(['creator:id,name', 'hotels:id,name,star_rating'])
                ->where('status', Package::STATUS_ACTIVE)
                ->where('approval_status', Package::APPROVAL_APPROVED)
                ->where(function ($query) use ($searchQuery) {
                    $query->where('name', 'like', "%{$searchQuery}%")
                          ->orWhere('description', 'like', "%{$searchQuery}%")
                          ->orWhere('detailed_description', 'like', "%{$searchQuery}%")
                          ->orWhereJsonContains('tags', $searchQuery)
                          ->orWhereJsonContains('highlights', $searchQuery)
                          ->orWhereJsonContains('destinations', $searchQuery);
                })
                ->orderByRaw("CASE 
                    WHEN name LIKE ? THEN 1
                    WHEN description LIKE ? THEN 2
                    ELSE 3
                END", ["%{$searchQuery}%", "%{$searchQuery}%"])
                ->orderBy('average_rating', 'desc')
                ->paginate($perPage);

            $transformedPackages = $packages->through(function ($package) {
                return $this->transformPackageForApi($package);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'packages' => $transformedPackages->items(),
                    'pagination' => [
                        'current_page' => $packages->currentPage(),
                        'last_page' => $packages->lastPage(),
                        'per_page' => $packages->perPage(),
                        'total' => $packages->total(),
                        'has_more_pages' => $packages->hasMorePages(),
                    ],
                    'query' => $searchQuery
                ],
                'message' => 'Search completed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error searching packages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error searching packages',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get featured packages
     */
    public function featured(): JsonResponse
    {
        try {
            // Cache featured packages for 30 minutes
            $packages = Cache::remember('featured_packages', 30 * 60, function () {
                return Package::with(['creator:id,name', 'hotels:id,name,star_rating'])
                    ->where('status', Package::STATUS_ACTIVE)
                    ->where('approval_status', Package::APPROVAL_APPROVED)
                    ->where('is_featured', true)
                    ->orderBy('average_rating', 'desc')
                    ->orderBy('bookings_count', 'desc')
                    ->limit(10)
                    ->get();
            });

            $transformedPackages = $packages->map(function ($package) {
                return $this->transformPackageForApi($package);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedPackages,
                'message' => 'Featured packages retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching featured packages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching featured packages',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get package categories and types
     */
    public function categories(): JsonResponse
    {
        try {
            // Cache categories for 2 hours
            $data = Cache::remember('package_categories', 2 * 60 * 60, function () {
                return [
                    'types' => collect(Package::getPackageTypes())->map(function ($label, $key) {
                        return [
                            'key' => $key,
                            'label' => $label,
                            'count' => Package::where('status', Package::STATUS_ACTIVE)
                                            ->where('approval_status', Package::APPROVAL_APPROVED)
                                            ->where('type', $key)
                                            ->count()
                        ];
                    })->values(),
                    'destinations' => $this->getPopularDestinations(),
                    'price_ranges' => $this->getPriceRanges(),
                    'durations' => $this->getPopularDurations(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Categories retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching categories: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get single package details
     */
    public function show(Request $request, $package): JsonResponse
    {
        try {
            $packageModel = Package::with([
                'creator:id,name,email',
                'hotels:id,name,star_rating,address,city,country,description',
                'flights:id,airline,flight_number,departure_airport,arrival_airport,departure_datetime,arrival_datetime',
                'transportServices:id,service_name,transport_type,max_passengers',
                'packageActivities' => function ($query) {
                    $query->orderBy('day_number')->orderBy('display_order');
                }
            ])
            ->where('status', Package::STATUS_ACTIVE)
            ->where('approval_status', Package::APPROVAL_APPROVED);

            // Find by ID or slug
            if (is_numeric($package)) {
                $packageModel = $packageModel->findOrFail($package);
            } else {
                $packageModel = $packageModel->where('slug', $package)->firstOrFail();
            }

            // Increment views count
            $packageModel->increment('views_count');

            return response()->json([
                'success' => true,
                'data' => $this->transformPackageForApiDetailed($packageModel),
                'message' => 'Package details retrieved successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found',
                'data' => null
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching package details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching package details',
                'data' => null
            ], 500);
        }
    }

    /**
     * Transform package for API response (list view)
     */
    private function transformPackageForApi(Package $package): array
    {
        return [
            'id' => $package->id,
            'slug' => $package->slug,
            'name' => $package->name,
            'description' => $package->short_description ?? $package->description,
            'type' => $package->type,
            'type_label' => Package::getPackageTypes()[$package->type] ?? $package->type,
            'duration' => $package->duration,
            'duration_formatted' => $package->formatted_duration,
            'base_price' => (float) $package->base_price,
            'total_price' => $package->total_price ? (float) $package->total_price : null,
            'currency' => $package->currency,
            'destinations' => $package->destinations ?? [],
            'features' => $package->features ?? [],
            'highlights' => array_slice($package->highlights ?? [], 0, 3),
            'images' => $this->transformImages(is_array($package->images) ? $package->images : []),
            'rating' => [
                'average' => (float) ($package->average_rating ?? 0),
                'count' => (int) ($package->reviews_count ?? 0),
            ],
            'bookings_count' => (int) ($package->bookings_count ?? 0),
            'is_featured' => $package->is_featured,
            'is_premium' => $package->is_premium,
            'instant_booking' => $package->instant_booking,
            'free_cancellation' => $package->free_cancellation,
            'creator' => [
                'id' => $package->creator?->id,
                'name' => $package->creator?->name,
            ],
            'created_at' => $package->created_at?->toISOString(),
            'availability' => [
                'available_from' => $package->available_from?->toDateString(),
                'available_until' => $package->available_until?->toDateString(),
                'min_participants' => $package->min_participants ? (int) $package->min_participants : null,
                'max_participants' => $package->max_participants ? (int) $package->max_participants : null,
                'current_bookings' => (int) ($package->current_bookings ?? 0),
                'availability_percentage' => $package->getAvailabilityPercentage(),
            ]
        ];
    }

    /**
     * Transform package for detailed API response
     */
    private function transformPackageForApiDetailed(Package $package): array
    {
        $basic = $this->transformPackageForApi($package);
        
        return array_merge($basic, [
            'detailed_description' => $package->detailed_description,
            'inclusions' => is_array($package->inclusions) ? $package->inclusions : [],
            'exclusions' => is_array($package->exclusions) ? $package->exclusions : [],
            'activities' => $package->packageActivities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'day_number' => $activity->day_number,
                    'activity_name' => $activity->activity_name,
                    'description' => $activity->description,
                    'start_time' => $activity->start_time,
                    'end_time' => $activity->end_time,
                    'location' => $activity->location,
                    'category' => $activity->category,
                    'is_included' => $activity->is_included,
                    'additional_cost' => $activity->additional_cost ? (float) $activity->additional_cost : null,
                    'is_optional' => $activity->is_optional,
                ];
            }),
            'hotels' => $package->hotels->map(function ($hotel) {
                return [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'rating' => $hotel->star_rating,
                    'location' => trim(implode(', ', array_filter([$hotel->address, $hotel->city, $hotel->country]))),
                    'address' => $hotel->address,
                    'city' => $hotel->city,
                    'country' => $hotel->country,
                    'description' => $hotel->description,
                    'pivot' => [
                        'is_primary' => (bool) ($hotel->pivot->is_primary ?? false),
                        'nights' => (int) ($hotel->pivot->nights ?? 1),
                        'room_type' => $hotel->pivot->room_type ?? '',
                        'rooms_needed' => (int) ($hotel->pivot->rooms_needed ?? 1),
                    ]
                ];
            }),
            'flights' => $package->flights->map(function ($flight) {
                return [
                    'id' => $flight->id,
                    'airline' => $flight->airline,
                    'flight_number' => $flight->flight_number,
                    'departure_airport' => $flight->departure_airport,
                    'arrival_airport' => $flight->arrival_airport,
                    'departure_time' => $flight->departure_datetime,
                    'arrival_time' => $flight->arrival_datetime,
                    'pivot' => [
                        'flight_type' => $flight->pivot->flight_type ?? '',
                        'seats_allocated' => (int) ($flight->pivot->seats_allocated ?? 1),
                    ]
                ];
            }),
            'transport' => $package->transportServices->map(function ($transport) {
                return [
                    'id' => $transport->id,
                    'company_name' => $transport->service_name,
                    'vehicle_type' => $transport->transport_type,
                    'capacity' => $transport->max_passengers,
                    'pivot' => [
                        'pickup_location' => $transport->pivot->pickup_location ?? '',
                        'dropoff_location' => $transport->pivot->dropoff_location ?? '',
                        'day_of_itinerary' => (int) ($transport->pivot->day_of_itinerary ?? 1),
                    ]
                ];
            }),
            'pricing' => [
                'base_price' => (float) $package->base_price,
                'child_price' => $package->child_price ? (float) $package->child_price : null,
                'infant_price' => $package->infant_price ? (float) $package->infant_price : null,
                'single_supplement' => $package->single_supplement ? (float) $package->single_supplement : null,
                'total_price' => $package->total_price ? (float) $package->total_price : null,
                'pricing_breakdown' => is_array($package->pricing_breakdown) ? $package->pricing_breakdown : [],
                'optional_addons' => is_array($package->optional_addons) ? $package->optional_addons : [],
                'payment_terms' => is_array($package->payment_terms) ? (object) $package->payment_terms : (object) [],
                'cancellation_policy' => is_array($package->cancellation_policy) ? (object) $package->cancellation_policy : (object) [],
                'deposit_percentage' => $package->deposit_percentage ? (float) $package->deposit_percentage : null,
                'currency' => $package->currency,
            ],
            'policies' => [
                'required_documents' => is_array($package->required_documents) ? $package->required_documents : [],
                'visa_requirements' => is_array($package->visa_requirements) ? $package->visa_requirements : [],
                'health_requirements' => is_array($package->health_requirements) ? $package->health_requirements : [],
                'age_restrictions' => is_array($package->age_restrictions) ? (object) $package->age_restrictions : (object) [],
                'terms_accepted' => is_array($package->terms_accepted) ? (object) $package->terms_accepted : (object) [],
            ]
        ]);
    }

    /**
     * Transform images for API response
     */
    private function transformImages(array $images): array
    {
        return array_map(function ($image) {
            $urls = [];
            if (isset($image['sizes']) && is_array($image['sizes'])) {
                foreach ($image['sizes'] as $size => $path) {
                    $urls[$size] = asset('storage/' . $path);
                }
            }
            
            return [
                'id' => $image['id'] ?? null,
                'filename' => $image['filename'] ?? null,
                'original_name' => $image['original_name'] ?? null,
                'alt_text' => $image['alt_text'] ?? '',
                'is_main' => $image['is_main'] ?? false,
                'urls' => $urls
            ];
        }, $images);
    }

    /**
     * Get popular destinations
     */
    private function getPopularDestinations(): array
    {
        return DB::table('packages')
            ->where('status', Package::STATUS_ACTIVE)
            ->where('approval_status', Package::APPROVAL_APPROVED)
            ->whereNotNull('destinations')
            ->get()
            ->pluck('destinations')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->map(function ($count, $destination) {
                return [
                    'name' => $destination,
                    'count' => $count
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get price ranges
     */
    private function getPriceRanges(): array
    {
        $ranges = [
            ['min' => 0, 'max' => 500, 'label' => 'Under $500'],
            ['min' => 500, 'max' => 1000, 'label' => '$500 - $1,000'],
            ['min' => 1000, 'max' => 2000, 'label' => '$1,000 - $2,000'],
            ['min' => 2000, 'max' => 5000, 'label' => '$2,000 - $5,000'],
            ['min' => 5000, 'max' => null, 'label' => 'Over $5,000'],
        ];

        foreach ($ranges as &$range) {
            $query = Package::where('status', Package::STATUS_ACTIVE)
                          ->where('approval_status', Package::APPROVAL_APPROVED)
                          ->where('base_price', '>=', $range['min']);
            
            if ($range['max']) {
                $query->where('base_price', '<=', $range['max']);
            }
            
            $range['count'] = $query->count();
        }

        return $ranges;
    }

    /**
     * Get popular durations
     */
    private function getPopularDurations(): array
    {
        return Package::where('status', Package::STATUS_ACTIVE)
            ->where('approval_status', Package::APPROVAL_APPROVED)
            ->select('duration', DB::raw('count(*) as count'))
            ->groupBy('duration')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'duration' => $item->duration,
                    'label' => $item->duration . ' day' . ($item->duration > 1 ? 's' : ''),
                    'count' => $item->count
                ];
            })
            ->toArray();
    }
}