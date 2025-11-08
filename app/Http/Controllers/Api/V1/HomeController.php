<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Get featured products (flights, hotels, packages) with local-first priority
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function featuredProducts(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = min($request->input('per_page', 10), 50);

            // Cache for 10 minutes
            $cacheKey = "featured_products_p{$page}_pp{$perPage}";
            
            $products = Cache::remember($cacheKey, 600, function () use ($perPage) {
                $products = [];

                // 1. Get featured packages (local) - top priority
                $packages = DB::table('packages')
                    ->where('is_featured', true)
                    ->select(
                        'id',
                        'name as title',
                        'description as short_desc',
                        'base_price as price',
                        'currency',
                        DB::raw("'package' as type"),
                        DB::raw("'local' as source"),
                        DB::raw('NULL as image_url'),
                        DB::raw('NULL as location'),
                        DB::raw('NULL as rating'),
                        DB::raw('NULL as review_count'),
                        DB::raw("'Featured' as badge")
                    )
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                // Get main image for each package
                foreach ($packages as $package) {
                    // TODO: Add package_images table
                    // $image = DB::table('package_images')
                    //     ->where('package_id', $package->id)
                    //     ->where('is_main', true)
                    //     ->first();
                    // 
                    // if ($image && isset($image->urls)) {
                    //     $urls = json_decode($image->urls, true);
                    //     $package->image_url = $urls['medium'] ?? $urls['original'] ?? null;
                    // }

                    // TODO: Add package_destinations and package_ratings tables
                    // $destinations = DB::table('package_destinations')
                    //     ->where('package_id', $package->id)
                    //     ->pluck('destination_name')
                    //     ->toArray();
                    // $package->location = implode(', ', array_slice($destinations, 0, 2));
                    // 
                    // $ratingData = DB::table('package_ratings')
                    //     ->where('package_id', $package->id)
                    //     ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
                    //     ->first();
                    // 
                    // if ($ratingData && $ratingData->count > 0) {
                    //     $package->rating = round($ratingData->avg_rating, 1);
                    //     $package->review_count = $ratingData->count;
                    // }
                }

                $products = array_merge($products, $packages->toArray());

                // 2. Get featured hotels (if you have local hotels table)
                // For now, we'll skip this as it seems hotels come from Amadeus

                // 3. Get popular packages to fill up to requested limit
                if (count($products) < $perPage) {
                    $remaining = $perPage - count($products);
                    $popularPackages = DB::table('packages')
                        ->join('package_bookings', 'packages.id', '=', 'package_bookings.package_id')
                        ->select(
                            'packages.id',
                            'packages.name as title',
                            'packages.description as short_desc',
                            'packages.base_price as price',
                            'packages.currency',
                            DB::raw("'package' as type"),
                            DB::raw("'local' as source"),
                            DB::raw('NULL as image_url'),
                            DB::raw('NULL as location'),
                            DB::raw('NULL as rating'),
                            DB::raw('NULL as review_count'),
                            DB::raw("'Popular' as badge"),
                            DB::raw('COUNT(package_bookings.id) as booking_count')
                        )
                        ->whereNotIn('packages.id', collect($products)->pluck('id')->toArray())
                        ->groupBy('packages.id', 'packages.name', 'packages.description', 
                                 'packages.base_price', 'packages.currency')
                        ->orderByDesc('booking_count')
                        ->limit($remaining)
                        ->get();

                    // Get images and locations for popular packages
                    foreach ($popularPackages as $package) {
                        // TODO: Add package_images table
                        // $image = DB::table('package_images')
                        //     ->where('package_id', $package->id)
                        //     ->where('is_main', true)
                        //     ->first();
                        // 
                        // if ($image && isset($image->urls)) {
                        //     $urls = json_decode($image->urls, true);
                        //     $package->image_url = $urls['medium'] ?? $urls['original'] ?? null;
                        // }

                        // TODO: Add tables
                        // $destinations = DB::table('package_destinations')
                        //     ->where('package_id', $package->id)
                        //     ->pluck('destination_name')
                        //     ->toArray();
                        // $package->location = implode(', ', array_slice($destinations, 0, 2));
                        // 
                        // $ratingData = DB::table('package_ratings')
                        //     ->where('package_id', $package->id)
                        //     ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
                        //     ->first();
                        // 
                        // if ($ratingData && $ratingData->count > 0) {
                        //     $package->rating = round($ratingData->avg_rating, 1);
                        //     $package->review_count = $ratingData->count;
                        // }
                        
                        unset($package->booking_count);
                    }

                    $products = array_merge($products, $popularPackages->toArray());
                }

                return $products;
            });

            return response()->json([
                'success' => true,
                'message' => 'Featured products retrieved successfully',
                'data' => $products,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch featured products: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured products',
            ], 500);
        }
    }

    /**
     * Get personalized recommendations for user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommendations(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $limit = min($request->input('limit', 10), 50);

            // For authenticated users with booking history
            if ($userId) {
                $recommendations = $this->getPersonalizedRecommendations($userId, $limit);
            } else {
                // For guests, show trending/popular items
                $recommendations = $this->getTrendingRecommendations($limit);
            }

            return response()->json([
                'success' => true,
                'message' => 'Recommendations retrieved successfully',
                'data' => $recommendations,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch recommendations: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recommendations',
            ], 500);
        }
    }

    /**
     * Get popular products by location
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function popular(Request $request)
    {
        try {
            $location = $request->input('location');
            $limit = min($request->input('limit', 10), 50);

            $cacheKey = "popular_products_loc{$location}_lim{$limit}";
            
            $products = Cache::remember($cacheKey, 600, function () use ($location, $limit) {
                // Get packages by location (based on destinations)
                $query = DB::table('packages')
                    ->join('package_bookings', 'packages.id', '=', 'package_bookings.package_id')
                    ->select(
                        'packages.id',
                        'packages.name as title',
                        'packages.description as short_desc',
                        'packages.base_price as price',
                        'packages.currency',
                        DB::raw("'package' as type"),
                        DB::raw("'local' as source"),
                        DB::raw('NULL as image_url'),
                        DB::raw('NULL as location'),
                        DB::raw('NULL as rating'),
                        DB::raw('NULL as review_count'),
                        DB::raw("'Popular' as badge"),
                        DB::raw('COUNT(package_bookings.id) as booking_count')
                    )
                    ->groupBy('packages.id', 'packages.name', 'packages.description',
                             'packages.base_price', 'packages.currency')
                    ->orderByDesc('booking_count');

                // Filter by location if provided
                if ($location) {
                    $query->join('package_destinations', 'packages.id', '=', 'package_destinations.package_id')
                        ->where(function($q) use ($location) {
                            $q->where('package_destinations.destination_name', 'LIKE', "%{$location}%")
                              ->orWhere('package_destinations.city_code', 'LIKE', "%{$location}%");
                        });
                }

                $products = $query->limit($limit)->get();

                // Enhance with images and location data
                foreach ($products as $product) {
                    // TODO: Add package_images table
                    // $image = DB::table('package_images')
                    //     ->where('package_id', $product->id)
                    //     ->where('is_main', true)
                    //     ->first();
                    // 
                    // if ($image && isset($image->urls)) {
                    //     $urls = json_decode($image->urls, true);
                    //     $product->image_url = $urls['medium'] ?? $urls['original'] ?? null;
                    // }

                    $destinations = DB::table('package_destinations')
                        ->where('package_id', $product->id)
                        ->pluck('destination_name')
                        ->toArray();
                    $product->location = implode(', ', array_slice($destinations, 0, 2));
                    
                    $ratingData = DB::table('package_ratings')
                        ->where('package_id', $product->id)
                        ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
                        ->first();
                    
                    if ($ratingData && $ratingData->count > 0) {
                        $product->rating = round($ratingData->avg_rating, 1);
                        $product->review_count = $ratingData->count;
                    }
                    
                    unset($product->booking_count);
                }

                return $products->toArray();
            });

            return response()->json([
                'success' => true,
                'message' => 'Popular products retrieved successfully',
                'data' => $products,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch popular products: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular products',
            ], 500);
        }
    }

    /**
     * Get personalized recommendations based on user's booking history
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    private function getPersonalizedRecommendations($userId, $limit)
    {
        // Get user's past bookings to understand preferences
        $userBookings = DB::table('package_bookings')
            ->where('customer_id', $userId)
            ->where('status', 'confirmed')
            ->pluck('package_id')
            ->toArray();

        if (empty($userBookings)) {
            // No history, return trending
            return $this->getTrendingRecommendations($limit);
        }

        // Get package types and destinations from past bookings
        $preferences = DB::table('packages')
            ->join('package_destinations', 'packages.id', '=', 'package_destinations.package_id')
            ->whereIn('packages.id', $userBookings)
            ->select('packages.type', 'package_destinations.destination_name')
            ->get();

        $preferredTypes = $preferences->pluck('type')->unique()->toArray();
        $preferredDestinations = $preferences->pluck('destination_name')->unique()->toArray();

        // Find similar packages (same type or destination) that user hasn't booked
        $recommendations = DB::table('packages')
            ->leftJoin('package_destinations', 'packages.id', '=', 'package_destinations.package_id')
            ->select(
                'packages.id',
                'packages.name as title',
                'packages.description as short_desc',
                'packages.base_price as price',
                'packages.currency',
                DB::raw("'package' as type"),
                DB::raw("'local' as source"),
                DB::raw('NULL as image_url'),
                DB::raw('NULL as location'),
                DB::raw('NULL as rating'),
                DB::raw('NULL as review_count'),
                DB::raw("'Recommended for you' as reason_tag")
            )
            ->whereNotIn('packages.id', $userBookings)
            ->where(function($query) use ($preferredTypes, $preferredDestinations) {
                $query->whereIn('packages.type', $preferredTypes)
                      ->orWhereIn('package_destinations.destination_name', $preferredDestinations);
            })
            ->groupBy('packages.id', 'packages.name', 'packages.description',
                     'packages.base_price', 'packages.currency')
            ->limit($limit)
            ->get();

        // Enhance with images and location data
        foreach ($recommendations as $rec) {
            // TODO: Add package_images table
            // $image = DB::table('package_images')
            //     ->where('package_id', $rec->id)
            //     ->where('is_main', true)
            //     ->first();
            // 
            // if ($image && isset($image->urls)) {
            //     $urls = json_decode($image->urls, true);
            //     $rec->image_url = $urls['medium'] ?? $urls['original'] ?? null;
            // }

            $destinations = DB::table('package_destinations')
                ->where('package_id', $rec->id)
                ->pluck('destination_name')
                ->toArray();
            $rec->location = implode(', ', array_slice($destinations, 0, 2));
            
            $ratingData = DB::table('package_ratings')
                ->where('package_id', $rec->id)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
                ->first();
            
            if ($ratingData && $ratingData->count > 0) {
                $rec->rating = round($ratingData->avg_rating, 1);
                $rec->review_count = $ratingData->count;
            }
        }

        return $recommendations->toArray();
    }

    /**
     * Get trending recommendations for guests
     *
     * @param int $limit
     * @return array
     */
    private function getTrendingRecommendations($limit)
    {
        // Get most booked packages in last 30 days
        $recommendations = DB::table('packages')
            ->join('package_bookings', 'packages.id', '=', 'package_bookings.package_id')
            ->select(
                'packages.id',
                'packages.name as title',
                'packages.description as short_desc',
                'packages.base_price as price',
                'packages.currency',
                DB::raw("'package' as type"),
                DB::raw("'local' as source"),
                DB::raw('NULL as image_url'),
                DB::raw('NULL as location'),
                DB::raw('NULL as rating'),
                DB::raw('NULL as review_count'),
                DB::raw("'Trending now' as reason_tag"),
                DB::raw('COUNT(package_bookings.id) as booking_count')
            )
            ->where('package_bookings.created_at', '>=', now()->subDays(30))
            ->groupBy('packages.id', 'packages.name', 'packages.description',
                     'packages.base_price', 'packages.currency')
            ->orderByDesc('booking_count')
            ->limit($limit)
            ->get();

        // Enhance with images and location data
        foreach ($recommendations as $rec) {
            // TODO: Add package_images table
            // $image = DB::table('package_images')
            //     ->where('package_id', $rec->id)
            //     ->where('is_main', true)
            //     ->first();
            // 
            // if ($image && isset($image->urls)) {
            //     $urls = json_decode($image->urls, true);
            //     $rec->image_url = $urls['medium'] ?? $urls['original'] ?? null;
            // }

            $destinations = DB::table('package_destinations')
                ->where('package_id', $rec->id)
                ->pluck('destination_name')
                ->toArray();
            $rec->location = implode(', ', array_slice($destinations, 0, 2));
            
            $ratingData = DB::table('package_ratings')
                ->where('package_id', $rec->id)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
                ->first();
            
            if ($ratingData && $ratingData->count > 0) {
                $rec->rating = round($ratingData->avg_rating, 1);
                $rec->review_count = $ratingData->count;
            }
            
            unset($rec->booking_count);
        }

        return $recommendations->toArray();
    }
}
