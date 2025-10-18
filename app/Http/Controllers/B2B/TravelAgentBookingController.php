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
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use DataTables;

class TravelAgentBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:travel_agent']);
    }

    /**
     * Display a listing of bookings for the authenticated travel agent
     */
    public function index(Request $request): View
    {
        $agent = Auth::user();
        
        // Get filter parameters
        $status = $request->get('status');
        $paymentStatus = $request->get('payment_status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $packageId = $request->get('package_id');
        
        // Build base query for agent's bookings
        $bookingsQuery = PackageBooking::forAgent($agent->id)
            ->with(['package', 'customer', 'payments'])
            ->orderBy('departure_date', 'asc');
        
        // Apply filters
        if ($status) {
            $bookingsQuery->where('status', $status);
        }
        
        if ($paymentStatus) {
            $bookingsQuery->where('payment_status', $paymentStatus);
        }
        
        if ($packageId) {
            $bookingsQuery->where('package_id', $packageId);
        }
        
        if ($dateFrom) {
            $bookingsQuery->whereDate('departure_date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $bookingsQuery->whereDate('departure_date', '<=', $dateTo);
        }
        
        $bookings = $bookingsQuery->paginate(15);
        
        // Get statistics
        $stats = $this->getBookingStats($agent->id);
        
        // Get agent's packages for filter dropdown
        $packages = Package::where('creator_id', $agent->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return view('b2b.travel-agent.bookings.index', compact(
            'bookings', 'stats', 'packages', 'status', 'paymentStatus', 
            'dateFrom', 'dateTo', 'packageId'
        ));
    }

    /**
     * Get bookings data for DataTables (AJAX)
     */
    public function getData(Request $request): JsonResponse
    {
        $agent = Auth::user();
        
        $bookings = PackageBooking::forAgent($agent->id)
            ->with(['package', 'customer', 'payments'])
            ->select('package_bookings.*');
        
        return DataTables::of($bookings)
            ->addColumn('package_name', function ($booking) {
                return $booking->package->name ?? 'N/A';
            })
            ->addColumn('customer_name', function ($booking) {
                return $booking->primary_contact_name;
            })
            ->addColumn('departure_date', function ($booking) {
                return $booking->departure_date->format('M d, Y');
            })
            ->addColumn('status_badge', function ($booking) {
                return '<span class="badge ' . $booking->status_badge_class . '">' . 
                       PackageBooking::STATUSES[$booking->status] . '</span>';
            })
            ->addColumn('payment_status_badge', function ($booking) {
                return '<span class="badge ' . $booking->payment_status_badge_class . '">' . 
                       PackageBooking::PAYMENT_STATUSES[$booking->payment_status] . '</span>';
            })
            ->addColumn('total_amount', function ($booking) {
                return $booking->currency . ' ' . number_format($booking->total_amount, 2);
            })
            ->addColumn('actions', function ($booking) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<a href="' . route('b2b.travel-agent.bookings.show', $booking->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                
                if ($booking->canBeConfirmed()) {
                    $actions .= '<button class="btn btn-sm btn-success confirm-booking" data-id="' . $booking->id . '"><i class="fas fa-check"></i></button>';
                }
                
                if ($booking->canBeCancelled()) {
                    $actions .= '<button class="btn btn-sm btn-danger cancel-booking" data-id="' . $booking->id . '"><i class="fas fa-times"></i></button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'payment_status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Display the specified booking
     */
    public function show($id): View
    {
        $agent = Auth::user();
        
        $booking = PackageBooking::with([
            'package', 'customer', 'agent', 'payments'
        ])->whereHas('package', function ($q) use ($agent) {
            $q->where('creator_id', $agent->id);
        })->findOrFail($id);
        
        return view('b2b.travel-agent.bookings.show', compact('booking'));
    }

    /**
     * Confirm a booking
     */
    public function confirm(Request $request, $id): JsonResponse
    {
        try {
            $agent = Auth::user();
            
            $booking = PackageBooking::whereHas('package', function ($q) use ($agent) {
                $q->where('creator_id', $agent->id);
            })->findOrFail($id);
            
            if (!$booking->canBeConfirmed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be confirmed.'
                ], 422);
            }
            
            $validated = $request->validate([
                'internal_notes' => 'nullable|string|max:1000',
                'send_confirmation_email' => 'boolean'
            ]);
            
            DB::beginTransaction();
            
            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'internal_notes' => $validated['internal_notes'] ?? $booking->internal_notes,
            ]);
            
            // Add to communication log
            $booking->addToCommunicationLog(
                'Booking confirmed by travel agent: ' . $agent->name, 
                'status_change', 
                $agent->id
            );
            
            // TODO: Send confirmation email to customer if requested
            if ($validated['send_confirmation_email'] ?? true) {
                // Send email notification
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully!',
                'booking' => $booking->fresh(['package', 'customer'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming booking', [
                'booking_id' => $id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $agent = Auth::user();
            
            $booking = PackageBooking::whereHas('package', function ($q) use ($agent) {
                $q->where('creator_id', $agent->id);
            })->findOrFail($id);
            
            if (!$booking->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be cancelled.'
                ], 422);
            }
            
            $validated = $request->validate([
                'cancellation_reason' => 'required|string|max:1000',
                'refund_amount' => 'nullable|numeric|min:0|max:' . $booking->paid_amount,
                'notify_customer' => 'boolean'
            ]);
            
            DB::beginTransaction();
            
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ]);
            
            // Handle refund if specified
            if (isset($validated['refund_amount']) && $validated['refund_amount'] > 0) {
                // TODO: Process refund logic
                $booking->addToCommunicationLog(
                    'Refund of ' . $booking->currency . ' ' . $validated['refund_amount'] . ' processed',
                    'refund',
                    $agent->id
                );
            }
            
            // Add to communication log
            $booking->addToCommunicationLog(
                'Booking cancelled by travel agent: ' . $validated['cancellation_reason'], 
                'cancellation', 
                $agent->id
            );
            
            // TODO: Send cancellation email to customer if requested
            if ($validated['notify_customer'] ?? true) {
                // Send email notification
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully!',
                'booking' => $booking->fresh(['package', 'customer'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling booking', [
                'booking_id' => $id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking payment status
     */
    public function updatePaymentStatus(Request $request, $id): JsonResponse
    {
        try {
            $agent = Auth::user();
            
            $booking = PackageBooking::whereHas('package', function ($q) use ($agent) {
                $q->where('creator_id', $agent->id);
            })->findOrFail($id);
            
            $validated = $request->validate([
                'payment_status' => 'required|in:' . implode(',', array_keys(PackageBooking::PAYMENT_STATUSES)),
                'paid_amount' => 'required|numeric|min:0|max:' . $booking->total_amount,
                'payment_method' => 'nullable|in:' . implode(',', array_keys(PackageBooking::PAYMENT_METHODS)),
                'payment_notes' => 'nullable|string|max:500'
            ]);
            
            DB::beginTransaction();
            
            $booking->update([
                'payment_status' => $validated['payment_status'],
                'paid_amount' => $validated['paid_amount'],
                'payment_method' => $validated['payment_method'] ?? $booking->payment_method,
            ]);
            
            // Add to communication log
            $booking->addToCommunicationLog(
                'Payment updated: ' . PackageBooking::PAYMENT_STATUSES[$validated['payment_status']] . 
                ' - Amount: ' . $booking->currency . ' ' . $validated['paid_amount'] . 
                ($validated['payment_notes'] ? ' - Notes: ' . $validated['payment_notes'] : ''),
                'payment',
                $agent->id
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully!',
                'booking' => $booking->fresh(['package', 'customer'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment status', [
                'booking_id' => $id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking statistics for dashboard
     */
    public function getStats(Request $request): JsonResponse
    {
        $agent = Auth::user();
        $stats = $this->getBookingStats($agent->id);
        
        return response()->json($stats);
    }

    /**
     * Export bookings to CSV/Excel
     */
    public function export(Request $request)
    {
        $agent = Auth::user();
        
        // TODO: Implement export functionality
        // Could use Laravel Excel or similar package
        
        return response()->json([
            'success' => false,
            'message' => 'Export functionality not yet implemented'
        ]);
    }

    /**
     * Calculate booking statistics for an agent
     */
    private function getBookingStats($agentId): array
    {
        $baseQuery = PackageBooking::forAgent($agentId);
        
        return [
            'total_bookings' => $baseQuery->count(),
            'pending_bookings' => $baseQuery->where('status', 'pending')->count(),
            'confirmed_bookings' => $baseQuery->where('status', 'confirmed')->count(),
            'completed_bookings' => $baseQuery->where('status', 'completed')->count(),
            'cancelled_bookings' => $baseQuery->where('status', 'cancelled')->count(),
            
            'total_revenue' => $baseQuery->sum('total_amount'),
            'total_paid' => $baseQuery->sum('paid_amount'),
            'pending_payments' => $baseQuery->where('payment_status', '!=', 'paid')->sum('pending_amount'),
            
            'overdue_payments' => $baseQuery->overduePayments()->count(),
            'upcoming_departures' => $baseQuery->upcoming()->where('departure_date', '<=', now()->addDays(30))->count(),
            
            'this_month_bookings' => $baseQuery->whereMonth('created_at', now()->month)->count(),
            'this_month_revenue' => $baseQuery->whereMonth('created_at', now()->month)->sum('total_amount'),
            
            'average_booking_value' => $baseQuery->avg('total_amount') ?: 0,
            'average_participants' => $baseQuery->avg('total_participants') ?: 0,
        ];
    }
}
