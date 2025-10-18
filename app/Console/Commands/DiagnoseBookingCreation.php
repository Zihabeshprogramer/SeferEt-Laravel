<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceRequest;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingIntegrationService;
use App\Services\RoomAvailabilityService;

class DiagnoseBookingCreation extends Command
{
    protected $signature = 'diagnose:booking-creation {service_request_id?}';
    protected $description = 'Diagnose booking creation issues for a service request';

    public function handle()
    {
        $this->info('🔍 Diagnosing Booking Creation Issues');
        $this->newLine();

        $serviceRequestId = $this->argument('service_request_id');
        
        if (!$serviceRequestId) {
            // Get the most recent failed service request
            $serviceRequest = ServiceRequest::where('provider_type', 'hotel')
                ->where('status', 'approved')
                ->whereJsonContains('metadata->booking_created', false)
                ->latest()
                ->first();
                
            if (!$serviceRequest) {
                $this->warn('No recent service request found with booking creation failure.');
                $this->info('Please provide a service request ID: php artisan diagnose:booking-creation {id}');
                return 1;
            }
            
            $this->info("Found recent failed service request: {$serviceRequest->id}");
        } else {
            $serviceRequest = ServiceRequest::find($serviceRequestId);
            if (!$serviceRequest) {
                $this->error("Service request {$serviceRequestId} not found.");
                return 1;
            }
        }

        $this->info("📋 Service Request Details:");
        $this->info("   ID: {$serviceRequest->id}");
        $this->info("   UUID: {$serviceRequest->uuid}");
        $this->info("   Status: {$serviceRequest->status}");
        $this->info("   Provider Type: {$serviceRequest->provider_type}");
        $this->info("   Hotel ID: {$serviceRequest->item_id}");
        $this->info("   Start Date: {$serviceRequest->start_date?->format('Y-m-d')}");
        $this->info("   End Date: {$serviceRequest->end_date?->format('Y-m-d')}");
        $this->info("   Requested Quantity: {$serviceRequest->requested_quantity}");
        $this->newLine();

        // Check if hotel exists
        $hotel = Hotel::find($serviceRequest->item_id);
        if (!$hotel) {
            $this->error("❌ Hotel not found with ID: {$serviceRequest->item_id}");
            return 1;
        }
        
        $this->info("🏨 Hotel Details:");
        $this->info("   Name: {$hotel->name}");
        $this->info("   Status: {$hotel->status}");
        $this->info("   Active: " . ($hotel->is_active ? 'Yes' : 'No'));
        $this->info("   Provider ID: {$hotel->provider_id}");
        $this->newLine();

        // Check rooms
        $rooms = $hotel->rooms()->where('is_active', true)->get();
        $this->info("🏠 Room Details:");
        $this->info("   Total Active Rooms: " . count($rooms));
        
        if (count($rooms) === 0) {
            $this->error("❌ No active rooms found for this hotel!");
            return 1;
        }
        
        foreach ($rooms as $room) {
            $this->info("   Room {$room->room_number}: {$room->name} (Max: {$room->max_occupancy}, Available: " . ($room->is_available ? 'Yes' : 'No') . ")");
        }
        $this->newLine();

        // Check agent
        $agent = User::find($serviceRequest->agent_id);
        if (!$agent) {
            $this->error("❌ Agent not found with ID: {$serviceRequest->agent_id}");
            return 1;
        }
        
        $this->info("👤 Agent Details:");
        $this->info("   Name: {$agent->name}");
        $this->info("   Email: {$agent->email}");
        $this->info("   Status: {$agent->status}");
        $this->newLine();

        // Check room availability
        $roomService = new RoomAvailabilityService();
        $availability = $roomService->getAvailableRoomsForServiceRequest($serviceRequest);
        
        $this->info("🔍 Room Availability Check:");
        if ($availability['success']) {
            $this->info("   Available Rooms: " . count($availability['rooms']));
            if (count($availability['rooms']) === 0) {
                $this->error("❌ No rooms available for the requested dates!");
            } else {
                foreach ($availability['rooms'] as $room) {
                    $this->info("   - Room {$room['room_number']}: {$room['name']} ($" . $room['base_price'] . "/night)");
                }
            }
        } else {
            $this->error("❌ Room availability check failed: " . $availability['message']);
        }
        $this->newLine();

        // Check metadata for room assignment
        if (isset($serviceRequest->metadata['assigned_room_id'])) {
            $assignedRoom = Room::find($serviceRequest->metadata['assigned_room_id']);
            $this->info("📌 Assigned Room:");
            if ($assignedRoom) {
                $this->info("   Room: {$assignedRoom->formatted_room_number} - {$assignedRoom->name}");
                $available = $roomService->isRoomAvailableForDates($assignedRoom, $serviceRequest->start_date, $serviceRequest->end_date);
                $this->info("   Available: " . ($available ? 'Yes' : 'No'));
            } else {
                $this->error("❌ Assigned room not found!");
            }
            $this->newLine();
        }

        // Try to create booking and catch the specific error
        $this->info("🔧 Attempting to create booking...");
        try {
            $bookingService = new BookingIntegrationService();
            $result = $bookingService->convertToBooking($serviceRequest);
            
            if ($result['success']) {
                $this->info("✅ Booking creation succeeded!");
                $this->info("   Booking ID: " . $result['booking_id']);
            } else {
                $this->error("❌ Booking creation failed:");
                $this->error("   Message: " . $result['message']);
                $this->error("   Error Code: " . ($result['error_code'] ?? 'Unknown'));
                
                if (isset($result['debug_info'])) {
                    $this->error("   Exception: " . $result['debug_info']['exception_class']);
                    $this->error("   File: " . $result['debug_info']['file'] . ':' . $result['debug_info']['line']);
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception during booking creation:");
            $this->error("   Message: " . $e->getMessage());
            $this->error("   File: " . $e->getFile() . ':' . $e->getLine());
            $this->error("   Class: " . get_class($e));
        }
        
        return 0;
    }
}