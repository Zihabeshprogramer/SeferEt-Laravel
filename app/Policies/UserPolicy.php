<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('manage users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create admin users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can edit their own profile, or admins can edit any user
        return $user->id === $model->id || $user->hasPermissionTo('manage users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only super admins can delete users, and they can't delete themselves
        return $user->hasRole('super_admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can update user status.
     */
    public function updateStatus(User $user, User $model): bool
    {
        // Users cannot modify their own status
        if ($user->id === $model->id) {
            return false;
        }

        // Check for specific status change permissions
        return $user->hasAnyPermission([
            'activate users', 
            'suspend users', 
            'reject users', 
            'verify users'
        ]);
    }

    /**
     * Determine whether the user can create admin users.
     */
    public function createAdmin(User $user): bool
    {
        return $user->hasPermissionTo('create admin users');
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRole(User $user): bool
    {
        return $user->hasPermissionTo('assign roles');
    }

    /**
     * Determine whether the user can manage roles.
     */
    public function manageRoles(User $user): bool
    {
        return $user->hasPermissionTo('manage roles');
    }

    /**
     * Determine whether the user can activate another user.
     */
    public function activate(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->hasPermissionTo('activate users');
    }

    /**
     * Determine whether the user can suspend another user.
     */
    public function suspend(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->hasPermissionTo('suspend users');
    }

    /**
     * Determine whether the user can reject another user.
     */
    public function reject(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->hasPermissionTo('reject users');
    }

    /**
     * Determine whether the user can verify another user.
     */
    public function verify(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->hasPermissionTo('verify users');
    }
}
}
