<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class B2CPackageService
{
    /**
     * Get paginated packages for the B2C platform
     */
    public function getPaginatedPackages(array $filters = [], int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?: config('b2c.pagination.packages_per_page', 12);
        
        $query = $this->buildPackageQuery($filters);
        
        $packages = $query->paginate($perPage);
        
        // Transform packages for B2C display
        $packages->getCollection()->transform(function ($package) {
            return $this->transformPackageForB2C($package);
        });
        
        return $packages;
    }

    /**
     * Get featured packages for homepage
     */
    public function getFeaturedPackages(int $limit = 4): Collection
    {
        $cacheKey = 'b2c_featured_packages_' . $limit;
        $cacheTtl = config('b2c.cache.ttl.featured_packages', 7200);
        
        if (!config('b2c.cache.enabled', true)) {
            return $this->fetchFeaturedPackages($limit);
        }
        
        return Cache::remember($cacheKey, $cacheTtl, function () use ($limit) {
            return $this->fetchFeaturedPackages($limit);
        });
    }

    /**
     * Search packages
     */
    public function searchPackages(string $query, array $filters = [], int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?: config('b2c.pagination.search_results_per_page', 15);
        
        $packageQuery = $this->buildPackageQuery($filters);
        $packageQuery = $this->applySearchQuery($packageQuery, $query);
        
        $packages = $packageQuery->paginate($perPage);
        
        // Transform packages for B2C display
        $packages->getCollection()->transform(function ($package) {
            return $this->transformPackageForB2C($package);
        });
        
        return $packages;
    }

    /**
     * Get package details by ID or slug
     */
    public function getPackageDetails(mixed $identifier): ?object
    {
        $cacheKey = 'b2c_package_details_' . $identifier;
        $cacheTtl = config('b2c.cache.ttl.package_details', 1800);
        
        if (!config('b2c.cache.enabled', true)) {
            return $this->fetchPackageDetails($identifier);
        }
        
        return Cache::remember($cacheKey, $cacheTtl, function () use ($identifier) {
            return $this->fetchPackageDetails($identifier);
        });
    }

    /**
     * Get related packages
     */
    public function getRelatedPackages(Package $package, int $limit = 4): Collection
    {
        // Find packages with similar characteristics
        $query = $this->buildBaseQuery()
            ->where('id', '!=', $package->id)
            ->where(function ($q) use ($package) {
                // Similar type
                if ($package->type) {
                    $q->orWhere('type', $package->type);
                }
                
                // Similar duration (±3 days)
                if ($package->duration) {
                    $q->orWhereBetween('duration', [
                        max(1, $package->duration - 3),
                        $package->duration + 3
                    ]);
                }
                
                // Similar price range (±20%)
                if ($package->base_price) {
                    $priceMin = $package->base_price * 0.8;
                    $priceMax = $package->base_price * 1.2;
                    $q->orWhereBetween('base_price', [$priceMin, $priceMax]);
                }
                
                // Similar destinations
                if ($package->destinations) {
                    foreach ($package->destinations as $destination) {
                        $q->orWhereJsonContains('destinations', $destination);
                    }
                }
            })
            ->inRandomOrder()
            ->limit($limit);

        return $query->get()->map(function ($pkg) {
            return $this->transformPackageForB2C($pkg);
        });
    }

    /**
     * Get filter options for packages
     */
    public function getFilterOptions(): array
    {
        $cacheKey = 'b2c_filter_options';
        $cacheTtl = config('b2c.cache.ttl.search_filters', 86400);
        
        if (!config('b2c.cache.enabled', true)) {
            return $this->buildFilterOptions();
        }
        
        return Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->buildFilterOptions();
        });
    }

    /**
     * Get package statistics for homepage
     */
    public function getStatistics(): array
    {
        $cacheKey = 'b2c_package_statistics';
        $cacheTtl = config('b2c.cache.ttl.statistics', 14400);
        
        if (!config('b2c.cache.enabled', true)) {
            return $this->calculateStatistics();
        }
        
        return Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->calculateStatistics();
        });
    }

    /**
     * Build the base package query with common filters
     */
    protected function buildPackageQuery(array $filters = []): Builder
    {
        $query = $this->buildBaseQuery();
        
        // Apply filters
        if (!empty($filters['departure'])) {
            $this->applyDepartureCityFilter($query, $filters['departure']);
        }
        
        // Handle both 'budget' (from home page) and 'price_range' (from filters)
        if (!empty($filters['budget'])) {
            $this->applyBudgetFilter($query, $filters['budget']);
        }
        
        if (!empty($filters['price_range'])) {
            $this->applyPriceFilter($query, $filters['price_range']);
        }
        
        if (!empty($filters['duration'])) {
            $this->applyDurationFilter($query, $filters['duration']);
        }
        
        // Handle singular destination (from filter form)
        if (!empty($filters['destination'])) {
            $this->applyDestinationFilter($query, [$filters['destination']]);
        }
        
        // Handle plural destinations (from checkboxes if any)
        if (!empty($filters['destinations'])) {
            $this->applyDestinationFilter($query, $filters['destinations']);
        }
        
        if (!empty($filters['type'])) {
            $this->applyTypeFilter($query, $filters['type']);
        }
        
        if (!empty($filters['rating'])) {
            $this->applyRatingFilter($query, $filters['rating']);
        }
        
        if (!empty($filters['features'])) {
            $this->applyFeatureFilter($query, $filters['features']);
        }
        
        // Apply sorting
        $this->applySorting($query, $filters['sort'] ?? 'featured');
        
        return $query;
    }

    /**
     * Build base query with eager loading and basic conditions
     */
    protected function buildBaseQuery(): Builder
    {
        $eagerLoadRelations = config('b2c.performance.eager_load_relations', []);
        
        return Package::query()
            ->select([
                'id', 'name', 'slug', 'description', 'short_description', 'detailed_description', 'type',
                'duration', 'base_price', 'child_price', 'currency', 'destinations',
                'images', 'highlights', 'tags', 'is_featured', 'is_premium',
                'average_rating', 'reviews_count', 'bookings_count', 'views_count',
                'status', 'approval_status', 'created_at', 'updated_at', 'creator_id',
                'includes_meals', 'includes_accommodation', 'includes_transport',
                'includes_guide', 'includes_flights', 'includes_activities',
                'free_cancellation', 'instant_confirmation', 'instant_booking',
                'min_participants', 'max_participants', 'current_bookings',
                'inclusions', 'exclusions', 'features', 'activities',
                'cancellation_policy', 'payment_terms', 'required_documents',
                'visa_requirements', 'health_requirements', 'available_from', 'available_until'
            ])
            ->with($eagerLoadRelations)
            ->active()
            ->approved()
            ->where(function ($query) {
                $query->whereNull('available_from')
                      ->orWhere('available_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('available_until')
                      ->orWhere('available_until', '>=', now());
            });
    }

    /**
     * Apply search query to package builder
     */
    protected function applySearchQuery(Builder $query, string $searchQuery): Builder
    {
        $searchFields = config('b2c.search.search_fields', ['name', 'description']);
        $minLength = config('b2c.search.min_query_length', 2);
        
        if (strlen(trim($searchQuery)) < $minLength) {
            return $query;
        }
        
        $searchTerms = explode(' ', trim($searchQuery));
        
        return $query->where(function ($q) use ($searchTerms, $searchFields) {
            foreach ($searchTerms as $term) {
                if (strlen($term) >= 2) {
                    $q->where(function ($subQuery) use ($term, $searchFields) {
                        foreach ($searchFields as $field) {
                            if (in_array($field, ['destinations', 'highlights', 'tags'])) {
                                // JSON fields
                                $subQuery->orWhereJsonContains($field, $term);
                            } else {
                                // Regular string fields
                                $subQuery->orWhere($field, 'like', "%{$term}%");
                            }
                        }
                    });
                }
            }
        });
    }

    /**
     * Apply departure city filter
     */
    protected function applyDepartureCityFilter(Builder $query, string $city): void
    {
        $query->whereJsonContains('departure_cities', $city);
    }
    
    /**
     * Apply budget filter (single value from home page)
     */
    protected function applyBudgetFilter(Builder $query, string $budget): void
    {
        if ($budget === '5000+') {
            $query->where('base_price', '>=', 5000);
        } else if (strpos($budget, '-') !== false) {
            [$min, $max] = explode('-', $budget);
            $query->whereBetween('base_price', [(int)$min, (int)$max]);
        }
    }
    
    /**
     * Apply price range filter (array from filter sidebar)
     */
    protected function applyPriceFilter(Builder $query, array $priceRanges): void
    {
        $query->where(function ($q) use ($priceRanges) {
            foreach ($priceRanges as $range) {
                if ($range === '10000+') {
                    $q->orWhere('base_price', '>=', 10000);
                } else {
                    [$min, $max] = explode('-', $range);
                    $q->orWhereBetween('base_price', [(int)$min, (int)$max]);
                }
            }
        });
    }

    /**
     * Apply duration filter
     */
    protected function applyDurationFilter(Builder $query, $durations): void
    {
        // Handle both single value and array
        if (!is_array($durations)) {
            $durations = [$durations];
        }
        
        $query->where(function ($q) use ($durations) {
            foreach ($durations as $range) {
                // Handle empty string
                if (empty($range)) {
                    continue;
                }
                
                // Handle single numeric value (exact duration)
                if (is_numeric($range)) {
                    $q->orWhere('duration', '=', (int)$range);
                    continue;
                }
                
                // Handle range strings
                if ($range === '30+' || $range === '30') {
                    $q->orWhere('duration', '>=', 30);
                } else if (strpos($range, '-') !== false) {
                    [$min, $max] = explode('-', $range);
                    $q->orWhereBetween('duration', [(int)$min, (int)$max]);
                }
            }
        });
    }

    /**
     * Apply destination filter
     */
    protected function applyDestinationFilter(Builder $query, array $destinations): void
    {
        $query->where(function ($q) use ($destinations) {
            foreach ($destinations as $destination) {
                $q->orWhereJsonContains('destinations', $destination);
            }
        });
    }

    /**
     * Apply package type filter
     */
    protected function applyTypeFilter(Builder $query, array $types): void
    {
        $query->whereIn('type', $types);
    }

    /**
     * Apply rating filter
     */
    protected function applyRatingFilter(Builder $query, float $minRating): void
    {
        $query->where('average_rating', '>=', $minRating);
    }

    /**
     * Apply feature filters
     */
    protected function applyFeatureFilter(Builder $query, array $features): void
    {
        foreach ($features as $feature) {
            switch ($feature) {
                case 'meals':
                    $query->where('includes_meals', true);
                    break;
                case 'accommodation':
                    $query->where('includes_accommodation', true);
                    break;
                case 'transport':
                    $query->where('includes_transport', true);
                    break;
                case 'guide':
                    $query->where('includes_guide', true);
                    break;
                case 'flights':
                    $query->where('includes_flights', true);
                    break;
                case 'activities':
                    $query->where('includes_activities', true);
                    break;
                case 'free_cancellation':
                    $query->where('free_cancellation', true);
                    break;
                case 'instant_booking':
                    $query->where('instant_booking', true);
                    break;
            }
        }
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting(Builder $query, string $sort): void
    {
        switch ($sort) {
            case 'price_low':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('base_price', 'desc');
                break;
            case 'rating':
                $query->orderByDesc('average_rating')
                      ->orderByDesc('reviews_count');
                break;
            case 'duration':
                $query->orderBy('duration', 'asc');
                break;
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            case 'popular':
                $query->orderByDesc('bookings_count')
                      ->orderByDesc('views_count');
                break;
            case 'featured':
            default:
                $query->orderByDesc('is_featured')
                      ->orderByDesc('is_premium')
                      ->orderByDesc('average_rating')
                      ->orderByDesc('created_at');
                break;
        }
    }

    /**
     * Fetch featured packages from database
     * 
     * Uses the new Featured Products module to get approved and active featured packages
     */
    protected function fetchFeaturedPackages(int $limit): Collection
    {
        $packages = $this->buildBaseQuery()
            ->featured() // Use the featured scope from Package model
            ->orderByDesc('featured_at') // Show most recently featured first
            ->orderByDesc('average_rating')
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();

        return $packages->map(function ($package) {
            return $this->transformPackageForB2C($package);
        });
    }

    /**
     * Fetch package details from database
     */
    protected function fetchPackageDetails(mixed $identifier): ?object
    {
        $query = $this->buildBaseQuery()
            ->with([
                'creator:id,name,email',
                'packageActivities' => function ($q) {
                    $q->orderBy('day_number')->orderBy('display_order');
                },
                'hotels' => function ($q) {
                    $q->orderByPivot('display_order');
                },
                'flights',
                'transportServices'
            ]);

        // Try to find by slug first, then by ID
        $package = is_numeric($identifier) 
            ? $query->find($identifier)
            : $query->where('slug', $identifier)->first();

        if (!$package) {
            return null;
        }

        // Increment view count
        $package->increment('views_count');

        return $this->transformPackageDetailsForB2C($package);
    }

    /**
     * Build filter options from database
     */
    protected function buildFilterOptions(): array
    {
        $baseQuery = Package::active()->approved();
        
        return [
            'price_ranges' => config('b2c.filters.price_ranges'),
            'durations' => config('b2c.filters.durations'),
            'ratings' => config('b2c.filters.ratings'),
            'destinations' => $this->getAvailableDestinations($baseQuery),
            'types' => $this->getAvailableTypes($baseQuery),
            'features' => $this->getAvailableFeatures(),
        ];
    }

    /**
     * Get available destinations from packages
     */
    protected function getAvailableDestinations(Builder $baseQuery): array
    {
        $destinations = $baseQuery
            ->whereNotNull('destinations')
            ->pluck('destinations')
            ->flatten()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return array_intersect($destinations, config('b2c.filters.popular_destinations', []));
    }

    /**
     * Get available package types
     */
    protected function getAvailableTypes(Builder $baseQuery): array
    {
        // Return all package types from Package model
        return Package::getPackageTypes();
    }

    /**
     * Get available features
     */
    protected function getAvailableFeatures(): array
    {
        return [
            'meals' => 'Meals Included',
            'accommodation' => 'Accommodation',
            'transport' => 'Transportation',
            'guide' => 'Guide Included',
            'flights' => 'Flights Included',
            'activities' => 'Activities Included',
            'free_cancellation' => 'Free Cancellation',
            'instant_booking' => 'Instant Booking',
        ];
    }

    /**
     * Calculate statistics
     */
    protected function calculateStatistics(): array
    {
        $baseQuery = Package::active()->approved();
        
        return [
            'total_packages' => $baseQuery->count(),
            'featured_packages' => $baseQuery->where('is_featured', true)->count(),
            'average_rating' => $baseQuery->avg('average_rating') ?: 0,
            'total_bookings' => $baseQuery->sum('bookings_count'),
            'destinations_count' => $baseQuery
                ->whereNotNull('destinations')
                ->pluck('destinations')
                ->flatten()
                ->unique()
                ->count(),
        ];
    }

    /**
     * Transform package for B2C display
     */
    protected function transformPackageForB2C(Package $package): object
    {
        return (object) [
            'id' => $package->id,
            'name' => $package->name,
            'slug' => $package->slug,
            'description' => $package->description,
            'short_description' => $package->short_description,
            'type' => $package->type,
            'type_label' => Package::getPackageTypes()[$package->type] ?? ucfirst($package->type),
            'duration' => $package->duration,
            'formatted_duration' => $package->formatted_duration,
            'base_price' => $package->base_price,
            'child_price' => $package->child_price,
            'currency' => $package->currency ?: 'USD',
            'destinations' => $package->destinations ?: [],
            'destinations_text' => is_array($package->destinations) 
                ? implode(' & ', $package->destinations) 
                : ($package->destinations ?: 'Multiple Destinations'),
            'highlights' => $package->highlights ?: [],
            'tags' => $package->tags ?: [],
            'is_featured' => $package->is_featured,
            'is_premium' => $package->is_premium,
            'average_rating' => $package->average_rating ?: 0,
            'reviews_count' => $package->reviews_count ?: 0,
            'bookings_count' => $package->bookings_count ?: 0,
            'main_image' => $package->getMainImageUrl('medium'),
            'images' => $package->getImagesWithUrls(),
            'creator_name' => $package->creator?->name ?: 'SeferEt Partner',
            'includes' => $this->getPackageIncludes($package),
            'features' => $this->getPackageFeatures($package),
            'availability_percentage' => $package->getAvailabilityPercentage(),
            'available_spots' => $package->max_participants ? 
                ($package->max_participants - $package->current_bookings) : null,
            'url' => route('packages.details', $package->slug ?? $package->id),
            'formatted_price' => '$' . number_format($package->base_price),
            'free_cancellation' => $package->free_cancellation,
            'instant_booking' => $package->instant_booking,
            'instant_confirmation' => $package->instant_confirmation,
        ];
    }

    /**
     * Transform package details for B2C display
     */
    protected function transformPackageDetailsForB2C(Package $package): object
    {
        $basicData = $this->transformPackageForB2C($package);
        
        return (object) array_merge((array) $basicData, [
            'detailed_description' => $package->detailed_description,
            'inclusions' => $package->inclusions ?: [],
            'exclusions' => $package->exclusions ?: [],
            'activities' => $package->packageActivities->map(function ($activity) {
                return [
                    'day' => $activity->day_number,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'location' => $activity->location,
                    'duration' => $activity->duration_minutes,
                    'is_included' => $activity->is_included,
                    'additional_cost' => $activity->additional_cost,
                ];
            })->groupBy('day'),
            'hotels' => $package->hotels->map(function ($hotel) {
                return [
                    'name' => $hotel->name,
                    'location' => $hotel->location,
                    'stars' => $hotel->stars,
                    'nights' => $hotel->pivot->nights,
                    'room_type' => $hotel->pivot->room_type,
                    'meal_plan' => $hotel->pivot->meal_plans,
                    'is_primary' => $hotel->pivot->is_primary,
                ];
            }),
            'flights' => $package->flights->map(function ($flight) {
                return [
                    'type' => $flight->pivot->flight_type,
                    'from' => $flight->departure_city,
                    'to' => $flight->arrival_city,
                    'departure_time' => $flight->departure_time,
                    'arrival_time' => $flight->arrival_time,
                    'airline' => $flight->airline,
                    'class' => $flight->class,
                ];
            }),
            'transport' => $package->transportServices->map(function ($transport) {
                return [
                    'type' => $transport->vehicle_type,
                    'category' => $transport->pivot->transport_category,
                    'route' => $transport->pivot->route_details,
                    'day' => $transport->pivot->day_of_itinerary,
                ];
            }),
            'min_participants' => $package->min_participants,
            'max_participants' => $package->max_participants,
            'current_bookings' => $package->current_bookings,
            'cancellation_policy' => $package->cancellation_policy,
            'payment_terms' => $package->payment_terms,
            'required_documents' => $package->required_documents ?: [],
            'visa_requirements' => $package->visa_requirements ?: [],
            'health_requirements' => $package->health_requirements ?: [],
        ]);
    }
    /**
     * Get package includes list
     */
    protected function getPackageIncludes(Package $package): array
    {
        $includes = [];
        
        if (!empty($package->includes_flights)) $includes[] = 'Flights';
        if (!empty($package->includes_accommodation)) $includes[] = 'Accommodation';
        if (!empty($package->includes_meals)) $includes[] = 'Meals';
        if (!empty($package->includes_transport)) $includes[] = 'Transportation';
        if (!empty($package->includes_guide)) $includes[] = 'Guide';
        if (!empty($package->includes_activities)) $includes[] = 'Activities';
        
        return $includes;
    }

    /**
     * Get package features
     */
    protected function getPackageFeatures(Package $package): array
    {
        $features = [];
        
        if (!empty($package->free_cancellation)) $features[] = 'Free Cancellation';
        if (!empty($package->instant_booking)) $features[] = 'Instant Booking';
        if (!empty($package->instant_confirmation)) $features[] = 'Instant Confirmation';
        if (!empty($package->is_premium)) $features[] = 'Premium Package';
        
        return $features;
    }

    /**
     * Clear cache for packages
     */
    public function clearCache(): void
    {
        if (config('b2c.cache.enabled')) {
            try {
                // Try tagged cache clearing first (Redis, Memcached)
                $tags = config('b2c.cache.tags', []);
                foreach ($tags as $tag) {
                    Cache::tags($tag)->flush();
                }
            } catch (\Exception $e) {
                // Fall back to individual key clearing for file/database cache
                $this->clearIndividualCacheKeys();
            }
        }
    }
    
    /**
     * Clear individual cache keys when tagging is not supported
     */
    protected function clearIndividualCacheKeys(): void
    {
        $cacheKeys = [
            'b2c_featured_packages_4',
            'b2c_filter_options', 
            'b2c_package_statistics',
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
        // Clear package details cache for IDs 1-100
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget('b2c_package_details_' . $i);
        }
        
        // Clear slug-based cache keys (common patterns)
        $slugPatterns = ['luxury-umrah', 'family-package', 'budget-umrah', 'premium-package'];
        foreach ($slugPatterns as $slug) {
            Cache::forget('b2c_package_details_' . $slug);
        }
    }
}