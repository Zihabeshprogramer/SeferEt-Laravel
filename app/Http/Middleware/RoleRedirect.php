<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class RoleRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Only redirect if user is active
            if ($user->status !== User::STATUS_ACTIVE) {
                Auth::logout();
                
                // Determine appropriate login route based on user role
                $loginRoute = match($user->role) {
                    User::ROLE_ADMIN => 'admin.login',
                    User::ROLE_PARTNER => 'b2b.login',
                    User::ROLE_TRAVEL_AGENT => 'b2b.login',
                    User::ROLE_HOTEL_PROVIDER => 'b2b.login',
                    User::ROLE_TRANSPORT_PROVIDER => 'b2b.login',
                    User::ROLE_CUSTOMER => 'customer.login',
                    default => 'customer.login'
                };
                
                return redirect()->route($loginRoute)->withErrors([
                    'email' => 'Your account is not active. Please contact support.'
                ]);
            }
            
            // Redirect based on role if not already on appropriate route
            switch ($user->role) {
                case User::ROLE_ADMIN:
                    if (!$request->is('admin/*')) {
                        return redirect()->route('admin.dashboard');
                    }
                    break;
                    
                case User::ROLE_PARTNER:
                    if (!$request->is('b2b/*')) {
                        return redirect()->route('b2b.dashboard');
                    }
                    break;
                    
                case User::ROLE_TRAVEL_AGENT:
                    if (!$request->is('b2b/*')) {
                        return redirect()->route('b2b.travel-agent.dashboard');
                    }
                    break;
                    
                case User::ROLE_HOTEL_PROVIDER:
                    if (!$request->is('b2b/*')) {
                        return redirect()->route('b2b.hotel-provider.dashboard');
                    }
                    break;
                    
                case User::ROLE_TRANSPORT_PROVIDER:
                    if (!$request->is('b2b/*')) {
                        return redirect()->route('b2b.transport-provider.dashboard');
                    }
                    break;
                    
                case User::ROLE_CUSTOMER:
                    if (!$request->is('customer/*') && !$request->is('/')) {
                        return redirect()->route('customer.dashboard');
                    }
                    break;
            }
        }
        
        return $next($request);
    }
}
