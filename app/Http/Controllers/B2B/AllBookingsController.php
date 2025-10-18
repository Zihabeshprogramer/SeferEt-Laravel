<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PackageBooking;
use App\Models\HotelBooking;
use App\Models\FlightBooking;
use App\Models\TransportBooking;
use App\Models\ServiceRequest;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;

class AllBookingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:travel_agent']);
    }

    /**
     * Display all bookings for the authenticated travel agent
     */
    public function index(Request $request): View
    {
        try {
            $agent = Auth::user();
            
            // Get filter parameters
            $bookingType = $request->get('booking_type', 'all');
            $status = $request->get('status');
            $paymentStatus = $request->get('payment_status');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            // Get all bookings based on type filter
            $bookings = $this->getFilteredBookings($agent, $bookingType, $status, $paymentStatus, $dateFrom, $dateTo);
            
            // Get comprehensive statistics
            $stats = $this->getAllBookingStats($agent->id);
            
            // Get filter options
            $filterOptions = $this->getFilterOptions($agent);
            
            return view('b2b.travel-agent.bookings.all-bookings', compact(
                'bookings', 'stats', 'filterOptions', 'bookingType', 'status', 
                'paymentStatus', 'dateFrom', 'dateTo'
            ));
            
        } catch (\Exception $e) {
            // Log the error and show debug info
            \Log::error('AllBookingsController index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            // For development - show the error
            if (app()->environment('local')) {
                throw $e;
            }
            
            // For production - redirect with error
            return redirect()->route('b2b.travel-agent.bookings')
                ->with('error', 'Unable to load all bookings page. Please try again.');
        }
    }

    /**
     * Get bookings data for DataTables (AJAX)
     */
    public function getData(Request $request): JsonResponse
    {
        $agent = Auth::user();
        $bookingType = $request->get('booking_type', 'all');
        
        $bookings = $this->getFilteredBookingsQuery($agent, $bookingType);
        
        return response()->json([
            'data' => $bookings->map(function ($booking) {
                return $this->formatBookingForTable($booking);
            })
        ]);
    }

    /**
     * Display the specified booking (handles all types)
     */
    public function show($type, $id): View
    {
        $agent = Auth::user();
        $booking = $this->findBookingByTypeAndId($type, $id, $agent);
        
        if (!$booking) {
            abort(404, 'Booking not found');
        }
        
        return view('b2b.travel-agent.bookings.show-universal', compact('booking', 'type'));
    }

    /**
     * Cancel a booking (handles all types)
     */
    public function cancel(Request $request, $type, $id): JsonResponse
    {
        try {
            $agent = Auth::user();
            $booking = $this->findBookingByTypeAndId($type, $id, $agent);
            
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            if (!$this->canCancelBooking($booking, $type)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be cancelled.'
                ], 422);
            }
            
            $validated = $request->validate([
                'cancellation_reason' => 'required|string|max:1000',
                'notify_customer' => 'boolean'
            ]);
            
            DB::beginTransaction();
            
            $this->cancelBookingByType($booking, $type, $validated);
            
            // Log the cancellation
            Log::info("Booking cancelled by travel agent", [
                'booking_type' => $type,
                'booking_id' => $id,
                'agent_id' => $agent->id,
                'reason' => $validated['cancellation_reason']
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' booking cancelled successfully!',
                'booking' => $booking->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling booking', [
                'booking_type' => $type,
                'booking_id' => $id,
                'agent_id' => $agent->id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment status for a booking
     */
    public function updatePaymentStatus(Request $request, $type, $id): JsonResponse
    {
        try {
            $agent = Auth::user();
            $booking = $this->findBookingByTypeAndId($type, $id, $agent);
            
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }
            
            $validated = $request->validate([
                'payment_status' => 'required|in:pending,partial,paid,refunded',
                'paid_amount' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string',
                'payment_notes' => 'nullable|string|max:500'
            ]);
            
            DB::beginTransaction();
            
            $this->updateBookingPayment($booking, $type, $validated);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully!',
                'booking' => $booking->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment status', [
                'booking_type' => $type,
                'booking_id' => $id,
                'agent_id' => $agent->id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive booking statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $agent = Auth::user();
        $stats = $this->getAllBookingStats($agent->id);
        
        return response()->json($stats);
    }

    /**
     * Export bookings to CSV/Excel
     */
    public function export(Request $request)
    {
        $agent = Auth::user();
        $bookingType = $request->get('booking_type', 'all');
        
        // TODO: Implement comprehensive export functionality
        return response()->json([
            'success' => false,
            'message' => 'Export functionality not yet implemented'
        ]);
    }

    /**
     * Get filtered bookings based on type and filters
     */
    private function getFilteredBookings($agent, $bookingType = 'all', $status = null, $paymentStatus = null, $dateFrom = null, $dateTo = null)
    {
        $allBookings = collect();

        if ($bookingType === 'all' || $bookingType === 'package') {
            $packageBookings = PackageBooking::forAgent($agent->id)
                ->with(['package', 'customer'])
                ->when($status, function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->when($paymentStatus, function ($query) use ($paymentStatus) {
                    return $query->where('payment_status', $paymentStatus);
                })
                ->when($dateFrom, function ($query) use ($dateFrom) {
                    return $query->whereDate('departure_date', '>=', $dateFrom);
                })
                ->when($dateTo, function ($query) use ($dateTo) {
                    return $query->whereDate('departure_date', '<=', $dateTo);
                })
                ->get()
                ->map(function ($booking) {
                    $booking->booking_type = 'package';
                    $booking->service_name = $booking->package->name ?? 'N/A';
                    $booking->reference_date = $booking->departure_date;
                    return $booking;
                });

            $allBookings = $allBookings->merge($packageBookings);
        }

        if ($bookingType === 'all' || $bookingType === 'hotel') {
            $hotelBookings = HotelBooking::where('source', 'service_request')
                ->whereHas('customer', function ($query) use ($agent) {
                    // For now, we'll get all hotel bookings created from service requests by this agent
                    // This might need refinement based on your business logic
                })
                ->with(['hotel', 'room', 'customer'])
                ->when($status, function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->when($paymentStatus, function ($query) use ($paymentStatus) {
                    return $query->where('payment_status', $paymentStatus);
                })
                ->when($dateFrom, function ($query) use ($dateFrom) {
                    return $query->whereDate('check_in_date', '>=', $dateFrom);
                })
                ->when($dateTo, function ($query) use ($dateTo) {
                    return $query->whereDate('check_in_date', '<=', $dateTo);
                })
                ->get()
                ->map(function ($booking) {
                    $booking->booking_type = 'hotel';
                    $booking->service_name = $booking->hotel->name ?? 'N/A';
                    $booking->reference_date = $booking->check_in_date;
                    return $booking;
                });

            $allBookings = $allBookings->merge($hotelBookings);
        }

        if ($bookingType === 'all' || $bookingType === 'flight') {
            $flightBookings = FlightBooking::where('source', 'service_request')
                ->with(['flight', 'customer'])
                ->when($status, function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->when($paymentStatus, function ($query) use ($paymentStatus) {
                    return $query->where('payment_status', $paymentStatus);
                })
                ->when($dateFrom, function ($query) use ($dateFrom) {
                    return $query->whereHas('flight', function ($q) use ($dateFrom) {
                        $q->whereDate('departure_datetime', '>=', $dateFrom);
                    });
                })
                ->when($dateTo, function ($query) use ($dateTo) {
                    return $query->whereHas('flight', function ($q) use ($dateTo) {
                        $q->whereDate('departure_datetime', '<=', $dateTo);
                    });
                })
                ->get()
                ->map(function ($booking) {
                    $booking->booking_type = 'flight';
                    $booking->service_name = ($booking->flight->airline ?? 'N/A') . ' ' . ($booking->flight->flight_number ?? '');
                    $booking->reference_date = $booking->flight->departure_datetime ?? null;
                    return $booking;
                });

            $allBookings = $allBookings->merge($flightBookings);
        }

        if ($bookingType === 'all' || $bookingType === 'transport') {
            $transportBookings = TransportBooking::where('source', 'service_request')
                ->with(['transportService', 'customer'])
                ->when($status, function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->when($paymentStatus, function ($query) use ($paymentStatus) {
                    return $query->where('payment_status', $paymentStatus);
                })
                ->when($dateFrom, function ($query) use ($dateFrom) {
                    return $query->whereDate('pickup_datetime', '>=', $dateFrom);
                })
                ->when($dateTo, function ($query) use ($dateTo) {
                    return $query->whereDate('pickup_datetime', '<=', $dateTo);
                })
                ->get()
                ->map(function ($booking) {
                    $booking->booking_type = 'transport';
                    $booking->service_name = $booking->transportService->name ?? $booking->transport_type ?? 'Transport';
                    $booking->reference_date = $booking->pickup_datetime;
                    return $booking;
                });

            $allBookings = $allBookings->merge($transportBookings);
        }

        // Sort by reference date (most recent first)
        return $allBookings->sortByDesc('reference_date')->take(50);
    }

    /**
     * Get filter options for dropdowns
     */
    private function getFilterOptions($agent): array
    {
        return [
            'booking_types' => [
                'all' => 'All Types',
                'package' => 'Package Tours',
                'hotel' => 'Hotel Bookings',
                'flight' => 'Flight Bookings',
                'transport' => 'Transport Bookings',
            ],
            'statuses' => [
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
                'completed' => 'Completed',
                'no_show' => 'No Show',
            ],
            'payment_statuses' => [
                'pending' => 'Pending',
                'partial' => 'Partial',
                'paid' => 'Paid',
                'refunded' => 'Refunded',
            ]
        ];
    }

    /**
     * Calculate comprehensive booking statistics
     */
    private function getAllBookingStats($agentId): array
    {
        // Package bookings stats
        $packageStats = PackageBooking::forAgent($agentId);
        
        // Hotel bookings stats (from service requests)
        $hotelStats = HotelBooking::where('source', 'service_request');
        
        // Flight bookings stats
        $flightStats = FlightBooking::where('source', 'service_request');
        
        // Transport bookings stats
        $transportStats = TransportBooking::where('source', 'service_request');

        return [
            // Overall stats
            'total_bookings' => $packageStats->count() + $hotelStats->count() + $flightStats->count() + $transportStats->count(),
            'total_revenue' => $packageStats->sum('total_amount') + $hotelStats->sum('total_amount') + $flightStats->sum('total_amount') + $transportStats->sum('total_amount'),
            'pending_bookings' => $packageStats->where('status', 'pending')->count() + $hotelStats->where('status', 'pending')->count() + $flightStats->where('status', 'pending')->count() + $transportStats->where('status', 'pending')->count(),
            'confirmed_bookings' => $packageStats->where('status', 'confirmed')->count() + $hotelStats->where('status', 'confirmed')->count() + $flightStats->where('status', 'confirmed')->count() + $transportStats->where('status', 'confirmed')->count(),
            
            // By type
            'package_bookings' => [
                'count' => $packageStats->count(),
                'revenue' => $packageStats->sum('total_amount'),
                'pending' => $packageStats->where('status', 'pending')->count(),
            ],
            'hotel_bookings' => [
                'count' => $hotelStats->count(),
                'revenue' => $hotelStats->sum('total_amount'),
                'pending' => $hotelStats->where('status', 'pending')->count(),
            ],
            'flight_bookings' => [
                'count' => $flightStats->count(),
                'revenue' => $flightStats->sum('total_amount'),
                'pending' => $flightStats->where('status', 'pending')->count(),
            ],
            'transport_bookings' => [
                'count' => $transportStats->count(),
                'revenue' => $transportStats->sum('total_amount'),
                'pending' => $transportStats->where('status', 'pending')->count(),
            ],
            
            // This month
            'this_month_bookings' => $packageStats->whereMonth('created_at', now()->month)->count() + 
                                   $hotelStats->whereMonth('created_at', now()->month)->count() + 
                                   $flightStats->whereMonth('created_at', now()->month)->count() + 
                                   $transportStats->whereMonth('created_at', now()->month)->count(),
            
            'this_month_revenue' => $packageStats->whereMonth('created_at', now()->month)->sum('total_amount') + 
                                  $hotelStats->whereMonth('created_at', now()->month)->sum('total_amount') + 
                                  $flightStats->whereMonth('created_at', now()->month)->sum('total_amount') + 
                                  $transportStats->whereMonth('created_at', now()->month)->sum('total_amount'),
        ];
    }

    /**
     * Find booking by type and ID
     */
    private function findBookingByTypeAndId($type, $id, $agent)
    {
        switch ($type) {
            case 'package':
                return PackageBooking::forAgent($agent->id)
                    ->with(['package', 'customer'])
                    ->findOrFail($id);
            
            case 'hotel':
                return HotelBooking::with(['hotel', 'room', 'customer'])
                    ->where('source', 'service_request')
                    ->findOrFail($id);
            
            case 'flight':
                return FlightBooking::with(['flight', 'customer'])
                    ->where('source', 'service_request')
                    ->findOrFail($id);
            
            case 'transport':
                return TransportBooking::with(['transportService', 'customer'])
                    ->where('source', 'service_request')
                    ->findOrFail($id);
            
            default:
                return null;
        }
    }

    /**
     * Check if a booking can be cancelled
     */
    private function canCancelBooking($booking, $type): bool
    {
        switch ($type) {
            case 'package':
                return method_exists($booking, 'canBeCancelled') ? $booking->canBeCancelled() : !in_array($booking->status, ['cancelled', 'completed']);
            
            case 'hotel':
            case 'flight':
            case 'transport':
                return method_exists($booking, 'canBeCancelled') ? $booking->canBeCancelled() : !in_array($booking->status, ['cancelled', 'completed']);
            
            default:
                return false;
        }
    }

    /**
     * Cancel booking by type
     */
    private function cancelBookingByType($booking, $type, $data)
    {
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $data['cancellation_reason']
        ]);
    }

    /**
     * Update booking payment by type
     */
    private function updateBookingPayment($booking, $type, $data)
    {
        $updateData = [
            'payment_status' => $data['payment_status'],
            'paid_amount' => $data['paid_amount']
        ];

        if (isset($data['payment_method'])) {
            $updateData['payment_method'] = $data['payment_method'];
        }

        $booking->update($updateData);
    }

    /**
     * Format booking data for table display
     */
    private function formatBookingForTable($booking): array
    {
        $type = $booking->booking_type;
        
        return [
            'id' => $booking->id,
            'type' => $type,
            'type_label' => ucfirst($type),
            'reference' => $booking->booking_reference ?? 'N/A',
            'service_name' => $booking->service_name,
            'customer_name' => $this->getCustomerName($booking),
            'reference_date' => $booking->reference_date ? $booking->reference_date->format('M d, Y') : 'N/A',
            'total_amount' => ($booking->currency ?? 'USD') . ' ' . number_format($booking->total_amount ?? 0, 2),
            'status' => $booking->status,
            'payment_status' => $booking->payment_status ?? 'pending',
            'created_at' => $booking->created_at->format('M d, Y'),
            'actions' => $this->generateActionButtons($booking, $type)
        ];
    }

    /**
     * Get customer name from booking
     */
    private function getCustomerName($booking): string
    {
        if (isset($booking->primary_contact_name)) {
            return $booking->primary_contact_name; // Package bookings
        }
        
        if (isset($booking->guest_name)) {
            return $booking->guest_name; // Hotel bookings
        }
        
        if (isset($booking->passenger_name)) {
            return $booking->passenger_name; // Flight bookings
        }
        
        if (isset($booking->passenger_name)) {
            return $booking->passenger_name; // Transport bookings (passenger_name)
        }
        
        return $booking->customer->name ?? 'N/A';
    }

    /**
     * Generate action buttons for booking
     */
    private function generateActionButtons($booking, $type): string
    {
        $buttons = [];
        
        // View button
        $buttons[] = '<a href="' . route('b2b.travel-agent.bookings.show-universal', [$type, $booking->id]) . '" class="btn btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>';
        
        // Cancel button (if cancellable)
        if ($this->canCancelBooking($booking, $type)) {
            $buttons[] = '<button class="btn btn-sm btn-danger cancel-booking" data-type="' . $type . '" data-id="' . $booking->id . '" title="Cancel Booking"><i class="fas fa-times"></i></button>';
        }
        
        // Payment update button
        if (!in_array($booking->status, ['cancelled', 'completed']) && $booking->payment_status !== 'paid') {
            $buttons[] = '<button class="btn btn-sm btn-warning update-payment" data-type="' . $type . '" data-id="' . $booking->id . '" title="Update Payment"><i class="fas fa-credit-card"></i></button>';
        }
        
        return '<div class="btn-group" role="group">' . implode('', $buttons) . '</div>';
    }

    /**
     * Get filtered bookings query for DataTables
     */
    private function getFilteredBookingsQuery($agent, $bookingType)
    {
        return $this->getFilteredBookings($agent, $bookingType);
    }
}