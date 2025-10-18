<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Package;
use App\Models\PackageDraft;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\NotificationService;
use App\Events\ServiceRequestCreated;
use App\Events\ServiceRequestApproved;
use App\Events\ServiceRequestRejected;
use App\Events\ServiceRequestExpired;
use App\Notifications\ServiceRequestCreatedNotification;
use App\Notifications\ServiceRequestApprovedNotification;
use App\Notifications\ServiceRequestRejectedNotification;
use App\Notifications\ServiceRequestExpiredNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    protected ApprovalService $approvalService;
    protected NotificationService $notificationService;

    public function __construct(
        ApprovalService $approvalService,
        NotificationService $notificationService
    ) {
        $this->approvalService = $approvalService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new service request (Agent action)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'package_id' => 'required|integer',
                'provider_id' => 'required|integer', // This is actually the service entity ID
                'provider_type' => 'required|in:hotel,hotels,flight,flights,transport,transports',
                'item_id' => 'required|integer',
                // Quantity is optional - will be determined from draft data based on provider type
                'requested_quantity' => 'nullable|integer|min:1',
                // Dates are optional - will be taken from draft data
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'guest_count' => 'nullable|integer|min:1',
                'special_requirements' => 'nullable|string|max:1000',
                'expires_in_hours' => 'nullable|integer|min:1|max:168' // Max 1 week
            ]);
            
            // Normalize provider_type to singular form
            $validated['provider_type'] = $this->normalizeProviderType($validated['provider_type']);
            
            // Resolve actual provider user ID from service entity
            $actualProviderId = $this->resolveProviderUserId($validated['provider_id'], $validated['provider_type']);
            if (!$actualProviderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid provider or provider not found'
                ], 422);
            }
            
            // Update provider_id to the actual user ID
            $serviceEntityId = $validated['provider_id']; // Keep original for item_id
            $validated['provider_id'] = $actualProviderId;

            // Find package or package draft
            $package = null;
            $packageDraft = null;
            $isPackageDraft = false;
            
            // First try to find as a published package
            $package = Package::find($validated['package_id']);
            
            if (!$package) {
                // If not found, try to find as a draft package
                $packageDraft = PackageDraft::find($validated['package_id']);
                if ($packageDraft && !$packageDraft->isExpired()) {
                    $isPackageDraft = true;
                }
            }
            
            if (!$package && !$packageDraft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }
            
            // Verify agent owns the package/draft
            $ownerId = $isPackageDraft ? $packageDraft->user_id : $package->creator_id;
            if ($ownerId !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create requests for your own packages'
                ], 403);
            }
            
            // Extract dates and calculate quantity from package/draft data
            if ($isPackageDraft) {
                $draftData = $packageDraft->draft_data;
                $startDate = $validated['start_date'] ?? ($draftData['start_date'] ?? null);
                $endDate = $validated['end_date'] ?? ($draftData['end_date'] ?? null);
                $guestCount = $validated['guest_count'] ?? ($draftData['max_participants'] ?? 1);
                
                // Calculate quantity based on provider type and draft data
                $requestedQuantity = $this->calculateQuantityFromDraft(
                    $validated['provider_type'], 
                    $draftData, 
                    $validated['requested_quantity'],
                    $serviceEntityId // Pass the hotel ID for optimized room calculation
                );
            } else {
                // For published packages, use provided dates or package dates
                $startDate = $validated['start_date'] ?? $package->start_date->format('Y-m-d');
                $endDate = $validated['end_date'] ?? $package->end_date->format('Y-m-d');
                $guestCount = $validated['guest_count'] ?? $package->max_participants ?? 1;
                
                // For published packages, quantity must be provided or default to 1
                $requestedQuantity = $validated['requested_quantity'] ?? 1;
            }
            
            if (!$startDate || !$endDate) {
                Log::error('Package dates missing', [
                    'is_draft' => $isPackageDraft,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'draft_data_keys' => $isPackageDraft ? array_keys($draftData) : null,
                    'package_data' => !$isPackageDraft ? [
                        'start_date' => $package->start_date ?? 'null',
                        'end_date' => $package->end_date ?? 'null'
                    ] : null
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Package dates are required but not found in package data'
                ], 422);
            }

            // Check if provider exists and has the right role
            $provider = User::findOrFail($validated['provider_id']);
            // Skip role check for now - assume all users can be providers
            // if (!method_exists($provider, 'hasRole') || !$provider->hasRole('service_provider')) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Invalid provider'
            //     ], 422);
            // }

            // Set expiration time
            $expiresAt = now()->addHours($validated['expires_in_hours'] ?? 24);

            // Check if this is own service (auto-approve)
            $isOwnService = $this->checkIfOwnService(
                $isPackageDraft ? $packageDraft : $package,
                $isPackageDraft, 
                $validated['provider_id'],
                $validated['provider_type'],
                $validated['item_id']
            );

            DB::beginTransaction();

            // Prepare metadata with guest count and special requirements
            $metadata = [];
            if ($guestCount) {
                $metadata['guest_count'] = $guestCount;
            }
            if ($validated['special_requirements']) {
                $metadata['special_requirements'] = $validated['special_requirements'];
            }
            if ($isPackageDraft) {
                $metadata['is_draft_package'] = true;
                $metadata['draft_id'] = $packageDraft->id;
            }
            
            $serviceRequest = ServiceRequest::create([
                'uuid' => (string) \Str::uuid(),
                'package_id' => $isPackageDraft ? null : $validated['package_id'], // Set to null for draft packages
                'agent_id' => Auth::id(),
                'provider_id' => $validated['provider_id'], // Now the actual user provider ID
                'provider_type' => $validated['provider_type'],
                'item_id' => $serviceEntityId, // Use the service entity ID (hotel/flight/transport ID)
                'requested_quantity' => $requestedQuantity,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'metadata' => !empty($metadata) ? $metadata : null,
                'priority' => ServiceRequest::PRIORITY_NORMAL, // Default priority, no user input
                'expires_at' => $expiresAt,
                'auto_approved' => $isOwnService,
                'status' => $isOwnService ? ServiceRequest::STATUS_APPROVED : ServiceRequest::STATUS_PENDING
            ]);

            // Auto-approve if own service
            if ($isOwnService) {
                try {
                    $approvalResult = $this->approvalService->approveRequest($serviceRequest, [
                        'auto_approved' => true,
                        'notes' => 'Automatically approved - own service',
                        'pricing' => [
                            'unit_price' => null, // Will use default from inventory
                            'commission_rate' => 0 // No commission for own service
                        ]
                    ]);
                    
                    if (!$approvalResult['success']) {
                        Log::warning('Auto-approval failed, reverting to pending status', [
                            'service_request_id' => $serviceRequest->id,
                            'error' => $approvalResult['message'] ?? 'Unknown error',
                            'error_code' => $approvalResult['error_code'] ?? null
                        ]);
                        
                        // Revert to pending status if auto-approval fails
                        $serviceRequest->update([
                            'status' => ServiceRequest::STATUS_PENDING,
                            'auto_approved' => false
                        ]);
                    } else {
                        Log::info('Auto-approval successful with inventory allocation', [
                            'service_request_id' => $serviceRequest->id,
                            'allocation_id' => $approvalResult['allocation']->id ?? null
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error during auto-approval', [
                        'service_request_id' => $serviceRequest->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Revert to pending status on error
                    $serviceRequest->update([
                        'status' => ServiceRequest::STATUS_PENDING,
                        'auto_approved' => false
                    ]);
                }
            }

            DB::commit();

            // Load relationships for events and notifications
            $serviceRequest->load(['agent', 'provider']);
            
            // Only load package relationship if it's not a draft
            if (!$isPackageDraft) {
                $serviceRequest->load(['package']);
            }

            // Send notifications
            if (!$isOwnService) {
                $serviceRequest->provider->notify(new ServiceRequestCreatedNotification($serviceRequest));
            }
            
            // Broadcast real-time event
            broadcast(new ServiceRequestCreated($serviceRequest));

            // Prepare the final data response
            $responseData = $serviceRequest->load(['agent', 'provider']);
            if (!$isPackageDraft) {
                $responseData->load(['package']);
            }
            
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'is_draft_package' => $isPackageDraft,
                'message' => $isOwnService ? 
                    'Service request created and auto-approved' : 
                    'Service request created successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating service request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create service request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service requests for a provider (Provider inbox)
     */
    public function providerIndex(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'nullable|in:pending,approved,rejected,expired,cancelled',
                'provider_type' => 'nullable|in:hotel,flight,transport',
                'sort_by' => 'nullable|in:created_at,expires_at,status',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $query = ServiceRequest::with(['package', 'agent', 'provider', 'allocations'])
                ->where('provider_id', Auth::id());

            // Apply filters
            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }


            if (isset($validated['provider_type'])) {
                $query->where('provider_type', $validated['provider_type']);
            }

            // Apply sorting
            $sortBy = $validated['sort_by'] ?? 'created_at';
            $sortOrder = $validated['sort_order'] ?? 'desc';
            
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $validated['per_page'] ?? 15;
            $serviceRequests = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $serviceRequests,
                'summary' => [
                    'pending_count' => ServiceRequest::where('provider_id', Auth::id())
                        ->where('status', ServiceRequest::STATUS_PENDING)->count(),
                    'expiring_soon_count' => ServiceRequest::where('provider_id', Auth::id())
                        ->where('status', ServiceRequest::STATUS_PENDING)
                        ->where('expires_at', '<=', now()->addHours(4))->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching provider service requests', [
                'error' => $e->getMessage(),
                'provider_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service requests'
            ], 500);
        }
    }

    /**
     * Get service requests for an agent (Agent's packages)
     */
    public function agentIndex(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'package_id' => 'nullable|exists:packages,id',
                'status' => 'nullable|in:pending,approved,rejected,expired,cancelled',
                'provider_type' => 'nullable|in:hotel,flight,transport'
            ]);

            $query = ServiceRequest::with(['package', 'provider', 'allocations'])
                ->where('agent_id', Auth::id());

            if (isset($validated['package_id'])) {
                $query->where('package_id', $validated['package_id']);
            }

            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (isset($validated['provider_type'])) {
                $query->where('provider_type', $validated['provider_type']);
            }

            $serviceRequests = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $serviceRequests
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching agent service requests', [
                'error' => $e->getMessage(),
                'agent_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service requests'
            ], 500);
        }
    }

    /**
     * Show detailed service request
     */
    public function show($id): JsonResponse
    {
        try {
            $serviceRequest = ServiceRequest::with([
                'package',
                'agent',
                'provider',
                'allocations'
            ])->findOrFail($id);
            
            // Check authorization
            $user = Auth::user();
            if (!$this->canAccessRequest($serviceRequest, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $serviceRequest
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching service request details', [
                'error' => $e->getMessage(),
                'request_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service request'
            ], 500);
        }
    }

    /**
     * Approve service request (Provider action)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000',
                'pricing' => 'nullable|array',
                'pricing.unit_price' => 'nullable|numeric|min:0',
                'pricing.commission_rate' => 'nullable|numeric|min:0|max:100',
                'pricing.total_price' => 'nullable|numeric|min:0',
                'terms_conditions' => 'nullable|string|max:2000'
            ]);

            $serviceRequest = ServiceRequest::findOrFail($id);

            // Verify provider ownership
            if ($serviceRequest->provider_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only approve your own service requests'
                ], 403);
            }

            // Check if request can be approved
            if (!$serviceRequest->canBeApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request cannot be approved in current state'
                ], 422);
            }

            DB::beginTransaction();

            $result = $this->approvalService->approveRequest($serviceRequest, $validated);

            if ($result['success']) {
                DB::commit();
                
                // Load fresh data with relationships
                $serviceRequest = $serviceRequest->fresh(['package', 'agent', 'provider', 'allocations']);
                
                // Send notifications
                $serviceRequest->agent->notify(new ServiceRequestApprovedNotification($serviceRequest));
                
                // Broadcast real-time event
                broadcast(new ServiceRequestApproved($serviceRequest));

                return response()->json([
                    'success' => true,
                    'data' => $serviceRequest->fresh(['package', 'agent', 'provider', 'allocations']),
                    'message' => 'Service request approved successfully'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? null
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving service request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'provider_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve service request'
            ], 500);
        }
    }

    /**
     * Reject service request (Provider action)
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:1000',
                'alternative_dates' => 'nullable|array',
                'alternative_dates.*.start_date' => 'date|after:today',
                'alternative_dates.*.end_date' => 'date|after:alternative_dates.*.start_date'
            ]);

            $serviceRequest = ServiceRequest::findOrFail($id);

            // Verify provider ownership
            if ($serviceRequest->provider_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only reject your own service requests'
                ], 403);
            }

            if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be rejected'
                ], 422);
            }

            DB::beginTransaction();

            $serviceRequest->reject([
                'rejection_reason' => $validated['rejection_reason'],
                'alternative_dates' => $validated['alternative_dates'] ?? null,
                'rejected_by' => Auth::id(),
                'rejected_at' => now()
            ]);

            DB::commit();

            // Load fresh data with relationships
            $serviceRequest = $serviceRequest->fresh(['package', 'agent', 'provider']);

            // Send notifications
            $serviceRequest->agent->notify(new ServiceRequestRejectedNotification($serviceRequest));
            
            // Broadcast real-time event
            broadcast(new ServiceRequestRejected($serviceRequest));

            return response()->json([
                'success' => true,
                'data' => $serviceRequest->fresh(['package', 'agent', 'provider']),
                'message' => 'Service request rejected'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting service request', [
                'error' => $e->getMessage(),
                'request_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject service request'
            ], 500);
        }
    }

    /**
     * Cancel service request (Agent action)
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cancellation_reason' => 'required|string|max:1000'
            ]);

            $serviceRequest = ServiceRequest::findOrFail($id);

            // Verify agent ownership
            if ($serviceRequest->agent_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only cancel your own service requests'
                ], 403);
            }

            if (!in_array($serviceRequest->status, [
                ServiceRequest::STATUS_PENDING,
                ServiceRequest::STATUS_APPROVED
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request cannot be cancelled in current state'
                ], 422);
            }

            DB::beginTransaction();

            // Release any allocations
            foreach ($serviceRequest->allocations as $allocation) {
                $allocation->release([
                    'reason' => 'Service request cancelled',
                    'cancelled_by' => Auth::id()
                ]);
            }

            $serviceRequest->update([
                'status' => ServiceRequest::STATUS_CANCELLED,
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service request cancelled'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling service request', [
                'error' => $e->getMessage(),
                'request_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel service request'
            ], 500);
        }
    }

    /**
     * Get batch service request status
     */
    public function batchStatus(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'request_ids' => 'required|array',
                'request_ids.*' => 'required|integer|exists:service_requests,id'
            ]);
            
            $serviceRequests = ServiceRequest::whereIn('id', $validated['request_ids'])
                ->select([
                    'id', 
                    'uuid',
                    'status', 
                    'created_at', 
                    'expires_at',
                    'rejected_reason',
                    'requested_quantity',
                    'provider_id',
                    'provider_type'
                ])
                ->get();
                
            return response()->json([
                'success' => true,
                'requests' => $serviceRequests
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching batch service request status', [
                'error' => $e->getMessage(),
                'request_ids' => $request->input('request_ids')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service request status'
            ], 500);
        }
    }

    /**
     * Get package approval status
     */
    public function packageApprovalStatus($packageId): JsonResponse
    {
        try {
            $package = Package::findOrFail($packageId);

            // Verify agent ownership
            if ($package->creator_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $serviceRequests = ServiceRequest::where('package_id', $packageId)->get();

            $approvalStatus = [
                'package_id' => $packageId,
                'total_requests' => $serviceRequests->count(),
                'approved' => $serviceRequests->where('status', ServiceRequest::STATUS_APPROVED)->count(),
                'pending' => $serviceRequests->where('status', ServiceRequest::STATUS_PENDING)->count(),
                'rejected' => $serviceRequests->where('status', ServiceRequest::STATUS_REJECTED)->count(),
                'expired' => $serviceRequests->where('status', ServiceRequest::STATUS_EXPIRED)->count(),
                'cancelled' => $serviceRequests->where('status', ServiceRequest::STATUS_CANCELLED)->count(),
                'can_proceed' => $serviceRequests->whereNotIn('status', [
                    ServiceRequest::STATUS_APPROVED,
                    ServiceRequest::STATUS_CANCELLED
                ])->count() === 0,
                'blocking_requests' => $serviceRequests->whereIn('status', [
                    ServiceRequest::STATUS_PENDING,
                    ServiceRequest::STATUS_REJECTED,
                    ServiceRequest::STATUS_EXPIRED
                ])->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $approvalStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching package approval status', [
                'error' => $e->getMessage(),
                'package_id' => $packageId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch approval status'
            ], 500);
        }
    }

    /**
     * Check if service is own service (agent owns the provider)
     */
    private function checkIfOwnService($packageOrDraft, bool $isDraft, int $providerId, string $providerType, int $itemId): bool
    {
        // Simple check: if creator_id/user_id equals provider_id, it's own service
        // You may need more complex logic based on your business rules
        $ownerId = $isDraft ? $packageOrDraft->user_id : $packageOrDraft->creator_id;
        return $ownerId === $providerId;
    }

    /**
     * Calculate appropriate quantity based on provider type and draft data
     */
     private function calculateQuantityFromDraft(string $providerType, array $draftData, ?int $overrideQuantity = null, ?int $serviceEntityId = null): int
     {
         // If quantity is explicitly provided, use it
         if ($overrideQuantity !== null) {
             return $overrideQuantity;
         }
         
         // Normalize provider type
         $normalizedType = $this->normalizeProviderType($providerType);
         
         // Get participant count for calculations
         $participants = $draftData['max_participants'] ?? 1;
         $duration = $draftData['duration_days'] ?? 1;
         
         switch ($normalizedType) {
             case 'hotel':
                 // For hotels: calculate rooms needed using optimized allocation
                 // Pass hotel ID if available for room-specific optimization
                 $hotelId = ($normalizedType === 'hotel') ? $serviceEntityId : null;
                 $roomsNeeded = $this->calculateOptimalHotelRooms($participants, $hotelId);
                 
                 // Check if draft has specific accommodation preferences
                 // For now, we'll use the calculated rooms but this could be enhanced
                 // to check for specific accommodation data if available in the draft
                 
                 return $roomsNeeded;
                 
             case 'flight':
                 // For flights: number of seats = number of participants
                 return $participants;
                 
             case 'transport':
                 // For transport: calculate vehicles needed
                 // Assuming 45-50 seats per bus for larger groups, 7-8 seats per van for smaller groups
                 if ($participants <= 8) {
                     return 1; // One van/car
                 } elseif ($participants <= 50) {
                     return 1; // One bus
                 } else {
                     // Multiple buses needed
                     return max(1, ceil($participants / 50));
                 }
                 
             default:
                 return 1; // Default fallback
         }
     }
     
     /**
      * Resolve actual provider user ID from service entity ID and type
      */
     private function resolveProviderUserId(int $serviceEntityId, string $providerType): ?int
     {
         try {
             switch ($providerType) {
                 case 'hotel':
                     $hotel = \App\Models\Hotel::find($serviceEntityId);
                     return $hotel ? $hotel->provider_id : null;
                     
                 case 'flight':
                     $flight = \App\Models\Flight::find($serviceEntityId);
                     return $flight ? $flight->provider_id : null;
                     
                 case 'transport':
                     $transportService = \App\Models\TransportService::find($serviceEntityId);
                     return $transportService ? $transportService->provider_id : null;
                     
                 default:
                     return null;
             }
         } catch (\Exception $e) {
             Log::error('Error resolving provider user ID', [
                 'service_entity_id' => $serviceEntityId,
                 'provider_type' => $providerType,
                 'error' => $e->getMessage()
             ]);
             return null;
         }
     }
     
     /**
      * Calculate optimal hotel room allocation based on participants and available rooms
      */
     private function calculateOptimalHotelRooms(int $participants, ?int $hotelId = null): int
     {
         // If no specific hotel is provided, use default calculation
         if ($hotelId === null) {
             // Default fallback: assume average of 3 people per room
             return max(1, ceil($participants / 3));
         }
         
         try {
             // Get available rooms for this hotel with their occupancies
             $hotel = \App\Models\Hotel::find($hotelId);
             if (!$hotel) {
                 // Hotel not found, use default calculation
                 return max(1, ceil($participants / 3));
             }
             
             // Get available rooms grouped by max_occupancy
             $availableRooms = $hotel->rooms()
                 ->where('is_available', true)
                 ->where('is_active', true)
                 ->selectRaw('max_occupancy, COUNT(*) as available_count')
                 ->groupBy('max_occupancy')
                 ->orderByDesc('max_occupancy') // Start with highest capacity rooms
                 ->get()
                 ->pluck('available_count', 'max_occupancy')
                 ->toArray();
             
             if (empty($availableRooms)) {
                 // No available rooms, use default calculation
                 return max(1, ceil($participants / 3));
             }
             
             return $this->optimizeRoomAllocation($participants, $availableRooms);
             
         } catch (\Exception $e) {
             \Log::error('Error calculating optimal hotel rooms', [
                 'error' => $e->getMessage(),
                 'participants' => $participants,
                 'hotel_id' => $hotelId
             ]);
             
             // Fallback to default calculation on error
             return max(1, ceil($participants / 3));
         }
     }
     
     /**
      * Optimize room allocation using greedy algorithm
      */
     private function optimizeRoomAllocation(int $participants, array $availableRooms): int
     {
         $totalRoomsNeeded = 0;
         $remainingParticipants = $participants;
         
         // Sort room types by occupancy in descending order (largest rooms first)
         krsort($availableRooms); // Use krsort instead of arsort to sort by keys (occupancy) descending
         
         foreach ($availableRooms as $occupancy => $availableCount) {
             if ($remainingParticipants <= 0) {
                 break;
             }
             
             // Calculate how many rooms of this type we need
             $roomsOfThisTypeNeeded = min(
                 $availableCount, // Don't exceed available rooms
                 ceil($remainingParticipants / $occupancy) // Rooms needed for remaining participants
             );
             
             // Use all available rooms of this type if they help accommodate participants efficiently
             $participantsThisTypeCanAccommodate = $roomsOfThisTypeNeeded * $occupancy;
             
             // If using all available rooms would overaccommodate by a lot, use fewer rooms
             if ($participantsThisTypeCanAccommodate > $remainingParticipants) {
                 $roomsOfThisTypeNeeded = ceil($remainingParticipants / $occupancy);
             }
             
             $totalRoomsNeeded += $roomsOfThisTypeNeeded;
             $remainingParticipants -= ($roomsOfThisTypeNeeded * $occupancy);
         }
         
         // If we still have remaining participants, we need more rooms
         // This shouldn't happen with proper room availability, but as a safety measure
         if ($remainingParticipants > 0) {
             // Find the smallest available room type to accommodate remaining participants
             $smallestRoomOccupancy = min(array_keys($availableRooms));
             $additionalRooms = ceil($remainingParticipants / $smallestRoomOccupancy);
             $totalRoomsNeeded += $additionalRooms;
         }
         
         return max(1, $totalRoomsNeeded);
     }
     
     /**
      * Normalize provider type to singular form
      */
     private function normalizeProviderType(string $providerType): string
     {
         $typeMap = [
             'hotels' => 'hotel',
             'flights' => 'flight',
             'transports' => 'transport'
         ];
         
         return $typeMap[$providerType] ?? $providerType;
     }

    /**
     * Get service request status summary for a package
     */
    public function packageStatus($packageId): JsonResponse
    {
        try {
            // Get all service requests for this package by the authenticated user
            $serviceRequests = ServiceRequest::where('package_id', $packageId)
                ->where('agent_id', Auth::id())
                ->with(['provider', 'agent'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calculate status summary
            $statusSummary = [
                'package_id' => $packageId,
                'total_requests' => $serviceRequests->count(),
                'pending' => $serviceRequests->where('status', ServiceRequest::STATUS_PENDING)->count(),
                'approved' => $serviceRequests->where('status', ServiceRequest::STATUS_APPROVED)->count(),
                'rejected' => $serviceRequests->where('status', ServiceRequest::STATUS_REJECTED)->count(),
                'expired' => $serviceRequests->where('status', ServiceRequest::STATUS_EXPIRED)->count(),
                'cancelled' => $serviceRequests->where('status', ServiceRequest::STATUS_CANCELLED)->count(),
                'approval_progress' => 0,
                'can_proceed' => false,
                'requests' => []
            ];
            
            // Calculate progress percentage
            if ($statusSummary['total_requests'] > 0) {
                $statusSummary['approval_progress'] = round(
                    ($statusSummary['approved'] / $statusSummary['total_requests']) * 100, 
                    1
                );
                
                // Can proceed if all non-cancelled requests are approved
                $nonCancelledRequests = $statusSummary['total_requests'] - $statusSummary['cancelled'];
                $statusSummary['can_proceed'] = $nonCancelledRequests > 0 && 
                    $statusSummary['approved'] === $nonCancelledRequests;
            }
            
            // Build detailed request list
            foreach ($serviceRequests as $request) {
                $statusSummary['requests'][] = [
                    'id' => $request->id,
                    'uuid' => $request->uuid,
                    'provider_type' => $request->provider_type,
                    'provider_name' => $request->provider->name ?? 'Unknown Provider',
                    'status' => $request->status,
                    'status_label' => ucfirst($request->status),
                    'status_badge_class' => $this->getStatusBadgeClass($request->status),
                    'requested_quantity' => $request->requested_quantity,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'expires_at' => $request->expires_at?->format('Y-m-d H:i:s'),
                    'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                    'is_expired' => $request->expires_at && $request->expires_at->isPast(),
                    'service_details' => $this->getServiceDetails($request->provider_type, $request->item_id),
                    'special_requirements' => $request->metadata['special_requirements'] ?? null
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $statusSummary
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching package service request status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'package_id' => $packageId,
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service request status',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
    
    /**
     * Get status badge CSS class for UI display
     */
    private function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            ServiceRequest::STATUS_PENDING => 'badge-warning',
            ServiceRequest::STATUS_APPROVED => 'badge-success', 
            ServiceRequest::STATUS_REJECTED => 'badge-danger',
            ServiceRequest::STATUS_EXPIRED => 'badge-secondary',
            ServiceRequest::STATUS_CANCELLED => 'badge-dark',
            default => 'badge-secondary'
        };
    }
    
    /**
     * Get human-readable service details based on provider type
     */
    private function getServiceDetails(string $providerType, int $itemId): string
    {
        try {
            switch ($providerType) {
                case 'hotel':
                    if ($itemId) {
                        $hotel = \App\Models\Hotel::find($itemId);
                        if ($hotel) {
                            return $hotel->name . ($hotel->city ? " ({$hotel->city})" : '');
                        }
                    }
                    return 'Hotel service';
                    
                case 'flight':
                    if ($itemId) {
                        $flight = \App\Models\Flight::find($itemId);
                        if ($flight) {
                            return $flight->airline . ' ' . $flight->flight_number . ' - ' . $flight->route;
                        }
                    }
                    return 'Flight service';
                    
                case 'transport':
                    if ($itemId) {
                        $transport = \App\Models\TransportService::find($itemId);
                        if ($transport) {
                            return $transport->service_name . ' (' . ucfirst($transport->transport_type) . ')';
                        }
                    }
                    return 'Transport service';
                    
                default:
                    return 'Service details not available';
            }
        } catch (\Exception $e) {
            \Log::error('Error loading service details', [
                'provider_type' => $providerType,
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            return 'Service details unavailable';
        }
    }
    
    /**
     * Check if user can access service request
     */
    private function canAccessRequest(ServiceRequest $serviceRequest, User $user): bool
    {
        return $serviceRequest->agent_id === $user->id ||
               $serviceRequest->provider_id === $user->id ||
               $user->hasRole('admin');
    }
}
