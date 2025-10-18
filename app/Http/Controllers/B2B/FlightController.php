<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

/**
 * FlightController
 * 
 * Handles flight operations for B2B travel agents
 */
class FlightController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:travel_agent']);
    }
    
    /**
     * Display a listing of flights for the authenticated travel agent
     */
    public function index(): View
    {
        $user = Auth::user();
        $flights = Flight::where('provider_id', $user->id)
            ->latest('departure_datetime')
            ->paginate(10);
        
        $stats = [
            'total_flights' => Flight::where('provider_id', $user->id)->count(),
            'active_flights' => Flight::where('provider_id', $user->id)->where('is_active', true)->count(),
            'scheduled_flights' => Flight::where('provider_id', $user->id)->where('status', 'scheduled')->count(),
            'total_seats' => Flight::where('provider_id', $user->id)->sum('total_seats'),
        ];

        return view('b2b.travel-agent.flights.index', compact('flights', 'stats'));
    }

    /**
     * Show the form for creating a new group flight booking
     */
    public function create(): View
    {
        $flightStatuses = [
            'scheduled' => 'Scheduled',
            'boarding' => 'Boarding',
            'departed' => 'Departed',
            'arrived' => 'Arrived',
            'cancelled' => 'Cancelled',
            'delayed' => 'Delayed'
        ];

        $currencies = [
            'SAR' => 'Saudi Riyal (SAR)',
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
            'AED' => 'UAE Dirham (AED)'
        ];

        $commonAirports = [
            'ADD' => 'Bole International Airport (Addis Ababa)',
            'RUH' => 'King Khalid International Airport (Riyadh)',
            'JED' => 'King Abdulaziz International Airport (Jeddah)',
            'DMM' => 'King Fahd International Airport (Dammam)',
            'AHB' => 'Prince Abdul Mohsin Bin Abdulaziz Airport (Al-Ahsa)',
            'DXB' => 'Dubai International Airport',
            'AUH' => 'Abu Dhabi International Airport',
            'DOH' => 'Hamad International Airport (Doha)',
            'CAI' => 'Cairo International Airport',
            'AMM' => 'Queen Alia International Airport (Amman)',
            'IST' => 'Istanbul Airport',
            'SAW' => 'Sabiha Gökçen International Airport (Istanbul)',
            'ESB' => 'Esenboğa Airport (Ankara)',
            'LHR' => 'Heathrow Airport (London)',
            'CDG' => 'Charles de Gaulle Airport (Paris)',
            'FRA' => 'Frankfurt Airport',
            'BOM' => 'Chhatrapati Shivaji International Airport (Mumbai)',
            'DEL' => 'Indira Gandhi International Airport (Delhi)',
            'KUL' => 'Kuala Lumpur International Airport',
            'SIN' => 'Singapore Changi Airport'
        ];
        
        $tripTypes = [
            'round_trip' => 'Round Trip (Recommended for Groups)',
            'one_way' => 'One Way'
        ];
        
        $paymentTerms = [
            'full_upfront' => 'Full Payment Upfront',
            '50_percent_deposit' => '50% Deposit + 50% Before Travel',
            '30_percent_deposit' => '30% Deposit + 70% Before Travel'
        ];
        
        // Get other travel agents for collaboration
        $availableAgents = \App\Models\User::where('id', '!=', Auth::id())
            ->whereHas('roles', function($query) {
                $query->where('name', 'travel_agent');
            })
            ->select('id', 'name', 'email', 'name')
            ->get();

        return view('b2b.travel-agent.flights.create', compact(
            'flightStatuses', 
            'currencies', 
            'commonAirports', 
            'tripTypes', 
            'paymentTerms',
            'availableAgents'
        ));
    }

    /**
     * Store a newly created group flight booking
     */
    public function store(StoreFlightRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'scheduled';
        $validated['currency'] = $validated['currency'] ?? 'SAR';
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['available_seats'] = $validated['total_seats']; // Initially all seats available
        
        // Set defaults for group booking
        if (!isset($validated['is_group_booking'])) {
            $validated['is_group_booking'] = false;
        }
        
        if (!isset($validated['allows_agent_collaboration'])) {
            $validated['allows_agent_collaboration'] = false;
        }
        
        // For round trip, ensure return flight details are provided
        if ($validated['trip_type'] === 'round_trip') {
            if (empty($validated['return_flight_number'])) {
                $validated['return_flight_number'] = $validated['flight_number'] . 'R';
            }
        } else {
            // Clear return flight data for one-way trips
            $validated['return_flight_number'] = null;
            $validated['return_departure_datetime'] = null;
            $validated['return_arrival_datetime'] = null;
        }
        
        $flight = Flight::create($validated);
        
        return redirect()->route('b2b.travel-agent.flights.show', $flight)
            ->with('success', 'Group flight booking created successfully! You can now invite other agents to collaborate.');
    }

    /**
     * Display the specified flight
     */
    public function show(Flight $flight): View
    {
        // Ensure flight belongs to authenticated user
        if ($flight->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to flight.');
        }

        $flight->load(['packages']);
        
        $flightStats = [
            'total_packages' => $flight->packages->count(),
            'occupancy_rate' => $flight->occupancy_rate,
            'duration' => $flight->formatted_duration,
            'revenue_potential' => $flight->economy_price * $flight->total_seats,
        ];

        return view('b2b.travel-agent.flights.show', compact('flight', 'flightStats'));
    }

    /**
     * Show the form for editing the specified flight
     */
    public function edit(Flight $flight): View
    {
        // Ensure flight belongs to authenticated user
        if ($flight->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to flight.');
        }

        $flightStatuses = [
            'scheduled' => 'Scheduled',
            'boarding' => 'Boarding',
            'departed' => 'Departed',
            'arrived' => 'Arrived',
            'cancelled' => 'Cancelled',
            'delayed' => 'Delayed'
        ];

        $currencies = [
            'SAR' => 'Saudi Riyal (SAR)',
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)'
        ];

        $commonAirports = [
            'RUH' => 'King Khalid International Airport (Riyadh)',
            'JED' => 'King Abdulaziz International Airport (Jeddah)',
            'DXB' => 'Dubai International Airport',
            'DOH' => 'Hamad International Airport (Doha)',
            'CAI' => 'Cairo International Airport',
            'IST' => 'Istanbul Airport',
        ];

        return view('b2b.travel-agent.flights.edit', compact('flight', 'flightStatuses', 'currencies', 'commonAirports'));
    }

    /**
     * Update the specified flight
     */
    public function update(UpdateFlightRequest $request, Flight $flight): RedirectResponse
    {
        // Ensure flight belongs to authenticated user
        if ($flight->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to flight.');
        }

        $validated = $request->validated();
        $flight->update($validated);

        return redirect()->route('b2b.travel-agent.flights.show', $flight)
            ->with('success', 'Flight updated successfully!');
    }

    /**
     * Remove the specified flight
     */
    public function destroy(Flight $flight): RedirectResponse
    {
        // Ensure flight belongs to authenticated user
        if ($flight->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to flight.');
        }

        // Check if flight is used in any packages
        if ($flight->packages()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete flight. It is used in one or more packages.');
        }

        $flight->delete();

        return redirect()->route('b2b.travel-agent.flights.index')
            ->with('success', 'Flight deleted successfully!');
    }

    /**
     * Toggle flight active status
     */
    public function toggleStatus(Flight $flight): RedirectResponse
    {
        // Ensure flight belongs to authenticated user
        if ($flight->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to flight.');
        }

        $flight->update([
            'is_active' => !$flight->is_active
        ]);
        
        $status = $flight->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Flight {$status} successfully!");
    }

    /**
     * Update flight status
     */
    public function updateStatus(Request $request, Flight $flight): RedirectResponse
    {
        // Ensure flight belongs to authenticated user
        if ($flight->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to flight.');
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', Flight::STATUSES)
        ]);

        $flight->update([
            'status' => $request->status
        ]);
        
        return back()->with('success', "Flight status updated to {$request->status}!");
    }

    /**
     * Duplicate flight
     */
    public function duplicate(Flight $flight): RedirectResponse
    {
        // Ensure flight belongs to authenticated user
        if ($flight->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to flight.');
        }

        $newFlight = $flight->replicate();
        $newFlight->flight_number = $flight->flight_number . '_COPY';
        $newFlight->departure_datetime = $flight->departure_datetime->addWeek();
        $newFlight->arrival_datetime = $flight->arrival_datetime->addWeek();
        $newFlight->available_seats = $newFlight->total_seats;
        $newFlight->status = 'scheduled';
        $newFlight->save();

        return redirect()->route('b2b.travel-agent.flights.edit', $newFlight)
            ->with('success', 'Flight duplicated successfully! Please update the details.');
    }

    /**
     * Get flight schedule data for calendar view
     */
    public function getScheduleData(Request $request): JsonResponse
    {
        $query = Flight::where('provider_id', Auth::id());

        if ($request->start && $request->end) {
            $query->whereBetween('departure_datetime', [$request->start, $request->end]);
        }

        $flights = $query->get()->map(function ($flight) {
            return [
                'id' => $flight->id,
                'title' => $flight->flight_number . ' - ' . $flight->route,
                'start' => $flight->departure_datetime->toISOString(),
                'end' => $flight->arrival_datetime->toISOString(),
                'backgroundColor' => $this->getStatusColor($flight->status),
                'borderColor' => $this->getStatusColor($flight->status),
                'url' => route('b2b.travel-agent.flights.show', $flight),
            ];
        });

        return response()->json($flights);
    }

    /**
     * Get status color for calendar
     */
    private function getStatusColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#28a745',
            'boarding' => '#ffc107',
            'departed' => '#007bff',
            'arrived' => '#6c757d',
            'cancelled' => '#dc3545',
            'delayed' => '#fd7e14',
            default => '#6c757d'
        };
    }

    /**
     * Bulk update available seats
     */
    public function bulkUpdateSeats(Request $request): JsonResponse
    {
        $request->validate([
            'flights' => 'required|array',
            'flights.*.id' => 'required|exists:flights,id',
            'flights.*.available_seats' => 'required|integer|min:0',
        ]);

        $updated = 0;
        foreach ($request->flights as $flightData) {
            $flight = Flight::where('id', $flightData['id'])
                          ->where('provider_id', Auth::id())
                          ->first();
            
            if ($flight && $flightData['available_seats'] <= $flight->total_seats) {
                $flight->update(['available_seats' => $flightData['available_seats']]);
                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} flights successfully."
        ]);
    }
}
