<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Package;

class DashboardController extends Controller
{
    /**
     * Display admin login page.
     *
     * @return View
     */
    public function login(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Display admin dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalPartners' => User::partners()->count(),
            'pendingPartners' => User::partners()->pending()->count(),
            'activePartners' => User::partners()->active()->count(),
        ];

        // Get recent pending partners for approval
        $pendingPartners = User::partners()->pending()->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'pendingPartners'));
    }

    /**
     * Display users management.
     *
     * @return View
     */
    public function users(): View
    {
        $stats = [
            'total' => 0,
            'customers' => 0,
            'partners' => 0,
            'admins' => 0,
            'active' => 0,
            'inactive' => 0,
        ];

        $users = []; // TODO: Implement actual user data

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Display partners management.
     *
     * @return View
     */
    public function partners(): View
    {
        $stats = [
            'total' => User::partners()->count(),
            'active' => User::partners()->active()->count(),
            'pending' => User::partners()->pending()->count(),
            'suspended' => User::partners()->suspended()->count(),
        ];

        $partners = User::partners()->latest()->get();

        return view('admin.partners.index', compact('partners', 'stats'));
    }

    /**
     * Display packages management.
     *
     * @return View
     */
    public function packages(): View
    {
        $stats = [
            'total' => Package::count(),
            'active' => Package::where('status', Package::STATUS_ACTIVE)->count(),
            'pending' => Package::where('approval_status', Package::APPROVAL_PENDING)->count(),
            'rejected' => Package::where('approval_status', Package::APPROVAL_REJECTED)->count(),
        ];

        $packages = Package::with(['creator:id,name,email'])
                          ->latest()
                          ->get();

        return view('admin.packages.index', compact('packages', 'stats'));
    }

    /**
     * Display bookings management.
     *
     * @return View
     */
    public function bookings(): View
    {
        $stats = [
            'total' => 0,
            'confirmed' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'completed' => 0,
        ];

        $bookings = []; // TODO: Implement actual booking data

        return view('admin.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Display system analytics.
     *
     * @return View
     */
    public function analytics(): View
    {
        $analytics = [
            'revenue' => [], // TODO: Implement revenue analytics
            'bookings' => [], // TODO: Implement booking analytics
            'users' => [], // TODO: Implement user analytics
            'packages' => [], // TODO: Implement package analytics
            'geographical' => [], // TODO: Implement geographical data
        ];

        return view('admin.analytics', compact('analytics'));
    }

    /**
     * Display system settings.
     *
     * @return View
     */
    public function settings(): View
    {
        $settings = []; // TODO: Implement system settings
        $permissions = []; // TODO: Implement role permissions

        return view('admin.settings', compact('settings', 'permissions'));
    }

    /**
     * Approve a pending partner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approvePartner(Request $request, $id)
    {
        $partner = User::partners()->pending()->findOrFail($id);
        
        $partner->activate();
        
        return redirect()->back()->with('success', "Partner '{$partner->name}' has been approved successfully.");
    }

    /**
     * Reject a pending partner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectPartner(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $partner = User::partners()->pending()->findOrFail($id);
        
        $partner->suspend($request->input('reason', 'Registration rejected by administrator.'));
        
        return redirect()->back()->with('success', "Partner '{$partner->name}' has been rejected.");
    }

    /**
     * View partner details for approval.
     *
     * @param  int  $id
     * @return View
     */
    public function viewPartner($id): View
    {
        $partner = User::partners()->findOrFail($id);
        
        return view('admin.partners.show', compact('partner'));
    }

    /**
     * Display admin profile.
     *
     * @param  int  $id
     * @return View
     */
    public function profile($id): View
    {
        $user = User::findOrFail($id);
        
        // Ensure the user can only view their own profile or admin can view any
        if (auth()->id() !== (int)$id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to profile.');
        }
        
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update admin profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Ensure the user can only update their own profile or admin can update any
        if (auth()->id() !== (int)$id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to profile.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        
        $user->save();
        
        return redirect()->route('admin.profile', $id)
                        ->with('success', 'Profile updated successfully!');
    }

    /**
     * Approve a pending package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approvePackage(Request $request, $id)
    {
        $package = Package::where('approval_status', Package::APPROVAL_PENDING)->findOrFail($id);
        
        $package->approval_status = Package::APPROVAL_APPROVED;
        $package->approved_at = now();
        $package->approved_by = auth()->id();
        
        // If package is still in draft, make it active
        if ($package->status === Package::STATUS_DRAFT) {
            $package->status = Package::STATUS_ACTIVE;
        }
        
        $package->save();
        
        // Clear B2C cache so approved packages appear immediately
        if (config('b2c.cache.enabled')) {
            try {
                // Try tagged cache clearing first (Redis, Memcached)
                \Illuminate\Support\Facades\Cache::tags(['b2c_packages', 'b2c_search'])->flush();
            } catch (\Exception $e) {
                // Fall back to individual key clearing for file/database cache
                $cacheKeys = [
                    'b2c_featured_packages_4',
                    'b2c_filter_options',
                    'b2c_package_statistics',
                ];
                
                foreach ($cacheKeys as $key) {
                    \Illuminate\Support\Facades\Cache::forget($key);
                }
                
                // Clear package details cache (pattern-based clearing)
                for ($i = 1; $i <= 100; $i++) {
                    \Illuminate\Support\Facades\Cache::forget('b2c_package_details_' . $i);
                }
            }
        }
        
        return redirect()->back()->with('success', "Package '{$package->name}' has been approved successfully.");
    }

    /**
     * Reject a pending package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectPackage(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000'
        ]);

        $package = Package::where('approval_status', Package::APPROVAL_PENDING)->findOrFail($id);
        
        $package->approval_status = Package::APPROVAL_REJECTED;
        $package->rejection_reason = $request->input('reason', 'Package rejected by administrator.');
        $package->approved_by = auth()->id();
        $package->approved_at = now();
        
        $package->save();
        
        return redirect()->back()->with('success', "Package '{$package->name}' has been rejected.");
    }

    /**
     * View package details for approval.
     *
     * @param  int  $id
     * @return View
     */
    public function viewPackage($id): View
    {
        $package = Package::with(['creator:id,name,email', 'packageActivities', 'hotels', 'flights'])->findOrFail($id);
        
        return view('admin.packages.show', compact('package'));
    }
}
