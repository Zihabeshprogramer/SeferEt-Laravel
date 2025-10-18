<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\Allocation;
use App\Models\InventoryCalendar;
use App\Models\Flight;
use App\Models\Hotel;
use App\Services\FlightAvailabilityService;
use App\Services\RoomAvailabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ApprovalService
{
    /**
     * Approve a service request with atomic allocation
     */
    public function approveRequest(ServiceRequest $serviceRequest, array $options = []): array
    {
        try {
            return DB::transaction(function () use ($serviceRequest, $options) {
                
                // Lock the service request for update to prevent concurrent approvals
                $serviceRequest = ServiceRequest::lockForUpdate()->find($serviceRequest->id);
                
                if (!$serviceRequest) {
                    return [
                        'success' => false,
                        'message' => 'Service request not found',
                        'error_code' => 'REQUEST_NOT_FOUND'
                    ];
                }

                // Validate request can still be approved
                if (!$this->validateApprovalPreconditions($serviceRequest)) {
                    return [
                        'success' => false,
                        'message' => 'Request cannot be approved in current state',
                        'error_code' => 'INVALID_STATE'
                    ];
                }

                // Check and reserve availability atomically
                $availabilityResult = $this->checkAndReserveAvailability($serviceRequest, $options);
                
                if (!$availabilityResult['success']) {
                    return $availabilityResult;
                }

                // Update service request status
                $serviceRequest->approve([
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'approval_notes' => $options['notes'] ?? null,
                    'pricing' => $options['pricing'] ?? null,
                    'terms_conditions' => $options['terms_conditions'] ?? null,
                    'auto_approved' => $options['auto_approved'] ?? false
                ]);

                // Create allocation record
                $allocation = $this->createAllocation($serviceRequest, $availabilityResult['allocation_data'], $options);

                Log::info('Service request approved successfully', [
                    'service_request_id' => $serviceRequest->id,
                    'allocation_id' => $allocation->id,
                    'approved_by' => auth()->id()
                ]);

                return [
                    'success' => true,
                    'allocation' => $allocation,
                    'message' => 'Request approved and inventory allocated successfully'
                ];

            }, 5); // 5 retries for deadlock resolution

        } catch (\Illuminate\Database\QueryException $e) {
            if ($this->isDeadlockException($e)) {
                Log::warning('Deadlock detected during approval, retrying...', [
                    'service_request_id' => $serviceRequest->id,
                    'error' => $e->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Approval failed due to concurrent request. Please retry.',
                    'error_code' => 'DEADLOCK_RETRY'
                ];
            }

            Log::error('Database error during approval', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Database error during approval',
                'error_code' => 'DATABASE_ERROR'
            ];

        } catch (\Exception $e) {
            Log::error('Unexpected error during approval', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Unexpected error during approval process',
                'error_code' => 'UNEXPECTED_ERROR'
            ];
        }
    }

    /**
     * Check availability and reserve inventory atomically
     */
    private function checkAndReserveAvailability(ServiceRequest $serviceRequest, array $options): array
    {
        // For flights, use direct flight seat management
        if ($serviceRequest->provider_type === ServiceRequest::PROVIDER_FLIGHT) {
            return $this->checkAndReserveFlightSeats($serviceRequest, $options);
        }

        // For hotels, use enhanced hotel room management
        if ($serviceRequest->provider_type === ServiceRequest::PROVIDER_HOTEL) {
            return $this->checkAndReserveHotelRooms($serviceRequest, $options);
        }

        // For other types, use the existing inventory calendar approach
        return $this->checkAndReserveInventoryCalendar($serviceRequest, $options);
    }

    /**
     * Check and reserve flight seats directly
     */
    private function checkAndReserveFlightSeats(ServiceRequest $serviceRequest, array $options): array
    {
        $requestedSeats = $serviceRequest->requested_quantity ?? 1;
        
        try {
            // Get and lock the flight
            $flight = Flight::lockForUpdate()->find($serviceRequest->item_id);
            
            if (!$flight) {
                return [
                    'success' => false,
                    'message' => 'Flight not found',
                    'error_code' => 'FLIGHT_NOT_FOUND'
                ];
            }

            // Use FlightAvailabilityService for comprehensive validation
            $flightAvailabilityService = new FlightAvailabilityService();
            $validation = $flightAvailabilityService->validateBookingAvailability($flight, $requestedSeats);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Flight booking validation failed: ' . implode(', ', $validation['errors']),
                    'error_code' => 'FLIGHT_VALIDATION_FAILED',
                    'validation_errors' => $validation['errors']
                ];
            }

            // Reserve seats atomically
            if (!$flightAvailabilityService->reserveSeats($flight, $requestedSeats)) {
                return [
                    'success' => false,
                    'message' => 'Failed to reserve flight seats. Another booking may have occurred simultaneously.',
                    'error_code' => 'SEAT_RESERVATION_FAILED'
                ];
            }

            // Calculate pricing
            $flightClass = $options['flight_class'] ?? 'economy';
            $unitPrice = $options['pricing']['unit_price'] ?? $this->getFlightPrice($flight, $flightClass);
            $totalPrice = $unitPrice * $requestedSeats;

            Log::info('Flight seats reserved successfully', [
                'flight_id' => $flight->id,
                'seats_reserved' => $requestedSeats,
                'remaining_seats' => $flight->available_seats,
                'service_request_id' => $serviceRequest->id
            ]);

            return [
                'success' => true,
                'allocation_data' => [
                    'total_price' => $totalPrice,
                    'unit_price' => $unitPrice,
                    'commission_rate' => $options['pricing']['commission_rate'] ?? 0,
                    'flight_class' => $flightClass,
                    'seats_reserved' => $requestedSeats,
                    'flight_id' => $flight->id,
                    'flight_details' => [
                        'airline' => $flight->airline,
                        'flight_number' => $flight->flight_number,
                        'departure_datetime' => $flight->departure_datetime,
                        'arrival_datetime' => $flight->arrival_datetime,
                        'route' => $flight->route
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error during flight seat reservation', [
                'service_request_id' => $serviceRequest->id,
                'flight_id' => $serviceRequest->item_id,
                'requested_seats' => $requestedSeats,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reserve flight seats: ' . $e->getMessage(),
                'error_code' => 'FLIGHT_RESERVATION_ERROR'
            ];
        }
    }

    /**
     * Enhanced hotel room reservation with room availability service
     */
    private function checkAndReserveHotelRooms(ServiceRequest $serviceRequest, array $options): array
    {
        $requestedRooms = $serviceRequest->requested_quantity ?? 1;
        
        try {
            // Get the hotel
            $hotel = Hotel::find($serviceRequest->item_id);
            
            if (!$hotel) {
                return [
                    'success' => false,
                    'message' => 'Hotel not found',
                    'error_code' => 'HOTEL_NOT_FOUND'
                ];
            }

            // Use RoomAvailabilityService for validation
            $roomAvailabilityService = new RoomAvailabilityService();
            $availableRooms = $roomAvailabilityService->getAvailableRooms(
                $hotel->id,
                $serviceRequest->start_date,
                $serviceRequest->end_date,
                1, // guest count for filtering
                []
            );

            if ($availableRooms->count() < $requestedRooms) {
                return [
                    'success' => false,
                    'message' => "Insufficient rooms available. Requested: {$requestedRooms}, Available: {$availableRooms->count()}",
                    'error_code' => 'INSUFFICIENT_ROOMS'
                ];
            }

            // For hotels, we still use the inventory calendar approach but with enhanced validation
            return $this->checkAndReserveInventoryCalendar($serviceRequest, $options);

        } catch (\Exception $e) {
            Log::error('Error during hotel room validation', [
                'service_request_id' => $serviceRequest->id,
                'hotel_id' => $serviceRequest->item_id,
                'requested_rooms' => $requestedRooms,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to validate hotel availability: ' . $e->getMessage(),
                'error_code' => 'HOTEL_VALIDATION_ERROR'
            ];
        }
    }

    /**
     * Original inventory calendar approach for other provider types
     */
    private function checkAndReserveInventoryCalendar(ServiceRequest $serviceRequest, array $options): array
    {
        $startDate = Carbon::parse($serviceRequest->start_date);
        $endDate = Carbon::parse($serviceRequest->end_date);
        $requestedQuantity = $serviceRequest->requested_quantity;

        // Get all dates in the range
        // For hotels: exclude end date (checkout day)
        // For transport/flights: include end date for same-day services
        if ($serviceRequest->provider_type === 'hotel') {
            // Hotels: end_date is checkout (not included in stay)
            $dateRange = CarbonPeriod::create($startDate, $endDate->subDay())->toArray();
        } else {
            // Transport/Flight: can be same-day service
            if ($startDate->eq($endDate)) {
                // Same-day service
                $dateRange = [$startDate];
            } else {
                // Multi-day service (rare for transport, but possible)
                $dateRange = CarbonPeriod::create($startDate, $endDate)->toArray();
            }
        }
        
        $totalDays = count($dateRange);

        if ($totalDays === 0) {
            return [
                'success' => false,
                'message' => 'Invalid date range',
                'error_code' => 'INVALID_DATE_RANGE'
            ];
        }

        // Lock all inventory records for the date range to prevent concurrent allocations
        $queryEndDate = ($serviceRequest->provider_type === 'hotel') 
            ? $endDate->subDay()->format('Y-m-d')
            : $endDate->format('Y-m-d');
            
        $inventoryRecords = InventoryCalendar::lockForUpdate()
            ->where('provider_type', $serviceRequest->provider_type)
            ->where('item_id', $serviceRequest->item_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $queryEndDate])
            ->get()
            ->keyBy('date');

        // Check if we have inventory records for all required dates
        $missingDates = [];
        $insufficientDates = [];
        
        foreach ($dateRange as $date) {
            $dateStr = $date->format('Y-m-d');
            
            if (!isset($inventoryRecords[$dateStr])) {
                $missingDates[] = $dateStr;
                continue;
            }

            $inventory = $inventoryRecords[$dateStr];
            
            // Check available capacity (with optimistic locking)
            if ($inventory->available_capacity < $requestedQuantity) {
                $insufficientDates[] = [
                    'date' => $dateStr,
                    'requested' => $requestedQuantity,
                    'available' => $inventory->available_capacity
                ];
            }
        }

        // Handle missing inventory dates by auto-generating them
        if (!empty($missingDates)) {
            Log::info('Auto-generating missing inventory records', [
                'service_request_id' => $serviceRequest->id,
                'provider_type' => $serviceRequest->provider_type,
                'item_id' => $serviceRequest->item_id,
                'missing_dates' => $missingDates
            ]);
            
            // Auto-generate missing inventory records with default values
            $defaultCapacity = $this->getDefaultCapacityForProviderType($serviceRequest->provider_type);
            $basePrice = $this->getDefaultPriceForProviderType($serviceRequest->provider_type);
            
            foreach ($missingDates as $missingDate) {
                InventoryCalendar::updateOrCreate(
                    [
                        'provider_type' => $serviceRequest->provider_type,
                        'item_id' => $serviceRequest->item_id,
                        'date' => $missingDate,
                    ],
                    [
                        'total_capacity' => $defaultCapacity,
                        'available_capacity' => $defaultCapacity,
                        'allocated_capacity' => 0,
                        'blocked_capacity' => 0,
                        'base_price' => $basePrice,
                        'currency' => 'USD',
                        'is_available' => true,
                        'is_bookable' => true,
                        'version' => 1,
                        'last_updated_at' => now(),
                    ]
                );
            }
            
            // Refresh inventory records after auto-generation
            $inventoryRecords = InventoryCalendar::lockForUpdate()
                ->where('provider_type', $serviceRequest->provider_type)
                ->where('item_id', $serviceRequest->item_id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $queryEndDate])
                ->get()
                ->keyBy('date');
            
            // Re-check for insufficient capacity after auto-generation
            $insufficientDates = [];
            foreach ($dateRange as $date) {
                $dateStr = $date->format('Y-m-d');
                $inventory = $inventoryRecords[$dateStr] ?? null;
                
                if ($inventory && $inventory->available_capacity < $requestedQuantity) {
                    $insufficientDates[] = [
                        'date' => $dateStr,
                        'requested' => $requestedQuantity,
                        'available' => $inventory->available_capacity
                    ];
                }
            }
        }

        // Handle insufficient capacity
        if (!empty($insufficientDates)) {
            return [
                'success' => false,
                'message' => 'Insufficient capacity for requested dates',
                'error_code' => 'INSUFFICIENT_CAPACITY',
                'insufficient_dates' => $insufficientDates
            ];
        }

        // Atomically allocate capacity for all dates
        $allocationData = [];
        $totalPrice = 0;

        foreach ($inventoryRecords as $inventory) {
            // Use optimistic locking to update capacity
            $updated = InventoryCalendar::where('id', $inventory->id)
                ->where('version', $inventory->version)
                ->update([
                    'allocated_capacity' => DB::raw('allocated_capacity + ' . $requestedQuantity),
                    'available_capacity' => DB::raw('available_capacity - ' . $requestedQuantity),
                    'version' => DB::raw('version + 1')
                ]);

            if ($updated === 0) {
                // Optimistic lock failed - someone else updated this record
                return [
                    'success' => false,
                    'message' => 'Inventory was updated by another process. Please retry.',
                    'error_code' => 'OPTIMISTIC_LOCK_FAILED',
                    'failed_date' => $inventory->date
                ];
            }

            // Calculate pricing
            $unitPrice = $options['pricing']['unit_price'] ?? $inventory->base_price;
            $dailyPrice = $unitPrice * $requestedQuantity;
            $totalPrice += $dailyPrice;

            $allocationData[] = [
                'date' => $inventory->date,
                'unit_price' => $unitPrice,
                'daily_total' => $dailyPrice,
                'inventory_id' => $inventory->id
            ];
        }

        return [
            'success' => true,
            'allocation_data' => [
                'total_price' => $totalPrice,
                'unit_price' => $options['pricing']['unit_price'] ?? null,
                'commission_rate' => $options['pricing']['commission_rate'] ?? 0,
                'daily_allocations' => $allocationData
            ]
        ];
    }

    /**
     * Create allocation record
     */
    private function createAllocation(ServiceRequest $serviceRequest, array $allocationData, array $options): Allocation
    {
        $metadata = [];
        
        // For flights, store flight-specific data
        if ($serviceRequest->provider_type === ServiceRequest::PROVIDER_FLIGHT) {
            $metadata = [
                'flight_details' => $allocationData['flight_details'] ?? null,
                'flight_class' => $allocationData['flight_class'] ?? 'economy',
                'seats_reserved' => $allocationData['seats_reserved'] ?? $serviceRequest->requested_quantity,
                'approval_options' => $options
            ];
        } else {
            // For other provider types, use daily allocations
            $metadata = [
                'daily_allocations' => $allocationData['daily_allocations'] ?? [],
                'approval_options' => $options
            ];
        }

        $allocation = Allocation::create([
            'service_request_id' => $serviceRequest->id,
            'provider_id' => $serviceRequest->provider_id,
            'provider_type' => $serviceRequest->provider_type,
            'item_id' => $serviceRequest->item_id,
            'quantity' => $serviceRequest->requested_quantity,
            'start_date' => $serviceRequest->start_date,
            'end_date' => $serviceRequest->end_date,
            'allocated_price' => $allocationData['total_price'],
            'currency' => $serviceRequest->currency ?? 'USD',
            'commission_amount' => ($allocationData['total_price'] * $allocationData['commission_rate']) / 100,
            'status' => Allocation::STATUS_ACTIVE,
            'created_by' => auth()->id(),
            'allocated_at' => now(),
            'expires_at' => now()->addDays(7), // Default 7-day hold
            'metadata' => $metadata
        ]);

        return $allocation;
    }

    /**
     * Release allocation and restore inventory capacity
     */
    public function releaseAllocation(Allocation $allocation, array $options = []): array
    {
        try {
            return DB::transaction(function () use ($allocation, $options) {
                
                // Lock allocation for update
                $allocation = Allocation::lockForUpdate()->find($allocation->id);
                
                if (!$allocation || $allocation->status !== Allocation::STATUS_ACTIVE) {
                    return [
                        'success' => false,
                        'message' => 'Allocation not found or already released',
                        'error_code' => 'ALLOCATION_NOT_ACTIVE'
                    ];
                }

                // Get daily allocation data
                $dailyAllocations = $allocation->metadata['daily_allocations'] ?? [];
                
                // Release capacity for each day
                foreach ($dailyAllocations as $dayAllocation) {
                    InventoryCalendar::where('id', $dayAllocation['inventory_id'])
                        ->update([
                            'allocated_capacity' => DB::raw('allocated_capacity - ' . $allocation->quantity),
                            'available_capacity' => DB::raw('available_capacity + ' . $allocation->quantity),
                            'version' => DB::raw('version + 1')
                        ]);
                }

                // Update allocation status
                $allocation->release([
                    'released_by' => auth()->id(),
                    'released_at' => now(),
                    'release_reason' => $options['reason'] ?? 'Manual release'
                ]);

                Log::info('Allocation released successfully', [
                    'allocation_id' => $allocation->id,
                    'service_request_id' => $allocation->service_request_id,
                    'released_by' => auth()->id()
                ]);

                return [
                    'success' => true,
                    'message' => 'Allocation released successfully'
                ];

            }, 3);

        } catch (\Exception $e) {
            Log::error('Error releasing allocation', [
                'allocation_id' => $allocation->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to release allocation',
                'error_code' => 'RELEASE_ERROR'
            ];
        }
    }

    /**
     * Batch approve multiple service requests
     */
    public function batchApprove(array $serviceRequestIds, array $options = []): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($serviceRequestIds as $requestId) {
            try {
                $serviceRequest = ServiceRequest::find($requestId);
                
                if (!$serviceRequest) {
                    $results[] = [
                        'id' => $requestId,
                        'success' => false,
                        'message' => 'Service request not found'
                    ];
                    $failureCount++;
                    continue;
                }

                $result = $this->approveRequest($serviceRequest, $options);
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
                    'message' => 'Unexpected error: ' . $e->getMessage()
                ];
                $failureCount++;
            }
        }

        return [
            'total_processed' => count($serviceRequestIds),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ];
    }

    /**
     * Validate that service request can be approved
     */
    private function validateApprovalPreconditions(ServiceRequest $serviceRequest): bool
    {
        // Check current status
        if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
            return false;
        }

        // Check expiration
        if ($serviceRequest->expires_at && $serviceRequest->expires_at->isPast()) {
            return false;
        }

        // Check if dates are still valid
        if (Carbon::parse($serviceRequest->start_date)->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if exception is a deadlock
     */
    private function isDeadlockException(\Illuminate\Database\QueryException $e): bool
    {
        $errorCode = $e->errorInfo[1] ?? null;
        
        // MySQL deadlock error codes
        return in_array($errorCode, [1213, 1205]);
    }
    
    /**
     * Get default capacity for provider type
     */
    private function getDefaultCapacityForProviderType(string $providerType): int
    {
        return match($providerType) {
            'hotel' => 50,    // Default room availability
            'flight' => 150,  // Default seat availability
            'transport' => 10, // Default vehicle availability
            default => 20
        };
    }
    
    /**
     * Get default base price for provider type
     */
    private function getDefaultPriceForProviderType(string $providerType): float
    {
        return match($providerType) {
            'hotel' => 100.00,    // Default per room per night
            'flight' => 300.00,   // Default per seat
            'transport' => 50.00, // Default per passenger
            default => 100.00
        };
    }

    /**
     * Get availability summary for a provider's inventory
     */
    public function getAvailabilitySummary(string $providerType, int $itemId, Carbon $startDate, Carbon $endDate): array
    {
        $dateRange = CarbonPeriod::create($startDate, $endDate->copy()->subDay())->toArray();
        
        $inventoryRecords = InventoryCalendar::where('provider_type', $providerType)
            ->where('item_id', $itemId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->copy()->subDay()->format('Y-m-d')])
            ->get()
            ->keyBy('date');

        $summary = [
            'provider_type' => $providerType,
            'item_id' => $itemId,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'total_days' => count($dateRange),
            'available_days' => 0,
            'unavailable_days' => 0,
            'min_availability' => null,
            'max_availability' => null,
            'daily_availability' => []
        ];

        foreach ($dateRange as $date) {
            $dateStr = $date->format('Y-m-d');
            $inventory = $inventoryRecords[$dateStr] ?? null;
            
            if ($inventory) {
                $available = $inventory->available_capacity;
                $summary['daily_availability'][$dateStr] = [
                    'total_capacity' => $inventory->total_capacity,
                    'allocated_capacity' => $inventory->allocated_capacity,
                    'available_capacity' => $available,
                    'blocked_capacity' => $inventory->blocked_capacity,
                    'base_price' => $inventory->base_price
                ];
                
                if ($available > 0) {
                    $summary['available_days']++;
                    $summary['min_availability'] = $summary['min_availability'] === null ? 
                        $available : min($summary['min_availability'], $available);
                    $summary['max_availability'] = $summary['max_availability'] === null ? 
                        $available : max($summary['max_availability'], $available);
                } else {
                    $summary['unavailable_days']++;
                }
            } else {
                $summary['unavailable_days']++;
                $summary['daily_availability'][$dateStr] = [
                    'total_capacity' => 0,
                    'allocated_capacity' => 0,
                    'available_capacity' => 0,
                    'blocked_capacity' => 0,
                    'base_price' => null
                ];
            }
        }

        return $summary;
    }

    /**
     * Get flight price based on class
     */
    private function getFlightPrice(Flight $flight, string $flightClass = 'economy'): float
    {
        switch (strtolower($flightClass)) {
            case 'economy':
                return $flight->economy_price ?? $this->getDefaultPriceForProviderType('flight');
            case 'business':
                return $flight->business_price ?? $flight->economy_price ?? $this->getDefaultPriceForProviderType('flight');
            case 'first':
            case 'first_class':
                return $flight->first_class_price ?? $flight->business_price ?? $flight->economy_price ?? $this->getDefaultPriceForProviderType('flight');
            default:
                return $flight->economy_price ?? $this->getDefaultPriceForProviderType('flight');
        }
    }
}
