<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display partner login page.
     *
     * @return View
     */
    public function login(): View
    {
        return view('b2b.auth.login');
    }

    /**
     * Display partner dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $partner = Auth::user();
        
        $stats = [
            'totalPackages' => 0, // TODO: Implement actual counts
            'totalBookings' => 0,
            'totalRevenue' => 0,
            'activePackages' => 0,
        ];

        $recentBookings = []; // TODO: Implement recent bookings
        $topPackages = []; // TODO: Implement top packages

        return view('b2b.common.dashboard', compact('stats', 'recentBookings', 'topPackages', 'partner'));
    }

    /**
     * Display partner packages.
     *
     * @return View
     */
    public function packages(): View
    {
        $stats = [
            'total' => 0,
            'active' => 0,
            'draft' => 0,
        ];

        $packages = []; // TODO: Implement actual package data

        return view('b2b.packages.index', compact('packages', 'stats'));
    }

    /**
     * Display create package form.
     *
     * @return View
     */
    public function createPackage(): View
    {
        $categories = []; // TODO: Implement package categories
        $countries = []; // TODO: Implement countries

        return view('b2b.packages.create', compact('categories', 'countries'));
    }

    /**
     * Display edit package form.
     *
     * @param int $id
     * @return View
     */
    public function editPackage(int $id): View
    {
        $package = []; // TODO: Implement package data
        $categories = []; // TODO: Implement package categories
        $countries = []; // TODO: Implement countries

        return view('b2b.packages.edit', compact('package', 'categories', 'countries'));
    }

    /**
     * Display partner bookings.
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
        ];

        $bookings = []; // TODO: Implement actual booking data

        return view('b2b.common.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Display partner analytics.
     *
     * @return View
     */
    public function analytics(): View
    {
        $analytics = [
            'revenue' => [], // TODO: Implement revenue data
            'bookings' => [], // TODO: Implement booking analytics
            'packages' => [], // TODO: Implement package performance
            'customers' => [], // TODO: Implement customer analytics
        ];

        return view('b2b.analytics', compact('analytics'));
    }

    /**
     * Display partner profile.
     *
     * @return View
     */
    public function profile(): View
    {
        $partner = Auth::user();
        
        return view('b2b.common.profile', compact('partner'));
    }

    /**
     * Update partner profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'company_registration_number' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->update($request->only([
            'name',
            'company_name', 
            'email',
            'phone',
            'company_registration_number'
        ]));

        return redirect()->route('b2b.profile')->with('success', 'Profile updated successfully.');
    }
}
