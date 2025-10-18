<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class CustomerAuthController extends Controller
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
     * Show the customer login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.customer.login');
    }

    /**
     * Handle a customer login request.
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

        // Check if user is customer
        if ($user->role !== User::ROLE_CUSTOMER) {
            throw ValidationException::withMessages([
                'email' => ['Access denied. Customer access required.'],
            ]);
        }

        // Check if user status is active
        if ($user->status !== User::STATUS_ACTIVE) {
            $message = match($user->status) {
                User::STATUS_PENDING => 'Your account is pending verification. Please check your email.',
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

            return redirect()->intended(route('customer.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['These credentials do not match our records.'],
        ]);
    }

    /**
     * Log the customer out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
