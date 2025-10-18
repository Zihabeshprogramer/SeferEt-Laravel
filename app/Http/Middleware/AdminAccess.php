<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('admin.login');
        }
        
        // Check if user is admin and active
        if ($user->role !== User::ROLE_ADMIN || $user->status !== User::STATUS_ACTIVE) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        // If a specific permission is required, check it
        if ($permission) {
            $hasPermission = $user->getPermissionsViaRoles()->where('name', $permission)->isNotEmpty();
            if (!$hasPermission) {
                abort(403, "Access denied. Permission '{$permission}' required.");
            }
        }
        
        return $next($request);
    }
}
