<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PriceLookupService;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PriceLookupController extends Controller
{
    private PriceLookupService $priceLookupService;

    public function __construct(PriceLookupService $priceLookupService)
    {
        $this->priceLookupService = $priceLookupService;
        $this->middleware('auth');
    }

    /**
     * Get base price for a service based on provider type and item ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBasePrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'provider_type' => 'required|string|in:hotel,flight,transport',
                'item_id' => 'required|integer|min:1',
                'quantity' => 'nullable|integer|min:1|max:100',
                'start_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
                'end_date' => 'nullable|date_format:Y-m-d|after:start_date',
                'class' => 'nullable|string|in:economy,business,first_class,first',
                'is_group' => 'nullable|boolean'
            ]);

            $options = [
                'quantity' => $validated['quantity'] ?? 1,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'class' => $validated['class'] ?? 'economy',
                'is_group' => $validated['is_group'] ?? false
            ];

            $result = $this->priceLookupService->getBasePrice(
                $validated['provider_type'],
                $validated['item_id'],
                $options
            );

            // Add additional context for the response
            $result['request_context'] = [
                'provider_type' => $validated['provider_type'],
                'item_id' => $validated['item_id'],
                'options' => $options,
                'timestamp' => now()->toISOString()
            ];

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error in price lookup API', [
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while looking up pricing',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get base price for a specific service request
     *
     * @param Request $request
     * @param int $serviceRequestId
     * @return JsonResponse
     */
    public function getServiceRequestPrice(Request $request, int $serviceRequestId): JsonResponse
    {
        try {
            // Find the service request and verify permissions
            $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
            
            // Check if user has permission to view this service request
            if (!$this->canAccessServiceRequest($serviceRequest)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access this service request'
                ], 403);
            }

            // Extract options from the service request
            $options = [
                'quantity' => $serviceRequest->requested_quantity,
                'start_date' => $serviceRequest->start_date,
                'end_date' => $serviceRequest->end_date,
                'class' => $request->input('class', 'economy'),
                'is_group' => $request->boolean('is_group', false)
            ];

            // Add any additional metadata from service request
            if ($serviceRequest->metadata) {
                $metadata = is_array($serviceRequest->metadata) 
                    ? $serviceRequest->metadata 
                    : json_decode($serviceRequest->metadata, true);
                    
                if (isset($metadata['guest_count'])) {
                    $options['guest_count'] = $metadata['guest_count'];
                }
                if (isset($metadata['special_requirements'])) {
                    $options['special_requirements'] = $metadata['special_requirements'];
                }
            }

            $result = $this->priceLookupService->getBasePrice(
                $serviceRequest->provider_type,
                $serviceRequest->item_id,
                $options
            );

            // Add service request context
            $result['service_request_context'] = [
                'id' => $serviceRequest->id,
                'uuid' => $serviceRequest->uuid,
                'status' => $serviceRequest->status,
                'provider_type' => $serviceRequest->provider_type,
                'agent_name' => $serviceRequest->agent->name ?? 'Unknown',
                'package_name' => $serviceRequest->package->name ?? 'Unknown Package'
            ];

            return response()->json($result);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service request not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error in service request price lookup', [
                'service_request_id' => $serviceRequestId,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while looking up service request pricing',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get price calculation for date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPriceForDateRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'provider_type' => 'required|string|in:hotel,flight,transport',
                'item_id' => 'required|integer|min:1',
                'start_date' => 'required|date_format:Y-m-d|after_or_equal:today',
                'end_date' => 'required|date_format:Y-m-d|after:start_date',
                'quantity' => 'nullable|integer|min:1|max:100',
                'class' => 'nullable|string|in:economy,business,first_class,first',
                'is_group' => 'nullable|boolean'
            ]);

            $options = [
                'quantity' => $validated['quantity'] ?? 1,
                'class' => $validated['class'] ?? 'economy',
                'is_group' => $validated['is_group'] ?? false
            ];

            $result = $this->priceLookupService->calculatePriceForDateRange(
                $validated['provider_type'],
                $validated['item_id'],
                $validated['start_date'],
                $validated['end_date'],
                $options
            );

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error in date range price lookup', [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while calculating price for date range',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check if the current user can access a service request
     *
     * @param ServiceRequest $serviceRequest
     * @return bool
     */
    private function canAccessServiceRequest(ServiceRequest $serviceRequest): bool
    {
        $user = Auth::user();
        
        // Admin can access all
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Provider can access their own requests
        if ($user->hasRole('hotel_provider') || $user->hasRole('transport_provider') || $user->hasRole('flight_provider')) {
            return $serviceRequest->provider_id === $user->id;
        }
        
        // Travel agent can access their own requests
        if ($user->hasRole('travel_agent')) {
            return $serviceRequest->agent_id === $user->id;
        }
        
        return false;
    }

    /**
     * Get pricing info for multiple items (batch lookup)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBatchPrices(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'requests' => 'required|array|min:1|max:10',
                'requests.*.provider_type' => 'required|string|in:hotel,flight,transport',
                'requests.*.item_id' => 'required|integer|min:1',
                'requests.*.quantity' => 'nullable|integer|min:1|max:100',
                'requests.*.start_date' => 'nullable|date_format:Y-m-d',
                'requests.*.end_date' => 'nullable|date_format:Y-m-d|after:requests.*.start_date',
                'requests.*.class' => 'nullable|string|in:economy,business,first_class,first',
                'requests.*.is_group' => 'nullable|boolean'
            ]);

            $results = [];
            foreach ($validated['requests'] as $index => $requestData) {
                $options = [
                    'quantity' => $requestData['quantity'] ?? 1,
                    'start_date' => $requestData['start_date'] ?? null,
                    'end_date' => $requestData['end_date'] ?? null,
                    'class' => $requestData['class'] ?? 'economy',
                    'is_group' => $requestData['is_group'] ?? false
                ];

                $result = $this->priceLookupService->getBasePrice(
                    $requestData['provider_type'],
                    $requestData['item_id'],
                    $options
                );

                $result['batch_index'] = $index;
                $result['request_data'] = $requestData;
                $results[] = $result;
            }

            return response()->json([
                'success' => true,
                'message' => 'Batch price lookup completed',
                'count' => count($results),
                'results' => $results
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error in batch price lookup', [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during batch price lookup',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}