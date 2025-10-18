<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\PackageDraft;
use App\Models\Package;
use App\Models\PackageBooking;
use Illuminate\Support\Facades\Auth;

class TravelAgentDashboardController extends Controller
{
    /**
     * Display the travel agent dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Get user's draft packages (paginated for dashboard)
        $draftPackages = PackageDraft::forUser($user->id)
            ->active()
            ->latest('updated_at')
            ->paginate(6);
        
        // Get user's packages statistics
        $totalPackages = Package::where('creator_id', $user->id)->count();
        $activePackages = Package::where('creator_id', $user->id)->where('status', 'active')->count();
        $draftCount = PackageDraft::forUser($user->id)->active()->count();
        
        // Get recent activities (packages created in last 30 days)
        $recentPackages = Package::where('creator_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->take(5)
            ->get();
        
        // Get booking statistics
        $bookingStats = $this->getBookingStats($user->id);
        
        // Get recent bookings for quick overview
        $recentBookings = PackageBooking::forAgent($user->id)
            ->with(['package', 'customer'])
            ->latest()
            ->take(5)
            ->get();
        
        // Get upcoming departures (next 30 days)
        $upcomingDepartures = PackageBooking::forAgent($user->id)
            ->with(['package', 'customer'])
            ->where('departure_date', '>=', now()->toDateString())
            ->where('departure_date', '<=', now()->addDays(30)->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_date', 'asc')
            ->take(10)
            ->get();
        
        $stats = [
            'totalPackages' => $totalPackages,
            'activePackages' => $activePackages,
            'draftPackages' => $draftCount,
            'recentPackages' => $recentPackages->count(),
            
            // Booking statistics
            'totalBookings' => $bookingStats['total_bookings'],
            'pendingBookings' => $bookingStats['pending_bookings'],
            'confirmedBookings' => $bookingStats['confirmed_bookings'],
            'completedBookings' => $bookingStats['completed_bookings'],
            'totalRevenue' => $bookingStats['total_revenue'],
            'totalPaid' => $bookingStats['total_paid'],
            'pendingPayments' => $bookingStats['pending_payments'],
            'thisMonthBookings' => $bookingStats['this_month_bookings'],
            'thisMonthRevenue' => $bookingStats['this_month_revenue'],
            'overduePayments' => $bookingStats['overdue_payments'],
            'upcomingDepartures' => $bookingStats['upcoming_departures'],
        ];
        
        return view('b2b.travel-agent.dashboard', compact(
            'draftPackages',
            'stats',
            'recentPackages',
            'recentBookings',
            'upcomingDepartures',
            'bookingStats'
        ));
    }
    
    /**
     * Delete a draft package.
     *
     * @param Request $request
     * @param int $draftId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteDraft(Request $request, int $draftId)
    {
        $user = Auth::user();
        
        $draft = PackageDraft::where('id', $draftId)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $draft->delete();
        
        return redirect()->route('b2b.travel-agent.dashboard')
            ->with('success', 'Draft package deleted successfully.');
    }
    
    /**
     * Get all draft packages for the user.
     *
     * @param Request $request
     * @return View
     */
    public function drafts(Request $request): View
    {
        $user = Auth::user();
        
        $draftPackages = PackageDraft::forUser($user->id)
            ->when($request->get('status') === 'expired', function($query) {
                return $query->expired();
            }, function($query) {
                return $query->active();
            })
            ->latest('updated_at')
            ->paginate(12);
        
        // Get counts for badge display
        $activeDraftsCount = PackageDraft::forUser($user->id)->active()->count();
        $expiredDraftsCount = PackageDraft::forUser($user->id)->expired()->count();
        
        return view('b2b.travel-agent.drafts.index', compact(
            'draftPackages', 'activeDraftsCount', 'expiredDraftsCount'
        ));
    }
    
    /**
     * Clean expired drafts for the user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanupExpired()
    {
        $user = Auth::user();
        
        $expiredCount = PackageDraft::forUser($user->id)
            ->expired()
            ->delete();
        
        return redirect()->back()
            ->with('success', "Successfully cleaned up {$expiredCount} expired draft(s).");
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
            
            'total_revenue' => $baseQuery->sum('total_amount') ?: 0,
            'total_paid' => $baseQuery->sum('paid_amount') ?: 0,
            'pending_payments' => $baseQuery->where('payment_status', '!=', 'paid')->sum('pending_amount') ?: 0,
            
            'overdue_payments' => $baseQuery->overduePayments()->count(),
            'upcoming_departures' => $baseQuery->upcoming()->where('departure_date', '<=', now()->addDays(30))->count(),
            
            'this_month_bookings' => $baseQuery->whereMonth('created_at', now()->month)->count(),
            'this_month_revenue' => $baseQuery->whereMonth('created_at', now()->month)->sum('total_amount') ?: 0,
            
            'average_booking_value' => $baseQuery->avg('total_amount') ?: 0,
            'average_participants' => $baseQuery->avg('total_participants') ?: 0,
        ];
    }
}
