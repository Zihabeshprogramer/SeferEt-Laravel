<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ServiceOffer;
use App\Models\HotelService;
use App\Models\TransportService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * ServiceDiscoveryController
 * 
 * API endpoints for B2B service discovery and integration
 */
class ServiceDiscoveryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }
    
    /**
     * Get all available B2B service offers
     */
    public function getServiceOffers(Request $request): JsonResponse
    {
        $query = ServiceOffer::with(['provider', 'service'])
                            ->active();
        
        // Filter by service type if provided
        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }
        
        // Filter by location if provided
        if ($request->has('location')) {
            $location = $request->location;
            $query->whereHas('service', function($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                  ->orWhere('coverage_areas', 'like', "%{$location}%");
            });
        }
        
        // Filter by price range if provided
        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }
        
        $offers = $query->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }
    
    /**
     * Get hotel services
     */
    public function getHotelServices(Request $request): JsonResponse
    {
        $query = HotelService::with(['provider', 'offers'])
                           ->active();
        
        // Filter by city if provided
        if ($request->has('city')) {
            $query->inCity($request->city);
        }
        
        // Filter by star rating if provided
        if ($request->has('star_rating')) {
            $query->byStarRating($request->star_rating);
        }
        
        $hotels = $query->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $hotels,
        ]);
    }
    
    /**
     * Get transport services
     */
    public function getTransportServices(Request $request): JsonResponse
    {
        $query = TransportService::with(['provider', 'offers'])
                                ->active();
        
        // Filter by transport type if provided
        if ($request->has('transport_type')) {
            $query->ofType($request->transport_type);
        }
        
        // Filter by route type if provided
        if ($request->has('route_type')) {
            $query->forRoute($request->route_type);
        }
        
        $services = $query->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }
    
    /**
     * Get service providers
     */
    public function getServiceProviders(Request $request): JsonResponse
    {
        $query = User::serviceProviders()
                    ->active()
                    ->with(['hotelServices', 'transportServices', 'serviceOffers']);
        
        // Filter by provider type if provided
        if ($request->has('provider_type')) {
            $query->where('role', $request->provider_type);
        }
        
        $providers = $query->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $providers,
        ]);
    }
    
    /**
     * Get specific service offer details
     */
    public function getServiceOffer(ServiceOffer $serviceOffer): JsonResponse
    {
        if (!$serviceOffer->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Service offer is not available'
            ], 404);
        }
        
        $serviceOffer->load(['provider', 'service']);
        
        return response()->json([
            'success' => true,
            'data' => $serviceOffer,
        ]);
    }
}
