<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // Determine the appropriate login route based on the request path
        $path = $request->path();
        
        if (str_starts_with($path, 'admin')) {
            return route('admin.login');
        }
        
        if (str_starts_with($path, 'b2b')) {
            return route('b2b.login');
        }
        
        if (str_starts_with($path, 'customer')) {
            return route('customer.login');
        }
        
        // Default to customer login for public areas
        return route('customer.login');
    }
}
