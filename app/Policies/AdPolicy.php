<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ad;

class AdPolicy
{
    /**
     * Determine if the user can view any ads.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all ads
        if ($user->isAdmin()) {
            return true;
        }

        // B2B users can view their own ads
        return $user->isB2BUser();
    }

    /**
     * Determine if the user can view the ad.
     */
    public function view(User $user, Ad $ad): bool
    {
        // Admin can view all ads
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can view their own ads
        return $user->id === $ad->owner_id && 
               get_class($user) === $ad->owner_type;
    }

    /**
     * Determine if the user can create ads.
     */
    public function create(User $user): bool
    {
        // Only active B2B users can create ads
        return $user->isB2BUser() && $user->isActive();
    }

    /**
     * Determine if the user can update the ad.
     */
    public function update(User $user, Ad $ad): bool
    {
        // Admin can update any ad
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can only update draft or rejected ads
        $isOwner = $user->id === $ad->owner_id && 
                   get_class($user) === $ad->owner_type;
        
        return $isOwner && ($ad->isDraft() || $ad->isRejected());
    }

    /**
     * Determine if the user can delete the ad.
     */
    public function delete(User $user, Ad $ad): bool
    {
        // Admin can delete any ad
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can delete only draft or rejected ads
        $isOwner = $user->id === $ad->owner_id && 
                   get_class($user) === $ad->owner_type;
        
        return $isOwner && ($ad->isDraft() || $ad->isRejected());
    }

    /**
     * Determine if the user can submit the ad for approval.
     */
    public function submit(User $user, Ad $ad): bool
    {
        // Owner can submit draft ads
        $isOwner = $user->id === $ad->owner_id && 
                   get_class($user) === $ad->owner_type;
        
        return $isOwner && $ad->isDraft();
    }

    /**
     * Determine if the user can withdraw the ad from approval.
     */
    public function withdraw(User $user, Ad $ad): bool
    {
        // Owner can withdraw pending ads
        $isOwner = $user->id === $ad->owner_id && 
                   get_class($user) === $ad->owner_type;
        
        return $isOwner && $ad->isPending();
    }

    /**
     * Determine if the user can approve the ad.
     */
    public function approve(User $user, Ad $ad): bool
    {
        return $user->isAdmin() && $ad->isPending();
    }

    /**
     * Determine if the user can reject the ad.
     */
    public function reject(User $user, Ad $ad): bool
    {
        return $user->isAdmin() && $ad->isPending();
    }

    /**
     * Determine if the user can activate/deactivate the ad.
     */
    public function toggleActive(User $user, Ad $ad): bool
    {
        // Admin can toggle any approved ad
        if ($user->isAdmin() && $ad->isApproved()) {
            return true;
        }

        // Owner can toggle their own approved ads
        $isOwner = $user->id === $ad->owner_id && 
                   get_class($user) === $ad->owner_type;
        
        return $isOwner && $ad->isApproved();
    }

    /**
     * Determine if the user can set ad priority.
     */
    public function setPriority(User $user, Ad $ad): bool
    {
        // Only admins can set ad priority
        return $user->isAdmin();
    }

    /**
     * Determine if the user can upload images for the ad.
     */
    public function uploadImage(User $user, Ad $ad): bool
    {
        // Owner can upload images for draft or rejected ads
        $isOwner = $user->id === $ad->owner_id && 
                   get_class($user) === $ad->owner_type;
        
        return $isOwner && ($ad->isDraft() || $ad->isRejected());
    }

    /**
     * Determine if the user can view audit logs for the ad.
     */
    public function viewAuditLogs(User $user, Ad $ad): bool
    {
        // Admin can view all audit logs
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can view their own ad's audit logs
        return $user->id === $ad->owner_id && 
               get_class($user) === $ad->owner_type;
    }
}
