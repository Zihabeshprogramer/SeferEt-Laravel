<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class B2BAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the B2B login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('b2b.auth.login');
    }

    /**
     * Handle a B2B login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Check if user is B2B partner or service provider
        if (!$user->isB2BUser()) {
            throw ValidationException::withMessages([
                'email' => ['Access denied. B2B access required.'],
            ]);
        }

        // Check if user status is active
        if ($user->status !== User::STATUS_ACTIVE) {
            $message = match($user->status) {
                User::STATUS_PENDING => 'Your account is pending approval. Please contact support.',
                User::STATUS_SUSPENDED => 'Your account has been suspended. Please contact support.',
                default => 'Your account is not active. Please contact support.'
            };

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Update last login
            $user->updateLastLogin($request->ip());
            
            // Redirect based on user role
            $defaultRoute = match($user->role) {
                User::ROLE_PARTNER => 'b2b.dashboard',
                User::ROLE_HOTEL_PROVIDER => 'b2b.hotel-provider.dashboard',
                User::ROLE_TRANSPORT_PROVIDER => 'b2b.transport-provider.dashboard',
                default => 'b2b.dashboard'
            };

            return redirect()->intended(route($defaultRoute));
        }

        throw ValidationException::withMessages([
            'email' => ['These credentials do not match our records.'],
        ]);
    }

    /**
     * Log the B2B partner out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('b2b.login');
    }
}
