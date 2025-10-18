<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Hotel;
use App\Models\HotelService;
use App\Models\HotelBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PartnerManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.access:view admin dashboard');
    }

    /**
     * Display the partner management dashboard
     */
    public function index(Request $request)
    {
        // Check permission - TEMPORARILY DISABLED FOR TESTING
        // $user = auth()->user();
        // if (!$user->getPermissionsViaRoles()->where('name', 'manage partners')->isNotEmpty()) {
        //     abort(403, 'Access denied. Permission required: manage partners');
        // }

        if ($request->ajax()) {
            // Use the partners scope which includes all partner types
            $partners = User::partners()
                ->with(['roles'])
                ->when($request->partner_type, function ($query, $type) {
                    return $query->where('role', $type);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->approval_status, function ($query, $approvalStatus) {
                    switch($approvalStatus) {
                        case 'approved':
                            return $query->where('status', User::STATUS_ACTIVE);
                        case 'pending':
                            return $query->where('status', User::STATUS_PENDING);
                        case 'rejected':
                            return $query->where('status', User::STATUS_SUSPENDED);
                    }
                })
                ->select(['id', 'name', 'email', 'role', 'status', 'company_name', 'created_at']);

            return DataTables::of($partners)
                ->addColumn('partner_type', function ($partner) {
                    $typeMap = [
                        User::ROLE_PARTNER => 'Travel Agent (Legacy)',
                        User::ROLE_TRAVEL_AGENT => 'Travel Agent',
                        User::ROLE_HOTEL_PROVIDER => 'Hotel Provider',
                        User::ROLE_TRANSPORT_PROVIDER => 'Transport Provider',
                    ];
                    $type = $typeMap[$partner->role] ?? 'Unknown';
                    $badgeClass = match ($partner->role) {
                        User::ROLE_PARTNER => 'info',
                        User::ROLE_TRAVEL_AGENT => 'info',
                        User::ROLE_HOTEL_PROVIDER => 'warning',
                        User::ROLE_TRANSPORT_PROVIDER => 'dark',
                        default => 'secondary'
                    };
                    return '<span class="badge badge-' . $badgeClass . '">' . $type . '</span>';
                })
                ->addColumn('status_badge', function ($partner) {
                    $badgeClass = match ($partner->status) {
                        User::STATUS_ACTIVE => 'success',
                        User::STATUS_SUSPENDED => 'danger',
                        User::STATUS_PENDING => 'warning',
                        default => 'secondary'
                    };
                    $statusText = match ($partner->status) {
                        User::STATUS_ACTIVE => 'Approved',
                        User::STATUS_SUSPENDED => 'Rejected',
                        User::STATUS_PENDING => 'Pending Review',
                        default => ucfirst($partner->status)
                    };
                    return '<span class="badge badge-' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('business_info', function ($partner) {
                    $info = '<strong>' . ($partner->company_name ?: 'N/A') . '</strong><br>';
                    $info .= '<small class="text-muted">Joined: ' . $partner->created_at->format('M d, Y') . '</small>';
                    if ($partner->status === User::STATUS_ACTIVE) {
                        $info .= '<br><small class="text-success">Status: Active</small>';
                    }
                    return $info;
                })
                ->addColumn('business_stats', function ($partner) {
                    return $this->getPartnerBusinessStats($partner);
                })
                ->addColumn('actions', function ($partner) {
                    $actions = '<div class="btn-group" role="group">';
                    $currentUser = auth()->user();
                    
                    // View Details
                    $actions .= '<a href="' . route('admin.partners.show', $partner->id) . '" class="btn btn-sm btn-info" title="View Details">'
                             . '<i class="fas fa-eye"></i></a>';
                    
                    // Approval Actions - Simplified for testing
                    if ($partner->status === User::STATUS_PENDING) {
                        $actions .= '<button class="btn btn-sm btn-success ml-1" onclick="approvePartner(' . $partner->id . ')" title="Approve">'
                                 . '<i class="fas fa-check"></i></button>';
                        $actions .= '<button class="btn btn-sm btn-danger ml-1" onclick="rejectPartner(' . $partner->id . ')" title="Reject">'
                                 . '<i class="fas fa-times"></i></button>';
                    }
                    
                    if ($partner->status === User::STATUS_ACTIVE) {
                        $actions .= '<button class="btn btn-sm btn-warning ml-1" onclick="suspendPartner(' . $partner->id . ')" title="Suspend">'
                                 . '<i class="fas fa-pause"></i></button>';
                    }
                    
                    if ($partner->status === User::STATUS_SUSPENDED) {
                        $actions .= '<button class="btn btn-sm btn-success ml-1" onclick="reactivatePartner(' . $partner->id . ')" title="Reactivate">'
                                 . '<i class="fas fa-play"></i></button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['partner_type', 'status_badge', 'business_info', 'business_stats', 'actions'])
                ->make(true);
        }

        $stats = [
            'total' => User::partners()->count(),
            'active' => User::partners()->active()->count(),
            'pending' => User::partners()->pending()->count(),
            'suspended' => User::partners()->suspended()->count(),
            'legacy_partners' => User::where('role', User::ROLE_PARTNER)->count(),
            'travel_agents' => User::where('role', User::ROLE_TRAVEL_AGENT)->count(),
            'hotel_providers' => User::where('role', User::ROLE_HOTEL_PROVIDER)->count(),
            'transport_providers' => User::where('role', User::ROLE_TRANSPORT_PROVIDER)->count(),
        ];

        $partnerTypes = [
            User::ROLE_PARTNER => 'Travel Agent (Legacy)',
            User::ROLE_TRAVEL_AGENT => 'Travel Agent',
            User::ROLE_HOTEL_PROVIDER => 'Hotel Provider',
            User::ROLE_TRANSPORT_PROVIDER => 'Transport Provider',
        ];

        $statuses = User::STATUSES;

        return view('admin.partners.management', compact('stats', 'partnerTypes', 'statuses'));
    }

    /**
     * Show partner details for business review
     */
    public function show($id)
    {
        $partner = User::with(['roles'])->partners()->findOrFail($id);
        
        // Get partner-specific business data
        $businessData = $this->getPartnerDetailedStats($partner);
        
        // Get recent activity logs
        $recentActivity = $this->getPartnerRecentActivity($partner);
        
        return view('admin.partners.show', compact('partner', 'businessData', 'recentActivity'));
    }

    /**
     * Approve a partner
     */
    public function approve(Request $request, $id)
    {
        $partner = User::partners()->findOrFail($id);
        
        if ($partner->status !== User::STATUS_PENDING) {
            return response()->json([
                'success' => false, 
                'message' => 'Only pending partners can be approved.'
            ]);
        }
        
        DB::transaction(function () use ($partner) {
            $partner->status = User::STATUS_ACTIVE;
            $partner->save();
            
            // Assign appropriate role permissions
            $this->assignPartnerPermissions($partner);
            
            // Send approval notification email
            $this->sendApprovalNotification($partner);
        });
        
        return response()->json([
            'success' => true,
            'message' => "Partner '{$partner->name}' has been approved successfully."
        ]);
    }

    /**
     * Reject a partner
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first()
            ]);
        }

        $partner = User::partners()->findOrFail($id);
        
        DB::transaction(function () use ($partner, $request) {
            $partner->status = User::STATUS_SUSPENDED;
            $partner->suspend_reason = $request->reason;
            $partner->save();
            
            // Send rejection notification email
            $this->sendRejectionNotification($partner, $request->reason);
        });
        
        return response()->json([
            'success' => true,
            'message' => "Partner '{$partner->name}' has been rejected."
        ]);
    }

    /**
     * Suspend a partner
     */
    public function suspend(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first()
            ]);
        }

        $partner = User::partners()->findOrFail($id);
        
        DB::transaction(function () use ($partner, $request) {
            $partner->status = User::STATUS_SUSPENDED;
            $partner->suspend_reason = $request->reason;
            $partner->save();
            
            // Send suspension notification email
            $this->sendSuspensionNotification($partner, $request->reason);
        });
        
        return response()->json([
            'success' => true,
            'message' => "Partner '{$partner->name}' has been suspended."
        ]);
    }

    /**
     * Reactivate a partner
     */
    public function reactivate($id)
    {
        $partner = User::partners()->findOrFail($id);
        
        DB::transaction(function () use ($partner) {
            $partner->status = User::STATUS_ACTIVE;
            $partner->suspend_reason = null;
            $partner->save();
            
            // Send reactivation notification email
            $this->sendReactivationNotification($partner);
        });
        
        return response()->json([
            'success' => true,
            'message' => "Partner '{$partner->name}' has been reactivated."
        ]);
    }

    /**
     * Business performance overview
     */
    public function businessOverview()
    {
        $overview = [
            'revenue_trends' => $this->getRevenueTrends(),
            'partner_performance' => $this->getPartnerPerformance(),
            'business_metrics' => $this->getBusinessMetrics(),
            'pending_reviews' => $this->getPendingBusinessReviews(),
        ];

        return view('admin.partners.business-overview', compact('overview'));
    }

    /**
     * Export partners data
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality (CSV, Excel)
        $partners = User::partners()->with(['roles'])->get();
        
        $filename = 'partners_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($partners) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Company', 'Type', 'Status', 'Joined']);
            
            foreach ($partners as $partner) {
                fputcsv($file, [
                    $partner->id,
                    $partner->name,
                    $partner->email,
                    $partner->company_name,
                    ucfirst(str_replace('_', ' ', $partner->role)),
                    ucfirst($partner->status),
                    $partner->created_at->format('Y-m-d'),
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get partner business statistics for listing
     */
    private function getPartnerBusinessStats($partner)
    {
        switch ($partner->role) {
            case User::ROLE_HOTEL_PROVIDER:
                $hotelCount = Hotel::where('provider_id', $partner->id)->count();
                $bookingCount = HotelBooking::whereHas('hotel', function($q) use ($partner) {
                    $q->where('provider_id', $partner->id);
                })->count();
                $revenue = HotelBooking::whereHas('hotel', function($q) use ($partner) {
                    $q->where('provider_id', $partner->id);
                })->sum('total_amount');
                
                return '<small class="text-muted">Hotels: ' . $hotelCount . ' | Bookings: ' . $bookingCount . '<br>Revenue: $' . number_format($revenue, 0) . '</small>';
                
            case User::ROLE_PARTNER: // Legacy Travel Agent
            case User::ROLE_TRAVEL_AGENT: // Travel Agent
                // Check what column packages table uses for user relationship
                $packageCount = Package::where('creator_id', $partner->id)->count() ?? 0;
                $activePackages = Package::where('creator_id', $partner->id)->where('status', 'active')->count() ?? 0;
                return '<small class="text-muted">Packages: ' . $packageCount . ' | Active: ' . $activePackages . '</small>';
                
            case User::ROLE_TRANSPORT_PROVIDER:
                return '<small class="text-muted">Vehicles: 0 | Routes: 0<br>Coming Soon</small>';
                
            default:
                return '<small class="text-muted">No data available</small>';
        }
    }

    /**
     * Get detailed partner statistics for show page
     */
    private function getPartnerDetailedStats($partner)
    {
        $stats = [
            'overview' => [],
            'business_metrics' => [],
            'performance' => []
        ];

        switch ($partner->role) {
            case User::ROLE_HOTEL_PROVIDER:
                $hotels = Hotel::where('provider_id', $partner->id);
                $bookings = HotelBooking::whereHas('hotel', function($q) use ($partner) {
                    $q->where('provider_id', $partner->id);
                });
                
                $totalRevenue = $bookings->sum('total_amount');
                $monthlyRevenue = $bookings->where('created_at', '>=', now()->subDays(30))->sum('total_amount');
                
                $stats['overview'] = [
                    'Total Hotels' => $hotels->count(),
                    'Active Hotels' => $hotels->where('status', 'active')->count(),
                    'Total Bookings' => $bookings->count(),
                    'Total Revenue' => '$' . number_format($totalRevenue, 2)
                ];
                
                $stats['business_metrics'] = [
                    'Monthly Revenue' => '$' . number_format($monthlyRevenue, 2),
                    'Average Booking Value' => $bookings->count() > 0 ? '$' . number_format($totalRevenue / $bookings->count(), 2) : '$0',
                    'Occupancy Rate' => '75%', // TODO: Calculate actual occupancy
                    'Customer Rating' => '4.2/5' // TODO: Calculate from reviews
                ];
                break;
                
            case User::ROLE_PARTNER: // Legacy Travel Agent
            case User::ROLE_TRAVEL_AGENT: // Travel Agent
                $packages = Package::where('creator_id', $partner->id);
                
                $stats['overview'] = [
                    'Total Packages' => $packages->count() ?? 0,
                    'Active Packages' => $packages->where('status', 'active')->count() ?? 0,
                    'Total Bookings' => 0, // TODO: Implement when booking system is ready
                    'Total Revenue' => '$0.00'
                ];
                
                $stats['business_metrics'] = [
                    'Monthly Bookings' => 0,
                    'Commission Earned' => '$0.00',
                    'Customer Satisfaction' => 'N/A',
                    'Package Rating' => 'N/A'
                ];
                break;
                
            case User::ROLE_TRANSPORT_PROVIDER:
                $stats['overview'] = [
                    'Total Vehicles' => 0,
                    'Active Routes' => 0,
                    'Total Bookings' => 0,
                    'Total Revenue' => '$0.00'
                ];
                
                $stats['business_metrics'] = [
                    'Fleet Utilization' => '0%',
                    'On-time Performance' => 'N/A',
                    'Customer Rating' => 'N/A',
                    'Monthly Revenue' => '$0.00'
                ];
                break;
        }

        return $stats;
    }

    /**
     * Get partner recent activity
     */
    private function getPartnerRecentActivity($partner)
    {
        $activities = [];

        switch ($partner->role) {
            case User::ROLE_HOTEL_PROVIDER:
                $recentBookings = HotelBooking::whereHas('hotel', function($q) use ($partner) {
                    $q->where('provider_id', $partner->id);
                })->with(['hotel', 'customer'])->latest()->take(5)->get();

                foreach ($recentBookings as $booking) {
                    $activities[] = [
                        'type' => 'booking',
                        'icon' => 'fas fa-calendar-check',
                        'color' => 'success',
                        'title' => 'New Hotel Booking',
                        'description' => "Booking #{$booking->id} at {$booking->hotel->name}",
                        'date' => $booking->created_at->format('M d, Y H:i'),
                        'amount' => '$' . number_format($booking->total_amount, 2)
                    ];
                }

                $recentHotels = Hotel::where('provider_id', $partner->id)->latest()->take(3)->get();
                foreach ($recentHotels as $hotel) {
                    $activities[] = [
                        'type' => 'hotel',
                        'icon' => 'fas fa-hotel',
                        'color' => 'info',
                        'title' => 'Hotel Added',
                        'description' => "Added {$hotel->name}",
                        'date' => $hotel->created_at->format('M d, Y H:i'),
                        'amount' => null
                    ];
                }
                break;

            case User::ROLE_PARTNER:
            case User::ROLE_TRAVEL_AGENT:
                // TODO: Add travel agent activities when package system is ready
                $activities[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-info-circle',
                    'color' => 'secondary',
                    'title' => 'No Recent Activity',
                    'description' => 'Travel agent activity tracking coming soon',
                    'date' => now()->format('M d, Y H:i'),
                    'amount' => null
                ];
                break;

            case User::ROLE_TRANSPORT_PROVIDER:
                // TODO: Add transport provider activities
                $activities[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-info-circle',
                    'color' => 'secondary',
                    'title' => 'No Recent Activity',
                    'description' => 'Transport provider activity tracking coming soon',
                    'date' => now()->format('M d, Y H:i'),
                    'amount' => null
                ];
                break;
        }

        // Sort by date and limit to 10 most recent
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Assign appropriate permissions to newly approved partner
     */
    private function assignPartnerPermissions($partner)
    {
        switch ($partner->role) {
            case User::ROLE_HOTEL_PROVIDER:
                if (!$partner->hasRole('hotel_provider')) {
                    $partner->assignRole('hotel_provider');
                }
                break;
            case User::ROLE_PARTNER:
            case User::ROLE_TRAVEL_AGENT:
                if (!$partner->hasRole('travel_agent')) {
                    $partner->assignRole('travel_agent');
                }
                break;
            case User::ROLE_TRANSPORT_PROVIDER:
                if (!$partner->hasRole('transport_provider')) {
                    $partner->assignRole('transport_provider');
                }
                break;
        }
    }

    /**
     * Send approval notification email
     */
    private function sendApprovalNotification($partner)
    {
        // TODO: Implement email notification
        // Mail::to($partner->email)->send(new PartnerApprovedMail($partner));
    }

    /**
     * Send rejection notification email
     */
    private function sendRejectionNotification($partner, $reason)
    {
        // TODO: Implement email notification
        // Mail::to($partner->email)->send(new PartnerRejectedMail($partner, $reason));
    }

    /**
     * Send suspension notification email
     */
    private function sendSuspensionNotification($partner, $reason)
    {
        // TODO: Implement email notification
        // Mail::to($partner->email)->send(new PartnerSuspendedMail($partner, $reason));
    }

    /**
     * Send reactivation notification email
     */
    private function sendReactivationNotification($partner)
    {
        // TODO: Implement email notification
        // Mail::to($partner->email)->send(new PartnerReactivatedMail($partner));
    }

    /**
     * Get revenue trends for business overview
     */
    private function getRevenueTrends()
    {
        // TODO: Implement comprehensive revenue analytics
        return [
            'monthly_revenue' => 0,
            'growth_rate' => 0,
            'trend' => 'stable'
        ];
    }

    /**
     * Get partner performance metrics
     */
    private function getPartnerPerformance()
    {
        // TODO: Implement partner performance analytics
        return [
            'top_performers' => [],
            'underperformers' => [],
            'average_rating' => 0
        ];
    }

    /**
     * Get general business metrics
     */
    private function getBusinessMetrics()
    {
        // TODO: Implement comprehensive business metrics
        return [
            'total_bookings' => 0,
            'conversion_rate' => 0,
            'customer_satisfaction' => 0
        ];
    }

    /**
     * Get pending business reviews
     */
    private function getPendingBusinessReviews()
    {
        return User::partners()->pending()->with(['roles'])->latest()->take(5)->get();
    }

    // ===========================================
    // HOTEL SERVICE APPROVAL METHODS
    // ===========================================

    /**
     * Display hotel services pending approval
     */
    public function hotelServices(Request $request)
    {
        if ($request->ajax()) {
            $query = Hotel::with(['provider'])
                ->select(['id', 'provider_id', 'name', 'city', 'country', 'star_rating', 'status', 'is_active', 'created_at']);

            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'approved':
                        $query->where('status', 'active');
                        break;
                    case 'pending':
                        $query->where('status', 'pending');
                        break;
                    case 'suspended':
                        $query->where('status', 'suspended');
                        break;
                    case 'rejected':
                        $query->where('status', 'rejected');
                        break;
                }
            }

            if ($request->filled('city')) {
                $query->where('city', 'like', '%' . $request->city . '%');
            }

            return DataTables::of($query)
                ->addColumn('provider_name', function ($hotel) {
                    return $hotel->provider ? $hotel->provider->name : 'Unknown';
                })
                ->addColumn('status_badge', function ($hotel) {
                    $badgeClass = match ($hotel->status) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        'rejected' => 'danger',
                        default => 'secondary'
                    };
                    $statusText = match ($hotel->status) {
                        'active' => 'Approved',
                        'pending' => 'Pending',
                        'suspended' => 'Suspended',
                        'rejected' => 'Rejected',
                        default => ucfirst($hotel->status)
                    };
                    return "<span class='badge badge-{$badgeClass}'>" . $statusText . "</span>";
                })
                ->addColumn('location', function ($hotel) {
                    return $hotel->city . ', ' . $hotel->country;
                })
                ->addColumn('rating', function ($hotel) {
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $class = $i <= $hotel->star_rating ? 'fas fa-star text-warning' : 'far fa-star text-muted';
                        $stars .= "<i class='{$class}'></i> ";
                    }
                    return $stars;
                })
                ->addColumn('actions', function ($hotel) {
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    $actions .= '<button class="btn btn-info btn-sm" onclick="viewHotelService(' . $hotel->id . ')" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>';
                    
                    if ($hotel->status === 'pending') {
                        $actions .= '<button class="btn btn-success btn-sm" onclick="approveHotelService(' . $hotel->id . ')" title="Approve">
                            <i class="fas fa-check"></i>
                        </button>';
                        $actions .= '<button class="btn btn-danger btn-sm" onclick="rejectHotelService(' . $hotel->id . ')" title="Reject">
                            <i class="fas fa-times"></i>
                        </button>';
                    } elseif ($hotel->status === 'active') {
                        $actions .= '<button class="btn btn-warning btn-sm" onclick="suspendHotelService(' . $hotel->id . ')" title="Suspend">
                            <i class="fas fa-pause"></i>
                        </button>';
                    } elseif ($hotel->status === 'suspended') {
                        $actions .= '<button class="btn btn-success btn-sm" onclick="reactivateHotelService(' . $hotel->id . ')" title="Reactivate">
                            <i class="fas fa-play"></i>
                        </button>';
                    }
                    
                    $actions .= '</div>';
                    
                    return $actions;
                })
                ->editColumn('created_at', function ($hotel) {
                    return $hotel->created_at->format('M d, Y');
                })
                ->rawColumns(['status_badge', 'rating', 'actions'])
                ->make(true);
        }

        $stats = [
            'total_services' => Hotel::count(),
            'pending_services' => Hotel::where('status', 'pending')->count(),
            'approved_services' => Hotel::where('status', 'active')->count(),
            'suspended_services' => Hotel::where('status', 'suspended')->count(),
            'rejected_services' => Hotel::where('status', 'rejected')->count(),
        ];

        return view('admin.partners.hotel-services', compact('stats'));
    }

    /**
     * Get hotel service details
     */
    public function getHotelService($id)
    {
        $hotel = Hotel::with(['provider', 'rooms'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'description' => $hotel->description,
                'city' => $hotel->city,
                'country' => $hotel->country,
                'address' => $hotel->address,
                'star_rating' => $hotel->star_rating,
                'status' => $hotel->status,
                'is_active' => $hotel->is_active,
                'amenities' => $hotel->amenities,
                'images' => $hotel->images,
                'provider' => $hotel->provider,
                'rooms_count' => $hotel->rooms->count(),
                'created_at' => $hotel->created_at->format('M d, Y H:i'),
                'phone' => $hotel->phone,
                'email' => $hotel->email,
                'website' => $hotel->website,
                'check_in_time' => $hotel->check_in_time,
                'check_out_time' => $hotel->check_out_time,
                'distance_to_haram' => $hotel->distance_to_haram,
                'type' => $hotel->type
            ]
        ]);
    }

    /**
     * Approve a hotel service
     */
    public function approveHotelService(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        $hotel = Hotel::findOrFail($id);
        
        if ($hotel->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This hotel is already approved.'
            ]);
        }

        try {
            DB::transaction(function () use ($hotel, $request) {
                $hotel->update([
                    'status' => 'active'
                ]);
                
                // Log the approval if notes are provided
                if ($request->notes) {
                    // You can add an approval log table later if needed
                    \Log::info("Hotel {$hotel->name} approved by admin " . auth()->user()->name . ". Notes: " . $request->notes);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Hotel approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve hotel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a hotel service
     */
    public function rejectHotelService(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $hotel = Hotel::findOrFail($id);

        try {
            DB::transaction(function () use ($hotel, $request) {
                $hotel->update([
                    'status' => 'rejected'
                ]);
                
                // Log the rejection reason
                \Log::info("Hotel {$hotel->name} rejected by admin " . auth()->user()->name . ". Reason: " . $request->reason);
            });

            return response()->json([
                'success' => true,
                'message' => 'Hotel rejected'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject hotel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend a hotel service
     */
    public function suspendHotelService(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $hotel = Hotel::findOrFail($id);

        try {
            $hotel->update([
                'status' => 'suspended'
            ]);
            
            // Log the suspension reason
            \Log::info("Hotel {$hotel->name} suspended by admin " . auth()->user()->name . ". Reason: " . $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Hotel suspended'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend hotel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate a hotel service
     */
    public function reactivateHotelService(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);

        try {
            $hotel->update([
                'status' => 'active'
            ]);
            
            // Log the reactivation
            \Log::info("Hotel {$hotel->name} reactivated by admin " . auth()->user()->name);

            return response()->json([
                'success' => true,
                'message' => 'Hotel reactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate hotel: ' . $e->getMessage()
            ], 500);
        }
    }
}
