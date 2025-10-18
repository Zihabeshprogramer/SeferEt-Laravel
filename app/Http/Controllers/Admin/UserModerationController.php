<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.access:view admin dashboard');
    }

    /**
     * Display the user management dashboard
     */
    public function index(Request $request)
    {
        // Check permission using our custom method
        $user = auth()->user();
        if (!$user->getPermissionsViaRoles()->where('name', 'manage users')->isNotEmpty()) {
            abort(403, 'Access denied. Permission required: manage users');
        }

        if ($request->ajax()) {
            // Only show admin users, not B2B partners or customers
            $users = User::with('roles')
                ->where('role', User::ROLE_ADMIN) // Only admin users
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->permission_role, function ($query, $permissionRole) {
                    return $query->whereHas('roles', function ($q) use ($permissionRole) {
                        $q->where('name', $permissionRole);
                    });
                })
                ->select(['id', 'name', 'email', 'role', 'status', 'created_at']);

            return DataTables::of($users)
                ->addColumn('roles', function ($user) {
                    return $user->roles->pluck('name')->map(function ($role) {
                        return '<span class="badge badge-info">' . str_replace('_', ' ', ucwords($role, '_')) . '</span>';
                    })->implode(' ');
                })
                ->addColumn('status_badge', function ($user) {
                    $badgeClass = match ($user->status) {
                        User::STATUS_ACTIVE => 'success',
                        User::STATUS_SUSPENDED => 'danger',
                        User::STATUS_PENDING => 'warning',
                        default => 'secondary'
                    };
                    return '<span class="badge badge-' . $badgeClass . '">' . ucfirst($user->status) . '</span>';
                })
                ->addColumn('role_badge', function ($user) {
                    // Only admin users will be shown
                    return '<span class="badge badge-primary">Admin</span>';
                })
                ->addColumn('actions', function ($user) {
                    $actions = '<div class="btn-group" role="group">';
                    $currentUser = auth()->user();
                    
                    // Edit button (always available for users with manage users permission)
                    if ($currentUser->getPermissionsViaRoles()->where('name', 'manage users')->isNotEmpty()) {
                        $actions .= '<a href="' . route('admin.users.edit', $user->id) . '" class="btn btn-sm btn-info" title="Edit User">'
                                 . '<i class="fas fa-edit"></i></a>';
                    }
                    
                    // Status change buttons
                    if ($currentUser->getPermissionsViaRoles()->where('name', 'activate users')->isNotEmpty() && $user->status !== User::STATUS_ACTIVE) {
                        $actions .= '<button class="btn btn-sm btn-success ml-1" onclick="setUserStatus(' . $user->id . ', \'active\')" title="Activate User">'
                                 . '<i class="fas fa-check"></i></button>';
                    }
                    
                    if ($currentUser->getPermissionsViaRoles()->where('name', 'suspend users')->isNotEmpty() && $user->status !== User::STATUS_SUSPENDED) {
                        $actions .= '<button class="btn btn-sm btn-warning ml-1" onclick="setUserStatus(' . $user->id . ', \'suspended\')" title="Suspend User">'
                                 . '<i class="fas fa-pause"></i></button>';
                    }
                    
                    if ($currentUser->getPermissionsViaRoles()->where('name', 'reject users')->isNotEmpty() && $user->status === User::STATUS_PENDING) {
                        $actions .= '<button class="btn btn-sm btn-danger ml-1" onclick="setUserStatus(' . $user->id . ', \'rejected\')" title="Reject User">'
                                 . '<i class="fas fa-times"></i></button>';
                    }
                    
                    // Delete button (only for users other than self and with proper permissions)
                    if ($currentUser->getPermissionsViaRoles()->where('name', 'manage users')->isNotEmpty() && 
                        $currentUser->id !== $user->id && 
                        (!$user->hasRole('super_admin') || $currentUser->hasRole('super_admin'))) {
                        $actions .= '<button class="btn btn-sm btn-danger ml-1" onclick="confirmDeleteUser(' . $user->id . ', \'' . addslashes($user->name) . '\')" title="Delete User">'
                                 . '<i class="fas fa-trash"></i></button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['roles', 'status_badge', 'role_badge', 'actions'])
                ->make(true);
        }

        // Only admin-related roles and statuses
        $roles = [User::ROLE_ADMIN => 'Admin']; // Only admin role
        $statuses = User::STATUSES;
        $permissionRoles = Role::whereIn('name', ['super_admin', 'user_verifier', 'package_verifier'])->get(); // Only admin permission roles

        return view('admin.users.moderation', compact('roles', 'statuses', 'permissionRoles'));
    }

    /**
     * Update user status (approve/suspend/reject/activate)
     */
    public function setStatus(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,suspended,rejected',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $status = $request->status;
        
        // Authorization checks using our custom method
        $user = auth()->user();
        switch ($status) {
            case 'active':
                if (!$user->getPermissionsViaRoles()->where('name', 'activate users')->isNotEmpty()) {
                    abort(403, 'Access denied. Permission required: activate users');
                }
                break;
            case 'suspended':
                if (!$user->getPermissionsViaRoles()->where('name', 'suspend users')->isNotEmpty()) {
                    abort(403, 'Access denied. Permission required: suspend users');
                }
                break;
            case 'rejected':
                if (!$user->getPermissionsViaRoles()->where('name', 'reject users')->isNotEmpty()) {
                    abort(403, 'Access denied. Permission required: reject users');
                }
                break;
        }

        // Prevent self-actions
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot modify your own status.']);
        }

        $user->status = $status;
        if ($status === 'suspended' && $request->reason) {
            $user->suspend_reason = $request->reason;
        } elseif ($status === 'active') {
            $user->suspend_reason = null;
        }

        $user->save();

        $statusText = match ($status) {
            'active' => 'activated',
            'suspended' => 'suspended',
            'rejected' => 'rejected'
        };

        return response()->json([
            'success' => true, 
            'message' => "User {$user->name} has been {$statusText} successfully."
        ]);
    }

    /**
     * Show form to create admin user
     */
    public function createAdmin()
    {
        // Check permission using our custom method
        $user = auth()->user();
        if (!$user->getPermissionsViaRoles()->where('name', 'create admin users')->isNotEmpty()) {
            abort(403, 'Access denied. Permission required: create admin users');
        }
        
        $assignableRoles = $this->getAssignableRoles();
        
        return view('admin.users.create-admin', compact('assignableRoles'));
    }

    /**
     * Store new admin user
     */
    public function storeAdmin(Request $request)
    {
        // Check permission using our custom method
        $user = auth()->user();
        if (!$user->getPermissionsViaRoles()->where('name', 'create admin users')->isNotEmpty()) {
            abort(403, 'Access denied. Permission required: create admin users');
        }

        // Only allow creating admin users with admin role
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin', // Only admin role allowed
            'permission_roles' => 'required|array|min:1', // Must have at least one permission role
            'permission_roles.*' => 'exists:roles,name'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $assignableRoles = $this->getAssignableRoles();
        if ($request->permission_roles) {
            $invalidRoles = array_diff($request->permission_roles, $assignableRoles->pluck('name')->toArray());
            if (!empty($invalidRoles)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You cannot assign roles: ' . implode(', ', $invalidRoles)
                ]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Assign multiple permission roles (required)
        $user->syncRoles($request->permission_roles);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully!',
                'redirect' => route('admin.users.moderation')
            ]);
        }

        return redirect()->route('admin.users.moderation')
            ->with('success', 'Admin user created successfully!');
    }

    /**
     * Show form to edit admin user
     */
    public function edit(User $user)
    {
        // Check permission using our custom method
        $currentUser = auth()->user();
        if (!$currentUser->getPermissionsViaRoles()->where('name', 'manage users')->isNotEmpty()) {
            abort(403, 'Access denied. Permission required: manage users');
        }
        
        // Only allow editing admin users
        if ($user->role !== User::ROLE_ADMIN) {
            abort(403, 'You can only edit admin users through this interface.');
        }
        
        // Super admin can edit anyone, others can only edit lower-level admins
        if (!$currentUser->hasRole('super_admin') && $user->hasRole('super_admin')) {
            abort(403, 'You cannot edit super admin users.');
        }
        
        $assignableRoles = $this->getAssignableRoles();
        $currentRoles = $user->roles->pluck('name')->toArray();
        
        return view('admin.users.edit', compact('user', 'assignableRoles', 'currentRoles'));
    }

    /**
     * Update admin user
     */
    public function update(Request $request, User $user)
    {
        // Check permission using our custom method
        $currentUser = auth()->user();
        if (!$currentUser->getPermissionsViaRoles()->where('name', 'manage users')->isNotEmpty()) {
            abort(403, 'Access denied. Permission required: manage users');
        }
        
        // Only allow editing admin users
        if ($user->role !== User::ROLE_ADMIN) {
            abort(403, 'You can only edit admin users through this interface.');
        }
        
        // Super admin can edit anyone, others can only edit lower-level admins
        if (!$currentUser->hasRole('super_admin') && $user->hasRole('super_admin')) {
            abort(403, 'You cannot edit super admin users.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'permission_roles' => 'required|array|min:1',
            'permission_roles.*' => 'exists:roles,name',
            'status' => 'required|in:active,suspended,pending'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Validate assignable roles
        $assignableRoles = $this->getAssignableRoles();
        $invalidRoles = array_diff($request->permission_roles, $assignableRoles->pluck('name')->toArray());
        if (!empty($invalidRoles)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You cannot assign roles: ' . implode(', ', $invalidRoles)
                ]);
            }
            return redirect()->back()->withErrors(['permission_roles' => 'You cannot assign some of the selected roles.'])->withInput();
        }
        
        // Prevent removing super_admin role from self
        if ($currentUser->id === $user->id && $currentUser->hasRole('super_admin') && !in_array('super_admin', $request->permission_roles)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You cannot remove super admin role from yourself.'
                ]);
            }
            return redirect()->back()->withErrors(['permission_roles' => 'You cannot remove super admin role from yourself.'])->withInput();
        }
        
        // Update user details
        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = $request->status;
        
        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        // Update roles
        $user->syncRoles($request->permission_roles);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin user updated successfully!',
                'redirect' => route('admin.users.moderation')
            ]);
        }

        return redirect()->route('admin.users.moderation')
            ->with('success', 'Admin user updated successfully!');
    }
    
    /**
     * Delete admin user
     */
    public function destroy(User $user)
    {
        // Check permission using our custom method
        $currentUser = auth()->user();
        if (!$currentUser->getPermissionsViaRoles()->where('name', 'manage users')->isNotEmpty()) {
            abort(403, 'Access denied. Permission required: manage users');
        }
        
        // Only allow deleting admin users
        if ($user->role !== User::ROLE_ADMIN) {
            abort(403, 'You can only delete admin users through this interface.');
        }
        
        // Prevent self-deletion
        if ($currentUser->id === $user->id) {
            return response()->json(['success' => false, 'message' => 'You cannot delete yourself.']);
        }
        
        // Super admin can delete anyone, others can only delete lower-level admins
        if (!$currentUser->hasRole('super_admin') && $user->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => 'You cannot delete super admin users.']);
        }
        
        $userName = $user->name;
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Admin user '{$userName}' has been deleted successfully."
        ]);
    }

    /**
     * Get admin permission roles that current user can assign
     */
    private function getAssignableRoles()
    {
        $currentUser = auth()->user();
        
        // Only return administrative permission roles
        $adminRoles = ['super_admin', 'user_verifier', 'package_verifier'];
        
        if ($currentUser->hasRole('super_admin')) {
            // Super admin can assign any admin role
            return Role::whereIn('name', $adminRoles)->get();
        }
        
        // Other admin roles can only assign lower-level admin roles (not super_admin)
        return Role::whereIn('name', ['user_verifier', 'package_verifier'])->get();
    }
}
