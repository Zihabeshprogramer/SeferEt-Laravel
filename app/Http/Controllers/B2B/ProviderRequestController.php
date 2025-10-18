<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\BookingIntegrationService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProviderRequestController extends Controller
{
    protected ApprovalService $approvalService;
    protected BookingIntegrationService $bookingService;
    protected RoomAvailabilityService $roomAvailabilityService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
        $this->bookingService = new BookingIntegrationService();
        $this->roomAvailabilityService = new RoomAvailabilityService();
    }

    /**
     * Display the provider requests management page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user is a provider or travel agent (for flight requests)
        if (!$user || (!$user->isServiceProvider() && !$user->hasRole('travel_agent'))) {
            abort(403, 'Access denied. Only service providers and travel agents can access this page.');
        }
        
        // Return appropriate view based on provider type or travel agent role
        if ($user->hasRole('travel_agent')) {
            // Travel agents see requests for their flights
            return view('b2b.travel-agent.requests.index');
        } elseif ($user->isHotelProvider()) {
            return view('b2b.hotel-provider.requests.index');
        } elseif ($user->isTransportProvider()) {
            // Check if transport provider requests view exists, if not, use hotel provider view as fallback
            if (view()->exists('b2b.transport-provider.requests.index')) {
                return view('b2b.transport-provider.requests.index');
            } else {
                return view('b2b.hotel-provider.requests.index');
            }
        } else {
            return view('b2b.hotel-provider.requests.index');
        }
    }

    /**
     * Get requests data for AJAX
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'status' => 'nullable|in:all,pending,approved,rejected,expired,cancelled',
                'provider_type' => 'nullable|in:hotel,flight,transport',
                'priority' => 'nullable|in:low,normal,high,urgent',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:5|max:100',
                'page' => 'nullable|integer|min:1'
            ]);

            $query = ServiceRequest::with([
                'agent:id,name,email,phone',
                'package:id,name,start_date,end_date',
                'approvedBy:id,name'
            ])
            ->where('provider_id', $user->id);

            // Apply filters
            if (isset($validated['status']) && $validated['status'] !== 'all') {
                $query->where('status', $validated['status']);
            }

            if (isset($validated['provider_type'])) {
                $query->where('provider_type', $validated['provider_type']);
            }

            if (isset($validated['priority'])) {
                $query->where('priority', $validated['priority']);
            }

            if (isset($validated['search']) && !empty($validated['search'])) {
                $search = $validated['search'];
                $query->where(function ($q) use ($search) {
                    $q->whereHas('agent', function ($agentQuery) use ($search) {
                        $agentQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('package', function ($packageQuery) use ($search) {
                        $packageQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('provider_notes', 'like', "%{$search}%")
                    ->orWhere('agent_notes', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $query->orderBy('created_at', 'desc')
                  ->orderBy('priority', 'desc');

            $perPage = $validated['per_page'] ?? 15;
            $requests = $query->paginate($perPage);

            // Transform data for frontend
            $requests->getCollection()->transform(function ($request) {
                return [
                    'id' => $request->id,
                    'uuid' => $request->uuid,
                    'travel_agent' => $request->agent ? [
                        'name' => $request->agent->name,
                        'email' => $request->agent->email,
                        'phone' => $request->agent->phone
                    ] : null,
                    'package' => $this->getPackageInfo($request),
                    'service_type' => ucfirst($request->provider_type),
                    'service_details' => $this->getServiceDetails($request),
                    'dates' => [
                        'start' => $request->start_date?->format('Y-m-d'),
                        'end' => $request->end_date?->format('Y-m-d'),
                        'formatted' => $request->start_date && $request->end_date 
                            ? $request->start_date->format('M j, Y') . ' to ' . $request->end_date->format('M j, Y')
                            : 'Not specified'
                    ],
                    'quantity' => $request->requested_quantity,
                    'status' => $request->status,
                    'priority' => $request->priority,
                    'deadline' => $request->expires_at?->format('Y-m-d H:i:s'),
                    'days_left' => $request->expires_at ? max(0, now()->diffInDays($request->expires_at, false)) : null,
                    'hours_left' => $request->expires_at ? max(0, now()->diffInHours($request->expires_at, false)) : null,
                    'is_expired' => $request->expires_at ? $request->expires_at->isPast() : false,
                    'is_urgent' => $request->requiresImmediateAttention(),
                    'agent_notes' => $request->agent_notes,
                    'provider_notes' => $request->provider_notes,
                    'pricing' => [
                        'requested_price' => $request->requested_price,
                        'offered_price' => $request->offered_price,
                        'currency' => $request->currency
                    ],
                    'metadata' => $request->metadata,
                    'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $request->created_at->diffForHumans(),
                    'approved_at' => $request->approved_at?->format('Y-m-d H:i:s'),
                    'approved_by' => $request->approvedBy?->name,
                    'booking' => $this->getBookingStatusForRequest($request)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $requests,
                'message' => 'Requests loaded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading provider requests', [
                'error' => $e->getMessage(),
                'provider_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request statistics for provider dashboard
     */
    public function getStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $stats = [
                'pending_count' => ServiceRequest::where('provider_id', $user->id)
                    ->where('status', ServiceRequest::STATUS_PENDING)
                    ->count(),
                
                'approved_this_month' => ServiceRequest::where('provider_id', $user->id)
                    ->where('status', ServiceRequest::STATUS_APPROVED)
                    ->whereMonth('approved_at', now()->month)
                    ->whereYear('approved_at', now()->year)
                    ->count(),
                
                'expiring_soon_count' => ServiceRequest::where('provider_id', $user->id)
                    ->where('status', ServiceRequest::STATUS_PENDING)
                    ->where('expires_at', '<=', now()->addHours(4))
                    ->count(),
                
                'total_requests' => ServiceRequest::where('provider_id', $user->id)->count(),
                
                'response_rate' => $this->calculateResponseRate($user->id)
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading provider request stats', [
                'error' => $e->getMessage(),
                'provider_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }

    /**
     * Get detailed information about a specific request
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $request = ServiceRequest::with([
                'agent:id,name,email,phone',
                'package:id,name,description,start_date,end_date,max_participants',
                'approvedBy:id,name',
                'allocations'
            ])
            ->where('provider_id', $user->id)
            ->findOrFail($id);

            $details = [
                'id' => $request->id,
                'uuid' => $request->uuid,
                'travel_agent' => [
                    'name' => $request->agent->name,
                    'email' => $request->agent->email,
                    'phone' => $request->agent->phone
                ],
                'package' => $request->package ? [
                    'title' => $request->package->name,
                    'description' => $request->package->description,
                    'start_date' => $request->package->start_date?->format('Y-m-d'),
                    'end_date' => $request->package->end_date?->format('Y-m-d'),
                    'max_participants' => $request->package->max_participants
                ] : null,
                'service_type' => ucfirst($request->provider_type),
                'service_details' => $this->getServiceDetails($request),
                'request_details' => [
                    'quantity' => $request->requested_quantity,
                    'allocated_quantity' => $request->allocated_quantity,
                    'start_date' => $request->start_date?->format('Y-m-d'),
                    'end_date' => $request->end_date?->format('Y-m-d'),
                    'guest_count' => $request->metadata['guest_count'] ?? null,
                    'special_requirements' => $request->metadata['special_requirements'] ?? null,
                ],
                'status' => $request->status,
                'priority' => $request->priority,
                'pricing' => [
                    'requested_price' => $request->requested_price,
                    'offered_price' => $request->offered_price,
                    'currency' => $request->currency,
                    'commission_percentage' => $request->commission_percentage
                ],
                'notes' => [
                    'agent_notes' => $request->agent_notes,
                    'provider_notes' => $request->provider_notes,
                    'rejection_reason' => $request->rejection_reason
                ],
                'timeline' => [
                    'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                    'expires_at' => $request->expires_at?->format('Y-m-d H:i:s'),
                    'responded_at' => $request->responded_at?->format('Y-m-d H:i:s'),
                    'approved_at' => $request->approved_at?->format('Y-m-d H:i:s'),
                    'rejected_at' => $request->rejected_at?->format('Y-m-d H:i:s')
                ],
                'communication_log' => $request->communication_log ?? [],
                'terms_and_conditions' => $request->terms_and_conditions,
                'approval_conditions' => $request->approval_conditions,
                'allocations' => $request->allocations->map(function ($allocation) {
                    return [
                        'id' => $allocation->id,
                        'quantity' => $allocation->quantity,
                        'status' => $allocation->status,
                        'total_price' => $allocation->total_price,
                        'allocated_at' => $allocation->allocated_at?->format('Y-m-d H:i:s')
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $details
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading request details', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'provider_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load request details'
            ], 500);
        }
    }

    /**
     * Approve a service request
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'provider_notes' => 'required|string|max:2000',
                'confirmed_price' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|size:3',
                'terms_conditions' => 'nullable|string|max:2000',
                'notify_agent' => 'boolean',
                'create_booking' => 'boolean', // New field to control booking creation
                'auto_confirm_booking' => 'boolean', // Auto-confirm the created booking
                'selected_room_id' => 'nullable|integer|exists:rooms,id' // Room selection during approval
            ]);

            $serviceRequest = ServiceRequest::where('provider_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            if (!$serviceRequest->canBeApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request cannot be approved in its current state'
                ], 422);
            }
            
            // For hotel requests, validate room availability before approval
            if ($serviceRequest->provider_type === 'hotel') {
                // Check if enough rooms are available for this request
                $availabilityCheck = $this->roomAvailabilityService->getAvailableRoomsForServiceRequest($serviceRequest);
                
                if (!$availabilityCheck['success'] || count($availabilityCheck['rooms']) === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No rooms available for the requested dates. Cannot approve this request.',
                        'error_code' => 'NO_ROOMS_AVAILABLE'
                    ], 422);
                }
                
                // Check if we have enough rooms for the requested quantity
                $availableRoomCount = count($availabilityCheck['rooms']);
                $requestedQuantity = $serviceRequest->requested_quantity ?? 1;
                
                if ($availableRoomCount < $requestedQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient rooms available. Requested: {$requestedQuantity}, Available: {$availableRoomCount}. Cannot approve this request.",
                        'error_code' => 'INSUFFICIENT_ROOMS_AVAILABLE'
                    ], 422);
                }
                
                // If a specific room is selected, assign it
                if (!empty($validated['selected_room_id'])) {
                    // Verify the selected room is among the available ones
                    $selectedRoomAvailable = collect($availabilityCheck['rooms'])->contains('id', $validated['selected_room_id']);
                    
                    if (!$selectedRoomAvailable) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected room is no longer available for the requested dates.',
                            'error_code' => 'SELECTED_ROOM_UNAVAILABLE'
                        ], 422);
                    }
                    
                    // Assign the selected room before approval
                    $roomAssignmentResult = $this->roomAvailabilityService->assignRoomToServiceRequest(
                        $serviceRequest,
                        $validated['selected_room_id']
                    );
                    
                    if (!$roomAssignmentResult['success']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to assign selected room: ' . $roomAssignmentResult['message'],
                            'error_code' => $roomAssignmentResult['error_code'] ?? 'ROOM_ASSIGNMENT_FAILED'
                        ], 422);
                    }
                    
                    Log::info('Room assigned during approval process', [
                        'service_request_id' => $serviceRequest->id,
                        'selected_room_id' => $validated['selected_room_id'],
                        'room_details' => $roomAssignmentResult['room']
                    ]);
                } else {
                    // No specific room selected - we'll let the BookingIntegrationService choose the best available room
                    // But we've already verified that rooms are available
                    Log::info('Hotel request approved without specific room assignment - rooms available', [
                        'service_request_id' => $serviceRequest->id,
                        'available_rooms_count' => count($availabilityCheck['rooms'])
                    ]);
                }
            }

            DB::beginTransaction();

            // Prepare approval options
            $approvalOptions = [
                'notes' => $validated['provider_notes'],
                'pricing' => [
                    'unit_price' => $validated['confirmed_price'] ?? null,
                    'commission_rate' => 0 // Default commission rate
                ],
                'terms_conditions' => $validated['terms_conditions'] ?? null
            ];

            Log::info('About to call ApprovalService', [
                'service_request_id' => $serviceRequest->id,
                'approval_options' => $approvalOptions
            ]);
            
            // Use the ApprovalService for atomic approval and allocation
            $result = $this->approvalService->approveRequest($serviceRequest, $approvalOptions);
            
            Log::info('ApprovalService result', [
                'service_request_id' => $serviceRequest->id,
                'result' => $result
            ]);

            if ($result['success']) {
                // Update additional fields
                $serviceRequest->update([
                    'provider_notes' => $validated['provider_notes'],
                    'offered_price' => $validated['confirmed_price'],
                    'currency' => $validated['currency'] ?? 'USD',
                    'terms_and_conditions' => isset($validated['terms_conditions']) && $validated['terms_conditions'] ? [$validated['terms_conditions']] : null
                ]);

                // Update package draft status if service request is from a draft
                // This should happen immediately after approval, not only during booking creation
                Log::info('About to update package draft status after approval', [
                    'service_request_id' => $serviceRequest->id,
                    'has_draft_metadata' => isset($serviceRequest->metadata['is_draft_package']),
                    'is_draft_package' => $serviceRequest->metadata['is_draft_package'] ?? false,
                    'draft_id' => $serviceRequest->metadata['draft_id'] ?? null
                ]);
                $this->updatePackageDraftStatus($serviceRequest);

                DB::commit();

                // Create booking if requested (default: true for hotel and transport providers)
                $bookingResult = null;
                $shouldCreateBooking = $validated['create_booking'] ?? 
                    in_array($serviceRequest->provider_type, ['hotel', 'transport']);
                
                Log::info('Service request approval - checking booking creation', [
                    'service_request_id' => $serviceRequest->id,
                    'provider_type' => $serviceRequest->provider_type,
                    'should_create_booking' => $shouldCreateBooking,
                    'create_booking_param' => $validated['create_booking'] ?? 'not_set'
                ]);
                
                if ($shouldCreateBooking) {
                    Log::info('Attempting to create booking', [
                        'service_request_id' => $serviceRequest->id
                    ]);
                    
                    try {
                        $bookingResult = $this->bookingService->convertToBooking($serviceRequest->fresh());
                        
                        Log::info('Booking creation result', [
                            'service_request_id' => $serviceRequest->id,
                            'success' => $bookingResult['success'],
                            'message' => $bookingResult['message'] ?? 'No message',
                            'error_code' => $bookingResult['error_code'] ?? null
                        ]);
                        
                        if (!$bookingResult['success']) {
                            Log::warning('Failed to create booking after service request approval', [
                                'service_request_id' => $serviceRequest->id,
                                'error' => $bookingResult['message'],
                                'error_code' => $bookingResult['error_code'] ?? null
                            ]);
                            // Don't fail the approval if booking creation fails
                        }
                    } catch (\Exception $e) {
                        Log::error('Exception during booking creation', [
                            'service_request_id' => $serviceRequest->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        $bookingResult = [
                            'success' => false,
                            'message' => 'Exception during booking creation: ' . $e->getMessage(),
                            'error_code' => 'EXCEPTION'
                        ];
                    }
                }

                // Send notification if requested
                if ($validated['notify_agent'] ?? true) {
                    $serviceRequest->agent->notify(
                        new \App\Notifications\ServiceRequestApprovedNotification($serviceRequest->fresh())
                    );
                }

                $response = [
                    'success' => true,
                    'message' => 'Request approved successfully',
                    'allocation' => $result['allocation']
                ];
                
                // Add booking information if created
                if ($bookingResult && $bookingResult['success']) {
                    $response['booking'] = [
                        'created' => true,
                        'id' => $bookingResult['booking_id'],
                        'type' => $bookingResult['booking_type'],
                        'reference' => $bookingResult['booking']->booking_reference ?? null
                    ];
                    $response['message'] .= ' and booking created automatically';
                } elseif ($shouldCreateBooking && $bookingResult && !$bookingResult['success']) {
                    // Booking creation failed - this is a significant issue
                    $response['booking'] = [
                        'created' => false,
                        'error' => $bookingResult['message'],
                        'error_code' => $bookingResult['error_code'] ?? null
                    ];
                    
                    // Check if it's a room availability issue
                    if (strpos($bookingResult['message'], 'no longer available') !== false || 
                        strpos($bookingResult['message'], 'No suitable room found') !== false) {
                        $response['success'] = false;  // Change to failure since no booking was created
                        $response['message'] = 'Request was approved but booking creation failed: No rooms available. The approval has been reverted.';
                        $response['error_code'] = 'BOOKING_FAILED_NO_ROOMS';
                        
                        // Revert the approval since booking failed
                        $serviceRequest->update([
                            'status' => 'pending',
                            'approved_at' => null,
                            'approved_by' => null,
                            'provider_notes' => ($serviceRequest->provider_notes ?? '') . ' [REVERTED: Booking creation failed - no rooms available]'
                        ]);
                        
                        Log::warning('Approval reverted due to booking creation failure', [
                            'service_request_id' => $serviceRequest->id,
                            'booking_error' => $bookingResult['message']
                        ]);
                        
                        return response()->json($response, 422);
                    } else {
                        // Other booking creation errors - keep approval but warn user
                        $response['message'] = 'Request approved successfully, but booking creation failed: ' . $bookingResult['message'];
                        $response['warning'] = 'You may need to create the booking manually or contact support.';
                    }
                } elseif ($shouldCreateBooking) {
                    // No booking result at all - this shouldn't happen but let's handle it
                    $response['booking'] = [
                        'created' => false,
                        'error' => 'Booking creation was not attempted or failed silently',
                        'error_code' => 'BOOKING_NOT_ATTEMPTED'
                    ];
                    $response['warning'] = 'Booking creation may have failed. Please check manually.';
                }
                
                return response()->json($response);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? null
                ], 422);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving service request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'provider_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a service request
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'provider_notes' => 'required|string|max:2000',
                'rejection_reason' => 'required|string|max:1000',
                'alternative_suggestions' => 'nullable|string|max:1000',
                'notify_agent' => 'boolean'
            ]);

            $serviceRequest = ServiceRequest::where('provider_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be rejected'
                ], 422);
            }

            DB::beginTransaction();

            $serviceRequest->update([
                'status' => ServiceRequest::STATUS_REJECTED,
                'rejected_at' => now(),
                'responded_at' => now(),
                'provider_notes' => $validated['provider_notes'],
                'rejection_reason' => $validated['rejection_reason'],
                'approved_by' => Auth::id(), // Track who rejected it
                'version' => $serviceRequest->version + 1
            ]);

            // Add to communication log
            $serviceRequest->addToCommunicationLog(
                'Request rejected: ' . $validated['rejection_reason'],
                'rejection'
            );

            if (!empty($validated['alternative_suggestions'])) {
                $serviceRequest->addToCommunicationLog(
                    'Alternative suggestions: ' . $validated['alternative_suggestions'],
                    'suggestion'
                );
            }
            
            // Update package draft status if this is from a draft
            $this->updatePackageDraftStatusForRejection($serviceRequest);

            DB::commit();

            // Send notification if requested
            if ($validated['notify_agent'] ?? true) {
                $serviceRequest->agent->notify(
                    new \App\Notifications\ServiceRequestRejectedNotification($serviceRequest->fresh())
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Request rejected successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting service request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'provider_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch approve/reject multiple requests
     */
    public function batchAction(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:approve,reject',
                'request_ids' => 'required|array|min:1',
                'request_ids.*' => 'integer|exists:service_requests,id',
                'provider_notes' => 'required_if:action,reject|string|max:2000',
                'rejection_reason' => 'required_if:action,reject|string|max:1000',
                'confirmed_price' => 'required_if:action,approve|nullable|numeric|min:0',
                'currency' => 'nullable|string|size:3',
                'notify_agents' => 'boolean',
                'create_bookings' => 'boolean' // Control booking creation for batch approvals
            ]);

            $user = Auth::user();
            $results = [];
            $successCount = 0;
            $failureCount = 0;

            DB::beginTransaction();

            foreach ($validated['request_ids'] as $requestId) {
                try {
                    $serviceRequest = ServiceRequest::where('provider_id', $user->id)
                        ->where('id', $requestId)
                        ->first();

                    if (!$serviceRequest) {
                        $results[] = [
                            'id' => $requestId,
                            'success' => false,
                            'message' => 'Request not found or access denied'
                        ];
                        $failureCount++;
                        continue;
                    }

                    if ($validated['action'] === 'approve') {
                        $result = $this->processSingleApproval($serviceRequest, $validated);
                    } else {
                        $result = $this->processSingleRejection($serviceRequest, $validated);
                    }

                    $results[] = array_merge(['id' => $requestId], $result);
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failureCount++;
                    }

                } catch (\Exception $e) {
                    $results[] = [
                        'id' => $requestId,
                        'success' => false,
                        'message' => 'Error processing request: ' . $e->getMessage()
                    ];
                    $failureCount++;
                }
            }

            if ($failureCount === 0) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            return response()->json([
                'success' => $failureCount === 0,
                'message' => sprintf(
                    'Batch operation completed. %d succeeded, %d failed.',
                    $successCount,
                    $failureCount
                ),
                'results' => $results,
                'summary' => [
                    'total_processed' => count($validated['request_ids']),
                    'success_count' => $successCount,
                    'failure_count' => $failureCount
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in batch request action', [
                'error' => $e->getMessage(),
                'provider_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process batch action'
            ], 500);
        }
    }

    /**
     * Process single request approval for batch operations
     */
    private function processSingleApproval(ServiceRequest $serviceRequest, array $data): array
    {
        if (!$serviceRequest->canBeApproved()) {
            return [
                'success' => false,
                'message' => 'Request cannot be approved in current state'
            ];
        }

        $approvalOptions = [
            'notes' => $data['provider_notes'] ?? 'Batch approved',
            'pricing' => [
                'unit_price' => $data['confirmed_price'] ?? null,
                'commission_rate' => 0
            ]
        ];

        $result = $this->approvalService->approveRequest($serviceRequest, $approvalOptions);

        // Create booking if requested and approval was successful
        if ($result['success']) {
            $shouldCreateBooking = $data['create_bookings'] ?? 
                in_array($serviceRequest->provider_type, ['hotel', 'transport']);
            
            if ($shouldCreateBooking) {
                $bookingResult = $this->bookingService->convertToBooking($serviceRequest->fresh());
                
                if ($bookingResult['success']) {
                    $result['booking'] = [
                        'created' => true,
                        'id' => $bookingResult['booking_id'],
                        'type' => $bookingResult['booking_type']
                    ];
                } else {
                    $result['booking'] = [
                        'created' => false,
                        'error' => $bookingResult['message']
                    ];
                }
            }
            
            // Update package draft status if service request is from a draft
            $this->updatePackageDraftStatus($serviceRequest);
            
            // Send notification if requested
            if (isset($data['notify_agents']) && $data['notify_agents']) {
                $serviceRequest->agent->notify(
                    new \App\Notifications\ServiceRequestApprovedNotification($serviceRequest->fresh())
                );
            }
        }

        return $result;
    }

    /**
     * Process single request rejection for batch operations
     */
    private function processSingleRejection(ServiceRequest $serviceRequest, array $data): array
    {
        if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
            return [
                'success' => false,
                'message' => 'Only pending requests can be rejected'
            ];
        }

        $serviceRequest->update([
            'status' => ServiceRequest::STATUS_REJECTED,
            'rejected_at' => now(),
            'responded_at' => now(),
            'provider_notes' => $data['provider_notes'],
            'rejection_reason' => $data['rejection_reason'],
            'approved_by' => Auth::id(),
            'version' => $serviceRequest->version + 1
        ]);

        if (isset($data['notify_agents']) && $data['notify_agents']) {
            $serviceRequest->agent->notify(
                new \App\Notifications\ServiceRequestRejectedNotification($serviceRequest->fresh())
            );
        }

        return [
            'success' => true,
            'message' => 'Request rejected successfully'
        ];
    }

    /**
     * Calculate response rate for a provider
     */
    private function calculateResponseRate(int $providerId): int
    {
        $totalRequests = ServiceRequest::where('provider_id', $providerId)
            ->whereIn('status', [
                ServiceRequest::STATUS_PENDING,
                ServiceRequest::STATUS_APPROVED,
                ServiceRequest::STATUS_REJECTED
            ])
            ->count();

        if ($totalRequests === 0) {
            return 100;
        }

        $respondedRequests = ServiceRequest::where('provider_id', $providerId)
            ->whereIn('status', [
                ServiceRequest::STATUS_APPROVED,
                ServiceRequest::STATUS_REJECTED
            ])
            ->count();

        return (int) round(($respondedRequests / $totalRequests) * 100);
    }

    /**
     * Get package information, handling both published packages and drafts
     */
    private function getPackageInfo(ServiceRequest $request): ?array
    {
        // First try to get published package
        if ($request->package) {
            return [
                'title' => $request->package->name,
                'start_date' => $request->package->start_date?->format('Y-m-d'),
                'end_date' => $request->package->end_date?->format('Y-m-d')
            ];
        }
        
        // Check if this request was created from a draft package
        if ($request->metadata && isset($request->metadata['is_draft_package']) && isset($request->metadata['draft_id'])) {
            try {
                $draft = \App\Models\PackageDraft::find($request->metadata['draft_id']);
                if ($draft) {
                    return [
                        'title' => $draft->name . ' (Draft)',
                        'start_date' => $request->start_date?->format('Y-m-d'),
                        'end_date' => $request->end_date?->format('Y-m-d')
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error loading draft package info', [
                    'request_id' => $request->id,
                    'draft_id' => $request->metadata['draft_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Fallback - use the service request dates
        return [
            'title' => 'Package (Details not available)',
            'start_date' => $request->start_date?->format('Y-m-d'),
            'end_date' => $request->end_date?->format('Y-m-d')
        ];
    }
    
    /**
     * Get human-readable service details based on provider type
     */
    private function getServiceDetails(ServiceRequest $request): string
    {
        try {
            switch ($request->provider_type) {
                case 'hotel':
                    if ($request->item_id) {
                        $hotel = \App\Models\Hotel::find($request->item_id);
                        if ($hotel) {
                            return $hotel->name . ($hotel->city ? " ({$hotel->city})" : '');
                        }
                    }
                    return 'Hotel service';
                    
                case 'flight':
                    if ($request->item_id) {
                        $flight = \App\Models\Flight::find($request->item_id);
                        if ($flight) {
                            return $flight->airline . ' ' . $flight->flight_number . ' - ' . $flight->route;
                        }
                    }
                    return 'Flight service';
                    
                case 'transport':
                    if ($request->item_id) {
                        $transport = \App\Models\TransportService::find($request->item_id);
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
                'request_id' => $request->id,
                'provider_type' => $request->provider_type,
                'item_id' => $request->item_id,
                'error' => $e->getMessage()
            ]);
            return 'Service details unavailable';
        }
    }
    
    /**
     * Get booking details for a service request
     */
    public function getBookingDetails($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $serviceRequest = ServiceRequest::where('provider_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();
            
            // Check if booking was created
            if (!isset($serviceRequest->metadata['booking_created']) || !$serviceRequest->metadata['booking_created']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No booking has been created for this service request'
                ]);
            }
            
            $bookingId = $serviceRequest->metadata['booking_id'];
            $bookingType = $serviceRequest->metadata['booking_type'];
            
            $booking = null;
            
            // Get booking details based on type
            switch ($bookingType) {
                case 'hotel':
                    $booking = \App\Models\HotelBooking::with(['hotel', 'room', 'customer'])
                        ->find($bookingId);
                    break;
                    
                // Add other booking types when implemented
                case 'flight':
                    // TODO: Implement flight booking retrieval
                    break;
                    
                case 'transport':
                    // TODO: Implement transport booking retrieval
                    break;
            }
            
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or has been deleted'
                ]);
            }
            
            // Format booking data for response
            $bookingData = $this->formatBookingData($booking, $bookingType);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $bookingData,
                    'service_request' => [
                        'id' => $serviceRequest->id,
                        'uuid' => $serviceRequest->uuid,
                        'status' => $serviceRequest->status
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading booking details for service request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'provider_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load booking details'
            ], 500);
        }
    }
    
    /**
     * Format booking data based on booking type
     */
    private function formatBookingData($booking, string $bookingType): array
    {
        switch ($bookingType) {
            case 'hotel':
                return [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'hotel_name' => $booking->hotel->name,
                    'room_number' => $booking->room->room_number,
                    'guest_name' => $booking->guest_name,
                    'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                    'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                    'nights' => $booking->nights,
                    'total_amount' => $booking->total_amount,
                    'currency' => $booking->currency,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'view_url' => route('b2b.hotel-provider.bookings.show', $booking->id)
                ];
                
            // Add other booking types when implemented
            default:
                return [
                    'id' => $booking->id ?? null,
                    'type' => $bookingType,
                    'status' => 'unknown'
                ];
        }
    }
    
    /**
     * Get booking status for a service request (for list view)
     */
    private function getBookingStatusForRequest(ServiceRequest $request): array
    {
        if (!isset($request->metadata['booking_created']) || !$request->metadata['booking_created']) {
            return [
                'has_booking' => false,
                'status' => 'no_booking',
                'message' => 'No booking created'
            ];
        }
        
        return [
            'has_booking' => true,
            'id' => $request->metadata['booking_id'] ?? null,
            'type' => $request->metadata['booking_type'] ?? 'unknown',
            'reference' => $request->metadata['booking_reference'] ?? null,
            'created_at' => $request->metadata['booking_created_at'] ?? null,
            'status' => 'created',
            'message' => 'Booking created successfully'
        ];
    }
    
    /**
     * Update package draft status when service request is approved
     */
    private function updatePackageDraftStatus(ServiceRequest $serviceRequest): void
    {
        try {
            // Check if this service request is from a package draft
            if (!isset($serviceRequest->metadata['is_draft_package']) || 
                !$serviceRequest->metadata['is_draft_package'] ||
                !isset($serviceRequest->metadata['draft_id'])) {
                return;
            }
            
            $draftId = $serviceRequest->metadata['draft_id'];
            $draft = \App\Models\PackageDraft::find($draftId);
            
            if (!$draft) {
                Log::warning('Package draft not found for service request', [
                    'service_request_id' => $serviceRequest->id,
                    'draft_id' => $draftId
                ]);
                return;
            }
            
            // Get current draft data
            $draftData = $draft->draft_data ?? [];
            $stepStatus = $draft->step_status ?? [];
            
            // Update provider approval status for this service request
            if (!isset($draftData['provider_approvals'])) {
                $draftData['provider_approvals'] = [];
            }
            
            $draftData['provider_approvals'][$serviceRequest->id] = [
                'provider_id' => $serviceRequest->provider_id,
                'provider_type' => $serviceRequest->provider_type,
                'status' => 'approved',
                'approved_at' => now()->toDateTimeString(),
                'item_id' => $serviceRequest->item_id,
                'offered_price' => $serviceRequest->offered_price,
                'currency' => $serviceRequest->currency
            ];
            
            // Update the selected providers status in draft_data
            $this->updateSelectedProvidersStatus($draftData, $serviceRequest);
            
            // Check if all required service requests for this draft are approved
            $allServiceRequests = ServiceRequest::where('metadata->is_draft_package', true)
                ->where('metadata->draft_id', $draftId)
                ->get();
                
            $approvedCount = $allServiceRequests->where('status', ServiceRequest::STATUS_APPROVED)->count();
            $totalCount = $allServiceRequests->count();
            
            // Update step status for step 3 (provider selection)
            if (!isset($stepStatus[3])) {
                $stepStatus[3] = [];
            }
            
            $stepStatus[3]['provider_approvals'] = [
                'approved' => $approvedCount,
                'total' => $totalCount,
                'percentage' => $totalCount > 0 ? round(($approvedCount / $totalCount) * 100) : 0,
                'last_updated' => now()->toDateTimeString()
            ];
            
            // If all service requests are approved, mark step 3 as complete
            if ($approvedCount === $totalCount && $totalCount > 0) {
                $stepStatus[3]['status'] = 'completed';
                $stepStatus[3]['completed_at'] = now()->toDateTimeString();
                
                // If we're still on step 3, move to step 4
                if ($draft->current_step <= 3) {
                    $draft->current_step = 4;
                }
            }
            
            // Update the draft
            $draft->update([
                'draft_data' => $draftData,
                'step_status' => $stepStatus,
                'last_accessed_at' => now()
            ]);
            
            Log::info('Updated package draft status', [
                'draft_id' => $draftId,
                'service_request_id' => $serviceRequest->id,
                'approved_count' => $approvedCount,
                'total_count' => $totalCount,
                'current_step' => $draft->current_step
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating package draft status', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }
    
    /**
     * Update the selected providers status in draft_data when a service request is approved
     */
    private function updateSelectedProvidersStatus(array &$draftData, ServiceRequest $serviceRequest): void
    {
        try {
            $providerType = $serviceRequest->provider_type;
            $itemId = $serviceRequest->item_id;
            $serviceRequestId = $serviceRequest->id;
            
            // Determine the key based on provider type
            $selectedKey = match($providerType) {
                'hotel' => 'selected_hotels',
                'flight' => 'selected_flights',
                'transport' => 'selected_transport',
                default => null
            };
            
            if (!$selectedKey || !isset($draftData[$selectedKey])) {
                Log::info('No selected providers data found to update', [
                    'provider_type' => $providerType,
                    'selected_key' => $selectedKey,
                    'draft_keys' => array_keys($draftData)
                ]);
                return;
            }
            
            // Find and update the provider in the selected providers array
            $updated = false;
            foreach ($draftData[$selectedKey] as &$provider) {
                // Match by item_id or service_request_id (ensure string comparison)
                if ((string)$provider['id'] == (string)$itemId || 
                    (isset($provider['service_request_id']) && (string)$provider['service_request_id'] == (string)$serviceRequestId)) {
                    
                    // Update the provider status
                    $provider['service_request_status'] = 'approved';
                    $provider['approved_at'] = now()->toDateTimeString();
                    $provider['offered_price'] = $serviceRequest->offered_price;
                    $provider['currency'] = $serviceRequest->currency;
                    
                    // Add provider response details if available
                    if ($serviceRequest->provider_notes) {
                        $provider['provider_notes'] = $serviceRequest->provider_notes;
                    }
                    
                    if (isset($serviceRequest->terms_and_conditions) && $serviceRequest->terms_and_conditions) {
                        $provider['terms_conditions'] = $serviceRequest->terms_and_conditions;
                    }
                    
                    $updated = true;
                    
                    Log::info('Updated selected provider status in draft', [
                        'service_request_id' => $serviceRequest->id,
                        'provider_type' => $providerType,
                        'item_id' => $itemId,
                        'new_status' => 'approved',
                        'offered_price' => $serviceRequest->offered_price
                    ]);
                    
                    break;
                }
            }
            
            if (!$updated) {
                Log::warning('Could not find matching provider to update in draft data', [
                    'service_request_id' => $serviceRequest->id,
                    'provider_type' => $providerType,
                    'item_id' => $itemId,
                    'selected_providers_count' => count($draftData[$selectedKey])
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error updating selected providers status in draft', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }
    
    /**
     * Update package draft status when service request is rejected
     */
    private function updatePackageDraftStatusForRejection(ServiceRequest $serviceRequest): void
    {
        try {
            // Check if this service request is from a package draft
            if (!isset($serviceRequest->metadata['is_draft_package']) || 
                !$serviceRequest->metadata['is_draft_package'] ||
                !isset($serviceRequest->metadata['draft_id'])) {
                return;
            }
            
            $draftId = $serviceRequest->metadata['draft_id'];
            $draft = \App\Models\PackageDraft::find($draftId);
            
            if (!$draft) {
                Log::warning('Package draft not found for rejected service request', [
                    'service_request_id' => $serviceRequest->id,
                    'draft_id' => $draftId
                ]);
                return;
            }
            
            // Get current draft data
            $draftData = $draft->draft_data ?? [];
            
            // Update provider approval status for rejection
            if (!isset($draftData['provider_approvals'])) {
                $draftData['provider_approvals'] = [];
            }
            
            $draftData['provider_approvals'][$serviceRequest->id] = [
                'provider_id' => $serviceRequest->provider_id,
                'provider_type' => $serviceRequest->provider_type,
                'status' => 'rejected',
                'rejected_at' => now()->toDateTimeString(),
                'item_id' => $serviceRequest->item_id,
                'rejection_reason' => $serviceRequest->rejection_reason
            ];
            
            // Update the selected providers status in draft_data
            $this->updateSelectedProvidersStatusForRejection($draftData, $serviceRequest);
            
            // Update step status
            $stepStatus = $draft->step_status ?? [];
            
            if (!isset($stepStatus[3])) {
                $stepStatus[3] = [];
            }
            
            // Get all service requests for this draft
            $allServiceRequests = ServiceRequest::where('metadata->is_draft_package', true)
                ->where('metadata->draft_id', $draftId)
                ->get();
                
            $approvedCount = $allServiceRequests->where('status', ServiceRequest::STATUS_APPROVED)->count();
            $rejectedCount = $allServiceRequests->where('status', ServiceRequest::STATUS_REJECTED)->count();
            $totalCount = $allServiceRequests->count();
            
            $stepStatus[3]['provider_approvals'] = [
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
                'total' => $totalCount,
                'percentage' => $totalCount > 0 ? round(($approvedCount / $totalCount) * 100) : 0,
                'last_updated' => now()->toDateTimeString()
            ];
            
            // Update the draft
            $draft->update([
                'draft_data' => $draftData,
                'step_status' => $stepStatus,
                'last_accessed_at' => now()
            ]);
            
            Log::info('Updated package draft status for rejection', [
                'draft_id' => $draftId,
                'service_request_id' => $serviceRequest->id,
                'approved_count' => $approvedCount,
                'rejected_count' => $rejectedCount,
                'total_count' => $totalCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating package draft status for rejection', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }
    
    /**
     * Update the selected providers status for rejection in draft_data
     */
    private function updateSelectedProvidersStatusForRejection(array &$draftData, ServiceRequest $serviceRequest): void
    {
        try {
            $providerType = $serviceRequest->provider_type;
            $itemId = $serviceRequest->item_id;
            $serviceRequestId = $serviceRequest->id;
            
            // Determine the key based on provider type
            $selectedKey = match($providerType) {
                'hotel' => 'selected_hotels',
                'flight' => 'selected_flights', 
                'transport' => 'selected_transport',
                default => null
            };
            
            if (!$selectedKey || !isset($draftData[$selectedKey])) {
                return;
            }
            
            // Find and update the provider in the selected providers array
            foreach ($draftData[$selectedKey] as &$provider) {
                // Match by item_id or service_request_id (ensure string comparison)
                if ((string)$provider['id'] == (string)$itemId || 
                    (isset($provider['service_request_id']) && (string)$provider['service_request_id'] == (string)$serviceRequestId)) {
                    
                    // Update the provider status
                    $provider['service_request_status'] = 'rejected';
                    $provider['rejected_at'] = now()->toDateTimeString();
                    $provider['rejection_reason'] = $serviceRequest->rejection_reason;
                    
                    // Add provider response details if available
                    if ($serviceRequest->provider_notes) {
                        $provider['provider_notes'] = $serviceRequest->provider_notes;
                    }
                    
                    break;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error updating selected providers status for rejection in draft', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get available rooms for a hotel service request
     */
    public function getAvailableRooms($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $serviceRequest = ServiceRequest::where('provider_id', $user->id)
                ->where('id', $id)
                ->where('provider_type', 'hotel')
                ->firstOrFail();
            
            if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room availability can only be checked for pending requests'
                ], 422);
            }
            
            $availabilityResult = $this->roomAvailabilityService->getAvailableRoomsForServiceRequest($serviceRequest);
            
            return response()->json($availabilityResult);
            
        } catch (\Exception $e) {
            Log::error('Error getting available rooms for service request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'provider_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check room availability'
            ], 500);
        }
    }
    
    /**
     * Assign a specific room to a service request (before approval)
     */
    public function assignRoom(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'room_id' => 'required|integer|exists:rooms,id'
            ]);
            
            $user = Auth::user();
            
            $serviceRequest = ServiceRequest::where('provider_id', $user->id)
                ->where('id', $id)
                ->where('provider_type', 'hotel')
                ->firstOrFail();
            
            if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rooms can only be assigned to pending requests'
                ], 422);
            }
            
            $assignmentResult = $this->roomAvailabilityService->assignRoomToServiceRequest(
                $serviceRequest, 
                $validated['room_id']
            );
            
            if ($assignmentResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Room assigned successfully',
                    'room' => $assignmentResult['room']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $assignmentResult['message'],
                    'error_code' => $assignmentResult['error_code'] ?? null
                ], 422);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error assigning room to service request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'room_id' => $request->input('room_id'),
                'provider_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign room'
            ], 500);
        }
    }
    
    /**
     * Get room assignment status for a service request
     */
    public function getRoomAssignmentStatus($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $serviceRequest = ServiceRequest::where('provider_id', $user->id)
                ->where('id', $id)
                ->where('provider_type', 'hotel')
                ->firstOrFail();
            
            $assignmentStatus = $this->roomAvailabilityService->getRoomAssignmentStatus($serviceRequest);
            
            return response()->json([
                'success' => true,
                'assignment' => $assignmentStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting room assignment status', [
                'error' => $e->getMessage(),
                'request_id' => $id,
                'provider_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get room assignment status'
            ], 500);
        }
    }
    
    /**
     * Get hotel occupancy statistics
     */
    public function getHotelOccupancyStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'hotel_id' => 'required|integer|exists:hotels,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);
            
            // Verify the hotel belongs to the current provider
            $hotel = \App\Models\Hotel::where('id', $validated['hotel_id'])
                ->where('provider_id', $user->id)
                ->first();
                
            if (!$hotel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel not found or access denied'
                ], 404);
            }
            
            $startDate = isset($validated['start_date']) ? \Carbon\Carbon::parse($validated['start_date']) : null;
            $endDate = isset($validated['end_date']) ? \Carbon\Carbon::parse($validated['end_date']) : null;
            
            $occupancyStats = $this->roomAvailabilityService->getHotelOccupancyStats(
                $validated['hotel_id'],
                $startDate,
                $endDate
            );
            
            return response()->json($occupancyStats);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error getting hotel occupancy stats', [
                'error' => $e->getMessage(),
                'provider_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get occupancy statistics'
            ], 500);
        }
    }
}
