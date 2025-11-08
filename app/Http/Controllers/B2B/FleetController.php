<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\MaintenanceRecord;
use App\Models\VehicleAssignment;
use App\Models\ServiceRequest;
use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * FleetController
 * 
 * Manages fleet operations including vehicles, drivers, maintenance, and assignments
 */
class FleetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role.redirect']);
    }

    /*
    |--------------------------------------------------------------------------
    | VEHICLES MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Display vehicles list
     */
    public function vehiclesIndex(): View
    {
        $provider = Auth::user();

        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }

        $vehicles = Vehicle::where('provider_id', $provider->id)
                          ->with(['activeDrivers', 'maintenanceRecords'])
                          ->latest()
                          ->paginate(15);

        $stats = [
            'total_vehicles' => Vehicle::where('provider_id', $provider->id)->count(),
            'available' => Vehicle::where('provider_id', $provider->id)->where('status', Vehicle::STATUS_AVAILABLE)->count(),
            'assigned' => Vehicle::where('provider_id', $provider->id)->where('status', Vehicle::STATUS_ASSIGNED)->count(),
            'under_maintenance' => Vehicle::where('provider_id', $provider->id)->where('status', Vehicle::STATUS_UNDER_MAINTENANCE)->count(),
        ];

        return view('b2b.transport-provider.fleet.vehicles', compact('vehicles', 'stats'));
    }

    /**
     * Get single vehicle data
     */
    public function vehiclesShow(Vehicle $vehicle): JsonResponse
    {
        $provider = Auth::user();

        if ($vehicle->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json($vehicle->load('activeDrivers'));
    }

    /**
     * Store a new vehicle
     */
    public function vehiclesStore(Request $request): JsonResponse
    {
        $provider = Auth::user();

        if (!$provider->isTransportProvider()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'vehicle_name' => 'required|string|max:255',
            'vehicle_type' => ['required', Rule::in(Vehicle::VEHICLE_TYPES)],
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'capacity' => 'required|integer|min:1',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'specifications' => 'nullable|json',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['specifications'])) {
            $validated['specifications'] = json_decode($validated['specifications'], true);
        }

        $validated['provider_id'] = $provider->id;
        $validated['status'] = Vehicle::STATUS_AVAILABLE;

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully!',
            'vehicle' => $vehicle->load('activeDrivers')
        ]);
    }

    /**
     * Update vehicle
     */
    public function vehiclesUpdate(Request $request, Vehicle $vehicle): JsonResponse
    {
        $provider = Auth::user();

        if ($vehicle->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'vehicle_name' => 'required|string|max:255',
            'vehicle_type' => ['required', Rule::in(Vehicle::VEHICLE_TYPES)],
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $vehicle->id,
            'capacity' => 'required|integer|min:1',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'specifications' => 'nullable|json',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['specifications'])) {
            $validated['specifications'] = json_decode($validated['specifications'], true);
        }

        $vehicle->update($validated);
        $vehicle->updateStatusAutomatically();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully!',
            'vehicle' => $vehicle->fresh(['activeDrivers'])
        ]);
    }

    /**
     * Delete vehicle
     */
    public function vehiclesDestroy(Vehicle $vehicle): JsonResponse
    {
        $provider = Auth::user();

        if ($vehicle->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if vehicle has active assignments
        if ($vehicle->activeAssignments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete vehicle with active assignments'
            ], 422);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully!'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DRIVERS MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Display drivers list
     */
    public function driversIndex(): View
    {
        $provider = Auth::user();

        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }

        $drivers = Driver::where('provider_id', $provider->id)
                        ->with(['activeVehicles'])
                        ->latest()
                        ->paginate(15);

        $stats = [
            'total_drivers' => Driver::where('provider_id', $provider->id)->count(),
            'available' => Driver::where('provider_id', $provider->id)->where('availability_status', Driver::STATUS_AVAILABLE)->count(),
            'on_trip' => Driver::where('provider_id', $provider->id)->where('availability_status', Driver::STATUS_ON_TRIP)->count(),
            'license_expiring_soon' => Driver::where('provider_id', $provider->id)->licenseExpiring(30)->count(),
        ];

        return view('b2b.transport-provider.fleet.drivers', compact('drivers', 'stats'));
    }

    /**
     * Get single driver data
     */
    public function driversShow(Driver $driver): JsonResponse
    {
        $provider = Auth::user();

        if ($driver->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json($driver);
    }

    /**
     * Store a new driver
     */
    public function driversStore(Request $request): JsonResponse
    {
        $provider = Auth::user();

        if (!$provider->isTransportProvider()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'license_number' => 'required|string|unique:drivers,license_number',
            'license_expiry' => 'required|date|after:today',
            'license_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['provider_id'] = $provider->id;
        $validated['availability_status'] = Driver::STATUS_AVAILABLE;

        $driver = Driver::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver created successfully!',
            'driver' => $driver
        ]);
    }

    /**
     * Update driver
     */
    public function driversUpdate(Request $request, Driver $driver): JsonResponse
    {
        $provider = Auth::user();

        if ($driver->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'license_number' => 'required|string|unique:drivers,license_number,' . $driver->id,
            'license_expiry' => 'required|date',
            'license_type' => 'nullable|string|max:50',
            'availability_status' => ['nullable', Rule::in(Driver::STATUSES)],
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $driver->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver updated successfully!',
            'driver' => $driver->fresh()
        ]);
    }

    /**
     * Delete driver
     */
    public function driversDestroy(Driver $driver): JsonResponse
    {
        $provider = Auth::user();

        if ($driver->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if driver has active assignments
        if ($driver->activeAssignments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete driver with active assignments'
            ], 422);
        }

        $driver->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver deleted successfully!'
        ]);
    }

    /**
     * Assign driver to vehicle
     */
    public function assignDriverToVehicle(Request $request): JsonResponse
    {
        $provider = Auth::user();

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'assignment_type' => ['required', Rule::in(['primary', 'secondary'])],
            'assigned_from' => 'nullable|date',
            'assigned_until' => 'nullable|date|after:assigned_from',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $driver = Driver::findOrFail($validated['driver_id']);

        if ($vehicle->provider_id !== $provider->id || $driver->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if this assignment type already exists for this vehicle
        $existingAssignment = $vehicle->drivers()
                                    ->wherePivot('assignment_type', $validated['assignment_type'])
                                    ->wherePivot('is_active', true)
                                    ->first();

        if ($existingAssignment) {
            // Deactivate existing assignment
            $vehicle->drivers()->updateExistingPivot($existingAssignment->id, ['is_active' => false]);
        }

        // Create new assignment
        $vehicle->drivers()->attach($driver->id, [
            'assignment_type' => $validated['assignment_type'],
            'assigned_from' => $validated['assigned_from'] ?? now(),
            'assigned_until' => $validated['assigned_until'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Driver assigned successfully!',
            'vehicle' => $vehicle->fresh(['activeDrivers'])
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MAINTENANCE MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Display maintenance records
     */
    public function maintenanceIndex(): View
    {
        $provider = Auth::user();

        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }

        $maintenanceRecords = MaintenanceRecord::where('provider_id', $provider->id)
                                              ->with('vehicle')
                                              ->latest('maintenance_date')
                                              ->paginate(15);

        $stats = [
            'total_records' => MaintenanceRecord::where('provider_id', $provider->id)->count(),
            'scheduled' => MaintenanceRecord::where('provider_id', $provider->id)->scheduled()->count(),
            'in_progress' => MaintenanceRecord::where('provider_id', $provider->id)->inProgress()->count(),
            'upcoming' => MaintenanceRecord::where('provider_id', $provider->id)->upcoming(7)->count(),
            'overdue' => MaintenanceRecord::where('provider_id', $provider->id)->overdue()->count(),
        ];

        $vehicles = Vehicle::where('provider_id', $provider->id)->active()->get();

        return view('b2b.transport-provider.fleet.maintenance', compact('maintenanceRecords', 'stats', 'vehicles'));
    }

    /**
     * Store maintenance record
     */
    public function maintenanceStore(Request $request): JsonResponse
    {
        $provider = Auth::user();

        if (!$provider->isTransportProvider()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'maintenance_type' => ['required', Rule::in(MaintenanceRecord::TYPES)],
            'maintenance_date' => 'required|date',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'service_provider' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date|after:maintenance_date',
            'notes' => 'nullable|string',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated['provider_id'] = $provider->id;
        $validated['status'] = MaintenanceRecord::STATUS_SCHEDULED;
        $validated['currency'] = 'USD'; // Default currency

        $maintenance = MaintenanceRecord::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance record created successfully!',
            'maintenance' => $maintenance->load('vehicle')
        ]);
    }

    /**
     * Update maintenance status
     */
    public function maintenanceUpdateStatus(Request $request, MaintenanceRecord $maintenance): JsonResponse
    {
        $provider = Auth::user();

        if ($maintenance->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(MaintenanceRecord::STATUSES)],
            'notes' => 'nullable|string',
        ]);

        $maintenance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance status updated successfully!',
            'maintenance' => $maintenance->fresh(['vehicle'])
        ]);
    }

    /**
     * Delete maintenance record
     */
    public function maintenanceDestroy(MaintenanceRecord $maintenance): JsonResponse
    {
        $provider = Auth::user();

        if ($maintenance->provider_id !== $provider->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $maintenance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance record deleted successfully!'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VEHICLE ASSIGNMENT & AVAILABILITY
    |--------------------------------------------------------------------------
    */

    /**
     * Check availability for date range
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $provider = Auth::user();

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'vehicle_type' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $query = Vehicle::where('provider_id', $provider->id)
                       ->where('is_active', true);

        if (!empty($validated['vehicle_type'])) {
            $query->where('vehicle_type', $validated['vehicle_type']);
        }

        if (!empty($validated['capacity'])) {
            $query->where('capacity', '>=', $validated['capacity']);
        }

        $vehicles = $query->get();

        $availableVehicles = [];
        $availableDrivers = [];

        foreach ($vehicles as $vehicle) {
            if ($vehicle->isAvailableForDateRange($validated['start_date'], $validated['end_date'])) {
                $availableVehicles[] = $vehicle;
            }
        }

        $drivers = Driver::where('provider_id', $provider->id)
                        ->where('is_active', true)
                        ->get();

        foreach ($drivers as $driver) {
            if ($driver->isAvailableForDateRange($validated['start_date'], $validated['end_date'])) {
                $availableDrivers[] = $driver;
            }
        }

        return response()->json([
            'success' => true,
            'available_vehicles' => $availableVehicles,
            'available_drivers' => $availableDrivers,
        ]);
    }

    /**
     * Assign vehicle and drivers to service request
     */
    public function assignToServiceRequest(Request $request): JsonResponse
    {
        $provider = Auth::user();

        $validated = $request->validate([
            'service_request_id' => 'required|exists:service_requests,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'primary_driver_id' => 'nullable|exists:drivers,id',
            'secondary_driver_id' => 'nullable|exists:drivers,id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $serviceRequest = ServiceRequest::findOrFail($validated['service_request_id']);
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

            // Verify ownership and authorization
            if ($serviceRequest->provider_id !== $provider->id || $vehicle->provider_id !== $provider->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check if service request is approved
            if (!$serviceRequest->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service request must be approved before assignment'
                ], 422);
            }

            // Check vehicle availability
            if (!$vehicle->isAvailableForDateRange($serviceRequest->start_date, $serviceRequest->end_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle is not available for the requested dates'
                ], 422);
            }

            // Check driver availability if provided
            if (!empty($validated['primary_driver_id'])) {
                $primaryDriver = Driver::findOrFail($validated['primary_driver_id']);
                if (!$primaryDriver->isAvailableForDateRange($serviceRequest->start_date, $serviceRequest->end_date)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Primary driver is not available for the requested dates'
                    ], 422);
                }
            }

            if (!empty($validated['secondary_driver_id'])) {
                $secondaryDriver = Driver::findOrFail($validated['secondary_driver_id']);
                if (!$secondaryDriver->isAvailableForDateRange($serviceRequest->start_date, $serviceRequest->end_date)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Secondary driver is not available for the requested dates'
                    ], 422);
                }
            }

            // Get active allocation
            $allocation = $serviceRequest->allocations()->where('status', Allocation::STATUS_ACTIVE)->first();

            // Create vehicle assignment
            $assignment = VehicleAssignment::create([
                'service_request_id' => $serviceRequest->id,
                'allocation_id' => $allocation ? $allocation->id : null,
                'vehicle_id' => $vehicle->id,
                'primary_driver_id' => $validated['primary_driver_id'] ?? null,
                'secondary_driver_id' => $validated['secondary_driver_id'] ?? null,
                'provider_id' => $provider->id,
                'start_date' => $serviceRequest->start_date,
                'end_date' => $serviceRequest->end_date,
                'status' => VehicleAssignment::STATUS_SCHEDULED,
                'notes' => $validated['notes'] ?? null,
                'metadata' => [
                    'service_request_uuid' => $serviceRequest->uuid,
                    'guest_count' => $serviceRequest->requested_quantity,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle and drivers assigned successfully!',
                'assignment' => $assignment->load(['vehicle', 'primaryDriver', 'secondaryDriver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get calendar data for availability view
     */
    public function calendarData(Request $request): JsonResponse
    {
        $provider = Auth::user();

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'type' => 'nullable|in:vehicle,driver',
        ]);

        $events = [];

        // Get vehicle assignments
        if (!$validated['type'] || $validated['type'] === 'vehicle') {
            $assignments = VehicleAssignment::where('provider_id', $provider->id)
                                          ->with(['vehicle', 'primaryDriver', 'secondaryDriver'])
                                          ->forDateRange($validated['start'], $validated['end'])
                                          ->get();

            foreach ($assignments as $assignment) {
                $events[] = [
                    'id' => 'assignment-' . $assignment->id,
                    'title' => $assignment->vehicle->vehicle_name . ' - ' . $assignment->status_label,
                    'start' => $assignment->start_date->format('Y-m-d'),
                    'end' => $assignment->end_date->addDay()->format('Y-m-d'),
                    'backgroundColor' => $this->getEventColor($assignment->status),
                    'borderColor' => $this->getEventColor($assignment->status),
                    'extendedProps' => [
                        'type' => 'assignment',
                        'vehicle' => $assignment->vehicle->vehicle_name,
                        'driver' => $assignment->primaryDriver ? $assignment->primaryDriver->name : 'N/A',
                        'status' => $assignment->status,
                    ],
                ];
            }
        }

        // Get maintenance records
        $maintenanceRecords = MaintenanceRecord::where('provider_id', $provider->id)
                                            ->with('vehicle')
                                            ->whereBetween('maintenance_date', [$validated['start'], $validated['end']])
                                            ->whereIn('status', [MaintenanceRecord::STATUS_SCHEDULED, MaintenanceRecord::STATUS_IN_PROGRESS])
                                            ->get();

        foreach ($maintenanceRecords as $maintenance) {
            $events[] = [
                'id' => 'maintenance-' . $maintenance->id,
                'title' => $maintenance->vehicle->vehicle_name . ' - Maintenance',
                'start' => $maintenance->maintenance_date->format('Y-m-d'),
                'backgroundColor' => '#ffc107',
                'borderColor' => '#ffc107',
                'extendedProps' => [
                    'type' => 'maintenance',
                    'vehicle' => $maintenance->vehicle->vehicle_name,
                    'maintenance_type' => $maintenance->type_label,
                    'description' => $maintenance->description,
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Get event color based on status
     */
    private function getEventColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#17a2b8',
            'in_progress' => '#007bff',
            'completed' => '#28a745',
            'cancelled' => '#6c757d',
            default => '#6c757d',
        };
    }
}
