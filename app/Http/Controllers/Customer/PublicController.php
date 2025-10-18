<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('B2C/Home', [
            'packages' => [], // Placeholder for featured packages
            'testimonials' => [], // Placeholder for testimonials
        ]);
    }

    /**
     * Display packages page.
     *
     * @return Response
     */
    public function packages(): Response
    {
        return Inertia::render('B2C/Packages', [
            'packages' => [], // Placeholder for all packages
            'filters' => [], // Placeholder for package filters
        ]);
    }

    /**
     * Display package details.
     *
     * @param int $id
     * @return Response
     */
    public function packageDetails(int $id): Response
    {
        return Inertia::render('B2C/PackageDetails', [
            'package' => [], // Placeholder for package details
            'relatedPackages' => [], // Placeholder for related packages
        ]);
    }

    /**
     * Display about page.
     *
     * @return Response
     */
    public function about(): Response
    {
        return Inertia::render('B2C/About');
    }

    /**
     * Display contact page.
     *
     * @return Response
     */
    public function contact(): Response
    {
        return Inertia::render('B2C/Contact');
    }

    /**
     * Display login page.
     *
     * @return Response
     */
    public function login(): Response
    {
        return Inertia::render('B2C/Auth/Login');
    }

    /**
     * Display register page.
     *
     * @return Response
     */
    public function register(): Response
    {
        return Inertia::render('B2C/Auth/Register');
    }

    /**
     * Display customer dashboard.
     *
     * @return Response
     */
    public function dashboard(): Response
    {
        return Inertia::render('B2C/Dashboard', [
            'user' => auth()->user(),
            'recentBookings' => [], // Placeholder
            'recommendedPackages' => [], // Placeholder
        ]);
    }

    /**
     * Display customer bookings.
     *
     * @return Response
     */
    public function bookings(): Response
    {
        return Inertia::render('B2C/Bookings', [
            'bookings' => [], // Placeholder for user bookings
        ]);
    }

    /**
     * Display customer profile.
     *
     * @return Response
     */
    public function profile(): Response
    {
        return Inertia::render('B2C/Profile', [
            'user' => auth()->user(),
        ]);
    }
}
